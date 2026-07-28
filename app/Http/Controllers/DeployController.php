<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\DeploymentService;
use Throwable;

class DeployController extends Controller
{
    public function index()
    {
        return view('deploy');
    }

    public function deploy(Request $request)
    {
        $request->validate([
            'domain' => [
                'required',
                'regex:/^(?!-)(?:[A-Za-z0-9-]{1,63}\.)+[A-Za-z]{2,}$/'
            ],
            'zip' => [
                'required',
                'file',
                'mimes:zip'
            ]
        ]);

        $zip = $request->file('zip');

        $domain = strtolower(trim($request->domain));

        $fileName = $domain . '.zip';

        $disk = Storage::disk('local');

        if ($disk->exists('uploads/' . $fileName)) {
            $disk->delete('uploads/' . $fileName);
        }

        $path = $zip->storeAs('uploads', $fileName, 'local');

        $localZip = $disk->path($path);

        if (!file_exists($localZip)) {
            return response()->json([
                'status' => false,
                'message' => 'Local ZIP file not found.',
                'path' => $localZip
            ]);
        }

        $deployment = new DeploymentService();

        try {
            $siteResult = $deployment->registerSite($domain);

            $deployment->createWebsiteFolder($domain);

            $remoteZip = $deployment->uploadZip($domain, $localZip);

            $extractOutput = $deployment->extractZip($domain);

            // SSL is attempted but never allowed to fail the whole
            // deploy - the site + files are already live at this
            // point even if certificate issuance has a hiccup
            // (e.g. DNS not propagated yet for a brand new domain).
            $sslResult = null;
            $sslError = null;

            try {
                $sslResult = $deployment->applySsl($domain);
            } catch (Throwable $e) {
                $sslError = $e->getMessage();
            }

            // Standard rewrite rules (pretty URLs -> .php pages),
            // applied the same way to every deployed site.
            $rewriteResult = null;
            $rewriteError = null;

            try {
                $rewriteResult = $deployment->applyRewriteRules($domain);
            } catch (Throwable $e) {
                $rewriteError = $e->getMessage();
            }

            return response()->json([
                'status' => true,
                'message' => 'Website deployed successfully.',
                'local_zip' => $localZip,
                'remote_zip' => $remoteZip,
                'extract_output' => $extractOutput,
                'aapanel_site' => $siteResult,
                'ssl' => $sslResult,
                'ssl_error' => $sslError,
                'rewrite' => $rewriteResult,
                'rewrite_error' => $rewriteError,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Deployment failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
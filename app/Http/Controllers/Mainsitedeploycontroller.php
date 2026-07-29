<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\MainSiteDeploymentService;
use Throwable;

class MainSiteDeployController extends Controller
{
    public function deploy(Request $request)
    {
        set_time_limit(300);
        ini_set('max_execution_time', 300);

        $request->validate([
            'domain' => [
                'required',
                'regex:/^(?!-)(?:[A-Za-z0-9-]{1,63}\.)+[A-Za-z]{2,}$/'
            ],
            'ad_unit' => [
                'required',
                'string',
            ],
            'zip' => [
                'required',
                'file',
                'mimes:zip'
            ]
        ]);

        $zip = $request->file('zip');

        $domain = strtolower(trim($request->domain));
        $adUnit = trim($request->ad_unit);

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

        $deployment = new MainSiteDeploymentService();
        $warnings = [];

        try {
            $siteResult = $deployment->registerSite($domain);
            $domainAlreadyExisted = $siteResult['already_exists'] ?? false;

            if ($domainAlreadyExisted) {
                $warnings[] = "Domain '{$domain}' already exists on the server - existing files were overwritten with this upload.";
            }

            $remoteZip = $deployment->uploadZip($domain, $localZip);

            // Folder creation, extraction, flatten, placeholder
            // replace, and rewrite rules - all in ONE SSH round-trip.
            $deployOutput = $deployment->deployFiles($domain, 'AD_Unit', $adUnit);

            // SSL - non-fatal, and the slowest step (Let's Encrypt
            // issues a real certificate over the internet).
            $sslResult = null;
            $sslError = null;

            try {
                $sslResult = $deployment->applySsl($domain);
            } catch (Throwable $e) {
                $sslError = $e->getMessage();
                $warnings[] = "SSL installation failed: {$sslError}";
            }

            $message = empty($warnings)
                ? 'Main site deployed successfully - site created, SSL installed, and rewrite rules applied.'
                : 'Main site deployed with ' . count($warnings) . ' warning(s) - see "warnings" below.';

            return response()->json([
                'status' => true,
                'message' => $message,
                'domain_already_existed' => $domainAlreadyExisted,
                'warnings' => $warnings,
                'local_zip' => $localZip,
                'remote_zip' => $remoteZip,
                'deploy_output' => $deployOutput,
                'aapanel_site' => $siteResult,
                'ssl' => $sslResult,
                'ssl_error' => $sslError,
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Main site deployment failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
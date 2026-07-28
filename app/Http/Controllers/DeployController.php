<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\SSHService;
use App\Services\DeploymentService;

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
       $path = $zip->storeAs(
            'uploads',
            $fileName,
            'public'
        );
        

        // return response()->json([
        //     'status' => true,
        //     'message' => 'ZIP uploaded successfully.',
        //     'domain' => $request->domain,
        //     'zip_path' => storage_path('app/' . $path)
        // ]);
        // $ssh = new SSHService();
        // $output = $ssh->execute('whoami');
        // return response()->json([
        //     'status' => true,
        //     'domain' => $request->domain,
        //     'ssh_output' => trim($output)
        // ]);
        // $deployment = new DeploymentService();
        // $output = $deployment->createWebsiteFolder(
        //     $request->domain
        // );
        // return response()->json([
        //     'status' => true,
        //     'domain' => $request->domain,
        //     'message' => 'Website folder created successfully.',
        //     'output' => trim($output)
        // ]);

        // $deployment = new DeploymentService();

        // $deployment->createWebsiteFolder($domain);

        // $remoteZip = $deployment->uploadZip(
        //     $domain,
        //     storage_path('app/'.$path)
        // );

        // return response()->json([
        //     'status' => true,
        //     'message' => 'ZIP uploaded successfully.',
        //     'remote_zip' => $remoteZip
        // ]);
        $deployment = new DeploymentService();

        $deployment->createWebsiteFolder($domain);

        $deployment->uploadZip(
            $domain,
            storage_path('app/'.$path)
        );

        $deployment->extractZip($domain);

        return response()->json([
            'status' => true,
            'message' => 'Website deployed successfully.'
        ]);

    }

   
}
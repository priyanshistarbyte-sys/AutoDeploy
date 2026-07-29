<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeployController;
 use App\Http\Controllers\MainSiteDeployController;

Route::get('/', [DeployController::class, 'index']);
Route::post('/deploy', [DeployController::class, 'deploy']);

 Route::post('/deploy-main-site', [MainSiteDeployController::class, 'deploy']);
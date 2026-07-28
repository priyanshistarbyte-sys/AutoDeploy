<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeployController;

Route::get('/', [DeployController::class, 'index']);
Route::post('/deploy', [DeployController::class, 'deploy']);
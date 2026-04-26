<?php

use App\Http\Controllers\Api\WorkflowController;
use Illuminate\Support\Facades\Route;

Route::apiResource('workflows', WorkflowController::class);

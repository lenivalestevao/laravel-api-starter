<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API routes (prefixed with /api automatically)
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:auth');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:auth');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('projects', ProjectController::class);

    // index/store live under /projects/{project}/tasks, the rest under /tasks/{task}
    Route::apiResource('projects.tasks', TaskController::class)->shallow();
});

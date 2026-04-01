<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return 'pong';
});

Route::get('tasks/report', [TaskController::class, 'report']);
Route::apiResource('tasks',TaskController::class)->only(['store', 'index','destroy']);
Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus']);

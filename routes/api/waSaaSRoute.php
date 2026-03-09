<?php

use App\Http\Controllers\Api\v1\WaSaaSController;
use Illuminate\Support\Facades\Route;

Route::prefix('/wasaas')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/send', [WaSaaSController::class, 'send']);
    Route::post('/send-media', [WaSaaSController::class, 'sendMedia']);
    Route::get('/{id}', [WaSaaSController::class, 'show']);
    Route::post('/{id}', [WaSaaSController::class, 'update']);
});

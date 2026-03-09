<?php

use App\Http\Controllers\Api\v1\LogFileController;
use App\Http\Controllers\Api\v1\SettingController;
use App\Http\Controllers\Api\v1\UtilityController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum', 'locale'])
    ->prefix('logs')
    ->group(function () {
        Route::get('/', [LogFileController::class, 'meta']);     // basic info & file size
        Route::get('/tail', [LogFileController::class, 'tail']);     // last N lines
        Route::get('/download', [LogFileController::class, 'download']); // download/open
        Route::post('/upload', [LogFileController::class, 'upload']);   // upload/replace log file
        Route::delete('/', [LogFileController::class, 'destroy']);  // delete/clear
    });

Route::prefix('/setting')->middleware(['auth:sanctum'])->group(function () {

    Route::get('/download-logs', [UtilityController::class, 'downloadLogs']);
    Route::post('/clear-route-cache', [UtilityController::class, 'clearRouteAndCache']);
    Route::post('/clear-logs', [UtilityController::class, 'clearLogs']);


    Route::get('/', [SettingController::class, 'index']);
    Route::get('/{id}', [SettingController::class, 'show']);
    Route::post('/store', [SettingController::class, 'store']);
    Route::post('/update/{id}', [SettingController::class, 'update']);
    Route::delete('/delete/{id}', [SettingController::class, 'destroy']);
});
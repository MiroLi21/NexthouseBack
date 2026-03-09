<?php

use App\Http\Controllers\Api\v1\PermissionController;
use App\Http\Controllers\Api\v1\RoleController;
use App\Http\Controllers\Api\v1\SectionController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/user')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/index', [UserController::class, 'index']);
    Route::get('/index-lite', [UserController::class, 'getLite']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::get('/action/top', [UserController::class, 'ActionTop']);
    Route::post('/', [UserController::class, 'store']);
    Route::post('/{id}', [UserController::class, 'update']);
    Route::delete('/delete/{id}', [UserController::class, 'destroy']);
});

Route::prefix('/role')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/index', [RoleController::class, 'index']);
    Route::post('/', [RoleController::class, 'store']);
    Route::get('/{id}', [RoleController::class, 'show']);
    Route::post('/{id}', [RoleController::class, 'update']);
    Route::delete('/delete/{id}', [RoleController::class, 'destroy']);
});
Route::prefix('/permission')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/index', [PermissionController::class, 'index']);
});

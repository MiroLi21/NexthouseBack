<?php

use App\Http\Controllers\api\v1\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('locale');
Route::get('/me', [AuthController::class, 'me'])->middleware(['auth:sanctum', 'locale']);
Route::get('/profile', [AuthController::class, 'profile'])->middleware(['auth:sanctum', 'locale']);

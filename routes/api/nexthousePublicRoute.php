<?php

use App\Http\Controllers\Api\v1\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| NextHouse Public Routes (No Auth Required - for Frontend)
|--------------------------------------------------------------------------
*/
Route::prefix('/public')->group(function () {

    // GET /api/public/services
    Route::get('/services', [PublicController::class, 'services']);

    // GET /api/public/services/{service_id}/categories
    Route::get('/services/{service_id}/categories', [PublicController::class, 'categories']);

    // GET /api/public/categories/{category_id}/projects
    Route::get('/categories/{category_id}/projects', [PublicController::class, 'projects']);
});

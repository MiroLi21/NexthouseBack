<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/api/authRoute.php';
require __DIR__ . '/api/userRoute.php';
require __DIR__ . '/api/settingRoute.php';
require __DIR__ . '/api/waSaaSRoute.php';
require __DIR__ . '/api/nexthouseRoute.php';
require __DIR__ . '/api/nexthousePublicRoute.php';

Route::get('/', function () {
    return 'NextHouse API running...';
});
Route::get(uri: '/check', action: function (): \Illuminate\Http\JsonResponse {
    return response()->json(data: ['state' => 'NextHouse API running...']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

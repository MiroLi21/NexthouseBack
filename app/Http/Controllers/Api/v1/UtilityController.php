<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UtilityController extends Controller
{
    public function downloadLogs()
    {
        $path = storage_path('logs/laravel.log');

        if (!file_exists($path)) {
            abort(404, "Log file not found");
        }

        return response()->download($path, 'laravel.log', [
            'Content-Type' => 'text/plain'
        ]);
    }

    public function clearRouteAndCache()
    {
        // Clear caches
        Artisan::call('route:clear');
        Artisan::call('cache:clear');
        // Immediately cache routes again
        // Artisan::call('route:cache');

        // Rebuild route cache for production
        if (app()->environment('production')) {
            Artisan::call('route:cache');
        }
        return response()->json([
            'message' => 'Routes and cache cleared successfully!'
        ]);

        // return response()->json([
        //     'message' => 'Routes and cache cleared successfully!'
        // ]);
    }
    public function clearLogs()
    {
        $path = storage_path('logs/laravel.log');

        if (file_exists($path)) {
            file_put_contents($path, ''); // truncate log
        }

        return response()->json([
            'message' => 'Laravel logs cleared successfully!'
        ]);
    }
}

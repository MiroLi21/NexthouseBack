<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocalizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! empty($request->header('locale')) and in_array($request->header('locale'), ['en', 'ar'])) {
            app()->setlocale($request->header('locale'));
        } else {
            // return abort(404);
            // return redirect()->route('404',['locale'=> $request->locale]);
        }

        return $next($request);
    }
}

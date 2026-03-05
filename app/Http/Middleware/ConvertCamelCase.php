<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConvertCamelCase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Debugging line to check if middleware is triggered
        //Log::info('ConvertCamelCase middleware triggered');

        // Convert request keys to snake case
        $convertedRequest = $this->convertKeys($request->all());

        // Debugging line to check converted keys
        //Log::info('Converted Request: ', $convertedRequest);

        $request->replace($convertedRequest);
        return $next($request);
    }

    /**
     * Convert the keys of an array to snake case.
     *
     * @param  array  $array
     * @return array
     */
    protected function convertKeys(array $array)
    {
        $converted = [];
        foreach ($array as $key => $value) {
            $converted[Str::snake($key)] = is_array($value) ? $this->convertKeys($value) : $value;
        }
        return $converted;
    }
}

<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WaSaaS\WaSaaSResource;
use App\Models\WaSaaS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class WaSaaSController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'header' => 'required|string',
            'message' => 'required|string',
            'footer' => 'required|string',
            'phone' => 'required|string',
            'enable' => 'required|boolean',
            'session_key' => 'required',
        ]);
        if (!$validated['enable']) {


            // Combine header, message and footer into one text
            $text = implode("\n\n", [
                $validated['header'],
                $validated['message'],
                $validated['footer']
            ]);

            $waSaaS = WaSaaS::find(1);
            if (!$waSaaS) {
                return response()->json(['error' => 'WaSaaS session not found.'], 404);
            }

            $apiKey = $waSaaS->xkey;
            $baseUrl = $waSaaS->path_url;

            if (!$apiKey || !$baseUrl) {
                return response()->json(['error' => 'WASAAS_API_KEY or WASAAS_BASE_URL are not configured.'], 500);
            }

            $payload = [
                'wa_session_id' => (int) $validated['session_key'],
                'to' => $validated['phone'],
                'text' => $text,
            ];

            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($baseUrl . '/v1/messages/text', $payload);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                return response()->json(['error' => $response->body()], $response->status());
            }
        } else {
            return response()->json(['success' => 'WaSaaS is not enabled.'], 202);
        }
    }
    public function sendMedia(Request $request)
    {
        $validated = $request->validate([
            'wa_session_id' => 'required|integer',
            'to' => 'required|string',
            'media_url' => 'required|url',
            'caption' => 'nullable|string',
        ]);

        $apiKey = env('WASAAS_API_KEY');
        $baseUrl = env('WASAAS_BASE_URL');

        if (!$apiKey || !$baseUrl) {
            return response()->json(['error' => 'WASAAS_API_KEY or WASAAS_BASE_URL are not configured in .env file.'], 500);
        }

        $response = Http::withHeaders([
            'X-API-KEY' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post($baseUrl . '/v1/messages/media', $validated);

        if ($response->successful()) {
            return response()->json($response->json());
        } else {
            return response()->json(['error' => $response->body()], $response->status());
        }
    }
    public function show($id)
    {
        $waSaaS = WaSaaS::find($id);
        if (!$waSaaS) {
            return $this->FailedResponse(__('general.notFound'));
        }
        return $this->ok(new WaSaaSResource($waSaaS));
    }
    public function update(Request $request, $id)
    {
        $waSaaS = WaSaaS::find($id);
        if (!$waSaaS) {
            return $this->FailedResponse(__('general.notFound'));
        }
        $validated = $request->validate([
            'session_key' => 'required|string',
            'path_url' => 'required|string',
            'xkey' => 'required|string',
            'enable' => 'required|boolean',
        ]);
        try {
            $waSaaS->update($validated);
            return $this->ok(new WaSaaSResource($waSaaS));
        } catch (\Exception $e) {
            Log::alert($e);
            return $this->FailedResponse(__('general.saveUnsuccessfully'));
            // return $this->FailedResponse($e->getMessage(), __('general.saveUnsuccessfully'));
        }
    }

}

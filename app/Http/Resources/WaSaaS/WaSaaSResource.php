<?php

namespace App\Http\Resources\WaSaaS;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaSaaSResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'xkey' => $this->xkey,
            'path_url' => $this->path_url,
            'session_key' => $this->session_key,
            'enable' => $this->enable,
        ];
    }
}


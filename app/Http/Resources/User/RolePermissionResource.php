<?php

namespace App\Http\Resources\User;

use App\Http\Resources\GeneralIdNameResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RolePermissionResource extends JsonResource
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
            'name' => $this->name,
            'permissions' => GeneralIdNameResource::collection($this->permissions),
        ];
    }
}

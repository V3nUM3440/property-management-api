<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use Illuminate\Http\Request;

class RoleResource extends AppJsonResource
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
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
            'permissions_count' => $this->whenCounted('permissions_count'),
            'users_count' => $this->whenCounted('users_count'),
        ];
    }
}

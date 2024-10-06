<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use Illuminate\Http\Request;

class UserResource extends AppJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $all = [
            'id' => $this->id,
            'name' => $this->name,
        ];
        $general = [
            'email' => $this->email,
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'units' => UnitResource::collection($this->whenLoaded('units')),
            'units_count' => $this->whenCounted('units'),
        ];
        return $request->per_page == 'all' ? $all : array_merge($all, $general);
    }
}

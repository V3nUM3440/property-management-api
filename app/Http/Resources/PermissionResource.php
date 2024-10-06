<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use Illuminate\Http\Request;

class PermissionResource extends AppJsonResource
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
        ];
    }
}

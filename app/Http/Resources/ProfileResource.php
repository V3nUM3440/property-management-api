<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileResource extends AppJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User $this */
        if ($this->hasRole('super-admin')) {
            $permisssions = ['*'];
        } else {
            $permisssions = $this->getAllPermissions()->pluck('name');
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'permissions' => $permisssions,
        ];
    }
}

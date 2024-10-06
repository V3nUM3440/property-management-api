<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use Illuminate\Http\Request;

class TenantResource extends AppJsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'dob' => $this->dob,
            'email' => $this->email,
            'phone' => $this->phone,
            'identity_number' => $this->identity_number,
            'identity_expiry' => $this->identity_expiry,
            'passport_number' => $this->passport_number,
            'passport_expiry' => $this->passport_expiry,
            'parent_id' => $this->parent_id,
            'security_deposit' => new SecurityDepositResource($this->whenLoaded('security_deposit')),
            'last_contract' => new ContractResource($this->whenLoaded('last_contract')),
            'contracts' => ContractResource::collection($this->whenLoaded('contracts')),
            'contracts_count' => $this->whenCounted('contracts'),
            'parent' => new TenantResource($this->whenLoaded('parent')),
            'subtenants' => TenantResource::collection($this->whenLoaded('subtenants')),
            'subtenants_count' => $this->whenCounted('subtenants'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

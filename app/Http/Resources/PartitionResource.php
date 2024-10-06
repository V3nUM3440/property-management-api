<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use Illuminate\Http\Request;

class PartitionResource extends AppJsonResource
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
            'size' => $this->size,
            'unit_id' => $this->unit_id,
            'unit' => new UnitResource($this->whenLoaded('unit')),
            'building' => new BuildingResource($this->whenLoaded('building')),
            'contracts' => ContractResource::collection($this->whenLoaded('contracts')),
            'contracts_count' => $this->whenCounted('contracts_count'),
            'active_contract' => new ContractResource($this->whenLoaded('active_contract')),
            'security_deposits' => SecurityDepositResource::collection($this->whenLoaded('security_deposits')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        return $request->per_page == 'all' ? $all : array_merge($all, $general);
    }
}

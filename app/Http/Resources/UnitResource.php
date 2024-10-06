<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use Illuminate\Http\Request;

class UnitResource extends AppJsonResource
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
            'building_id' => $this->building_id,
            'user_id' => $this->user_id,
            'floor' => $this->floor,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'size' => $this->size,
            'building' => new BuildingResource($this->whenLoaded('building')),
            'manager' => new UserResource($this->whenLoaded('manager')),
            'partitions' => PartitionResource::collection($this->whenLoaded('partitions')),
            'partitions_count' => $this->whenCounted('partitions_count'),
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

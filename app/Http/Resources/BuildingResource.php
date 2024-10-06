<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use Illuminate\Http\Request;

class BuildingResource extends AppJsonResource
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
            'type' => $this->type,
            'size' => $this->size,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'units' => UnitResource::collection($this->whenLoaded('units')),
            'units_count' => $this->whenCounted('units'),
            'partitions' => PartitionResource::collection($this->whenLoaded('partitions')),
            'partitions_count' => $this->whenCounted('partitions'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
        return $request->per_page == 'all' ? $all : array_merge($all, $general);
    }
}

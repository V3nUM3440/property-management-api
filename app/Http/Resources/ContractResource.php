<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use App\Models\Unit;
use Illuminate\Http\Request;

class ContractResource extends AppJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = $this->contractable_type == Unit::class ? 'Unit' : 'Partition';
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'rent' => $this->rent,
            'discount' => $this->discount,
            'final_rent' => $this->final_rent,
            'tenant_id' => $this->tenant_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'contractable_type' => $type,
            'contractable_id' => $this->contractable_id,
            'contractable' => $this->when($this->relationLoaded('contractable'), function () {
                if ($this->contractable_type == Unit::class) {
                    return new UnitResource($this->contractable);
                }
                return new PartitionResource($this->contractable);
            }),
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
            'payments' => $this->whenLoaded('payments'),
            'payments_count' => $this->whenCounted('payments'),
            'total_paid' => $this->whenLoaded('payments', function ($query) {
                return $query->where('status', 'paid')->sum('amount');
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}

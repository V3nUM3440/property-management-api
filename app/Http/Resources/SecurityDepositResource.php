<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use App\Models\Unit;
use Illuminate\Http\Request;

class SecurityDepositResource extends AppJsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $type = $this->depositable_type == Unit::class ? 'Unit' : 'Partition';
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'receive_date' => $this->receive_date,
            'return_date' => $this->return_date,
            'tenant_id' => $this->tenant_id,
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
            'depositable_type' => $type,
            'depositable_id' => $this->depositable_id,
            'depositable' => $this->when($this->relationLoaded('depositable'), function () {
                if ($this->depositable_type == Unit::class) {
                    return new UnitResource($this->depositable);
                }
                return new PartitionResource($this->depositable);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

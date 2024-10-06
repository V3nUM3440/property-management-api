<?php

namespace App\Http\Resources;

use App\Http\Resources\Core\AppJsonResource;
use App\Models\Unit;
use Illuminate\Http\Request;

class PaymentResource extends AppJsonResource
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
            'reference' => $this->reference,
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => $this->status,
            'datetime' => $this->datetime,
            'contract_id' => $this->contract_id,
            'contract' => new ContractResource($this->whenLoaded('contract')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

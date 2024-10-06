<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SecurityDeposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'depositable_type',
        'depositable_id',
        'tenant_id',
        'amount',
        'receive_date',
        'return_date',
    ];

    public function depositable(): MorphTo
    {
        return $this->morphTo();
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

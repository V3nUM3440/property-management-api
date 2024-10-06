<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Partition extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'name',
        'size',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function building(): HasOneThrough
    {
        return $this->hasOneThrough(Building::class, Unit::class, 'id', 'id', 'unit_id', 'building_id');
    }

    public function active_contract(): MorphOne
    {
        return $this->morphOne(Contract::class, 'contractable')
        ->ofMany([
            'start_date' => 'max',
            'end_date' => 'max',
        ], function (Builder $query) {
            $query->where('start_date', '<', now());
            $query->where('end_date', '>=', now());
        });
    }

    public function contracts(): MorphMany
    {
        return $this->morphMany(Contract::class, 'contractable');
    }

    public function security_deposits(): MorphMany
    {
        return $this->morphMany(SecurityDeposit::class, 'depositable');
    }
}

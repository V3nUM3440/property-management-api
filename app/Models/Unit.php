<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'user_id',
        'name',
        'floor',
        'bedrooms',
        'bathrooms',
        'size',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function partitions(): HasMany
    {
        return $this->hasMany(Partition::class);
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

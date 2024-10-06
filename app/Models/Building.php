<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'size',
        'address',
        'city',
        'state',
        'country',
    ];

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function partitions(): HasManyThrough
    {
        return $this->hasManyThrough(Partition::class, Unit::class);
    }
}

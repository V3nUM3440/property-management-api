<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'dob',
        'email',
        'phone',
        'identity_number',
        'identity_expiry',
        'passport_number',
        'passport_expiry',
        'parent_id',
    ];

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'parent_id');
    }

    public function subtenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'parent_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class)->withTrashed();
    }

    public function last_contract(): HasOne
    {
        return $this->hasOne(Contract::class)->orderBy('end_date', 'desc')->withTrashed();
    }

    public function security_deposit(): HasOne
    {
        return $this->hasOne(SecurityDeposit::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'status', 'description'])]
class Tenant extends Model
{
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function paymentConfigs(): HasMany
    {
        return $this->hasMany(TenantPaymentConfig::class);
    }

    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }
}

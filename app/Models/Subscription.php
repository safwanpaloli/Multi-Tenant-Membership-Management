<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'membership_id',
    'status',
    'billing_cycle',
    'price',
    'is_free_allocation',
    'starts_at',
    'expires_at',
])]
class Subscription extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_free_allocation' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}

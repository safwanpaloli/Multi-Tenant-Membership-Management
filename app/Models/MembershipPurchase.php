<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'consumer_id',
    'membership_id',
    'payment_id',
    'payment_provider',
    'billing_cycle',
    'amount',
    'currency',
    'status',
    'metadata',
    'purchased_at',
])]
class MembershipPurchase extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'purchased_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function consumer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consumer_id');
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}

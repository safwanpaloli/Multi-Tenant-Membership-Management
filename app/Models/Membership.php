<?php

namespace App\Models;

use App\Enums\MembershipStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'name',
    'description',
    'benefits',
    'price',
    'monthly_price',
    'yearly_price',
    'free_membership_limit',
    'status',
])]
class Membership extends Model
{
    /** @use HasFactory<MembershipFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'price' => 'decimal:2',
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'free_membership_limit' => 'integer',
            'status' => MembershipStatus::class,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}

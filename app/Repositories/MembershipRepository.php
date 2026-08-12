<?php

namespace App\Repositories;

use App\Models\Membership;
use Illuminate\Pagination\LengthAwarePaginator;

class MembershipRepository
{
    /**
     * Get paginated memberships for a specific tenant.
     */
    public function paginateForTenant(int $tenantId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Membership::query()->where('tenant_id', $tenantId);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortField = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_dir'] ?? 'desc';
        
        $allowedSorts = ['name', 'price', 'created_at', 'status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    /**
     * Find a specific membership belonging to a tenant or fail.
     */
    public function findForTenantOrFail(int $tenantId, int $id): Membership
    {
        return Membership::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);
    }

    /**
     * Create a new membership for a tenant.
     */
    public function createForTenant(int $tenantId, array $data): Membership
    {
        return Membership::create(array_merge(
            ['tenant_id' => $tenantId],
            $data
        ));
    }

    /**
     * Update an existing membership.
     */
    public function update(Membership $membership, array $data): bool
    {
        return $membership->update($data);
    }

    /**
     * Delete a membership.
     */
    public function delete(Membership $membership): bool
    {
        return $membership->delete();
    }
}

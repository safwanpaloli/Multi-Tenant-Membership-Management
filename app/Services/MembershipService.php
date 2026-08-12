<?php

namespace App\Services;

use App\Models\Membership;
use App\Repositories\MembershipRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class MembershipService
{
    protected MembershipRepository $membershipRepository;

    public function __construct(MembershipRepository $membershipRepository)
    {
        $this->membershipRepository = $membershipRepository;
    }

    /**
     * Get paginated memberships for a tenant.
     */
    public function getMembershipsForTenant(int $tenantId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->membershipRepository->paginateForTenant($tenantId, $filters, $perPage);
    }

    /**
     * Create a new membership for a tenant.
     */
    public function createMembership(int $tenantId, array $data): Membership
    {
        // Any additional business logic (e.g., checking limits) would go here
        return $this->membershipRepository->createForTenant($tenantId, $data);
    }

    /**
     * Find a membership belonging to a tenant.
     */
    public function getMembershipForTenant(int $tenantId, int $membershipId): Membership
    {
        return $this->membershipRepository->findForTenantOrFail($tenantId, $membershipId);
    }

    /**
     * Update an existing membership for a tenant.
     */
    public function updateMembership(int $tenantId, int $membershipId, array $data): Membership
    {
        $membership = $this->membershipRepository->findForTenantOrFail($tenantId, $membershipId);
        
        $this->membershipRepository->update($membership, $data);
        
        return $membership->fresh();
    }

    /**
     * Delete a membership for a tenant.
     */
    public function deleteMembership(int $tenantId, int $membershipId): void
    {
        $membership = $this->membershipRepository->findForTenantOrFail($tenantId, $membershipId);
        
        $this->membershipRepository->delete($membership);
    }
}

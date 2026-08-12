<?php

namespace App\Repositories;

use App\Models\Tenant;

class TenantRepository
{
    /**
     * Find a tenant by its slug or fail.
     */
    public function findBySlugOrFail(string $slug): Tenant
    {
        return Tenant::query()->where('slug', $slug)->firstOrFail();
    }
}

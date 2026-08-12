<?php

namespace App\Enums;

enum PermissionSlug: string
{
    case MembershipView = 'membership.view';
    case MembershipCreate = 'membership.create';
    case MembershipUpdate = 'membership.update';
    case MembershipDelete = 'membership.delete';
    case PurchaseView = 'purchase.view';
    case TenantSettingsView = 'tenant.settings.view';
    case TenantSettingsUpdate = 'tenant.settings.update';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

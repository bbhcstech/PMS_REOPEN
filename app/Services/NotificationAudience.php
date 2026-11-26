<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Who a notification is for.
 *  - System/settings updates ("... Settings Updated", operating-days changes) are not notifications:
 *    they are never sent, and older ones are hidden from every Notification Center.
 *  - Role & Permission changes reach only the users that role's permissions apply to.
 */
class NotificationAudience
{
    public const SETTING_UPDATE = 'setting_update';
    public const PERMISSION_UPDATE = 'permission_update';

    public static function isSystemUpdate(array $data): bool
    {
        return ($data['type'] ?? null) === self::SETTING_UPDATE;
    }

    /**
     * Excludes system-update notices from a notifications query, including older broadcasts
     * (the working-days broadcast is the only one that stored "working_days").
     */
    public static function withoutSystemUpdates($query)
    {
        return $query->where(function ($visible) {
            $visible->where(function ($type) {
                $type->whereNull('data->type')->orWhere('data->type', '!=', self::SETTING_UPDATE);
            })->whereNull('data->working_days');
        });
    }

    /** Users whose permissions come from the given Role & Permission role key ("hr", "staff:1:4", ...). */
    public static function usersForPermissionRole(int $companyId, string $roleKey): Collection
    {
        return User::query()
            ->where('company_id', $companyId)
            ->whereNull('archived_at')
            ->where(function ($query) {
                $query->where('is_active', true)->orWhereNull('is_active');
            })
            ->get()
            ->filter(fn (User $user) => ! in_array($user->normalizedRole(), ['admin', 'superadmin'], true)
                && $user->permissionRole() === $roleKey)
            ->values();
    }
}

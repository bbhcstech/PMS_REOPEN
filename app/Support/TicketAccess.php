<?php

namespace App\Support;

/**
 * The company Admin Workspace uses "Platform Support & Complaints" for support,
 * so the internal ticket system is not offered there (one support feature, not two).
 * HR, managers, employees and clients keep using tickets as before.
 */
class TicketAccess
{
    public const ADMIN_WORKSPACE_ROLES = ['admin', 'superadmin', 'administrator'];

    public static function hiddenForCurrentUser(): bool
    {
        $role = strtolower(trim((string) (auth()->user()?->role ?? '')));

        return in_array($role, self::ADMIN_WORKSPACE_ROLES, true);
    }

    public static function isTicketRoute(?string $routeName): bool
    {
        if (! $routeName) {
            return false;
        }

        return str_starts_with($routeName, 'tickets.')
            || str_starts_with($routeName, 'ticket-groups.')
            || $routeName === 'dashboard.ticket';
    }
}

<?php

namespace App\Support;

/** Company tickets are separate from the central Platform Support channel. */
class TicketAccess
{
    public const ADMIN_WORKSPACE_ROLES = ['admin', 'superadmin', 'administrator'];

    public static function hiddenForCurrentUser(): bool
    {
        return ! auth()->user()?->company_id;
    }

    public static function canManage(): bool
    {
        $user = auth()->user();
        return $user && $user->company_id && (\App\Services\WorkforceAccess::isAdmin($user)
            || in_array(strtolower((string) $user->role), ['hr', 'manager'], true));
    }

    public static function authorize(\App\Models\Ticket $ticket, bool $manage = false, bool $progress = false): void
    {
        $user = auth()->user();
        abort_unless($user && $user->company_id, 403);
        $companyId = $ticket->company_id ?? $ticket->requester?->company_id;
        \App\Services\TenantScope::authorizeCompany($companyId);
        abort_unless(self::canManage() || (!$manage && ($progress
            ? (int) $ticket->agent_id === (int) $user->id
            : in_array((int) $user->id, [(int) $ticket->agent_id, (int) $ticket->requester_id], true))), 403);
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

<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Database\Eloquent\Collection;

class SystemNotificationService
{
    public const ERP_ROLES = ['admin', 'manager', 'hr', 'employee', 'user'];

    private static function companyId(?int $requested = null): int
    {
        $current = TenantScope::companyId();
        if ($current && $requested && $current !== $requested) {
            throw new \LogicException('Notifications cannot cross company workspaces.');
        }
        $companyId = $current ?: $requested;
        if (!$companyId) throw new \LogicException('A company is required for tenant notifications.');
        return $companyId;
    }

    public static function roleUsers(?int $companyId = null): Collection
    {
        return User::query()
            ->whereIn('role', self::ERP_ROLES)
            ->where(function ($query) {
                $query->where('is_active', true)->orWhereNull('is_active');
            })
            ->where('company_id', self::companyId($companyId))
            ->get();
    }

    public static function adminsAndHr(?int $companyId = null): Collection
    {
        return User::query()
            ->whereIn('role', ['admin', 'hr'])
            ->where('company_id', self::companyId($companyId))
            ->get();
    }

    public static function adminsHrManagers(?int $companyId = null): Collection
    {
        return User::query()
            ->whereIn('role', ['admin', 'hr', 'manager'])
            ->where('company_id', self::companyId($companyId))
            ->get();
    }

    public static function employees(?int $companyId = null): Collection
    {
        return User::query()
            ->where('role', 'employee')
            ->where('company_id', self::companyId($companyId))
            ->get();
    }

    public static function admins(?int $companyId = null): Collection
    {
        return User::query()
            ->where('role', 'admin')
            ->where('company_id', self::companyId($companyId))
            ->get();
    }

    public static function notifyAdmins(string $title, string $message, ?string $url = null, array $data = []): void
    {
        $actor = auth()->user();
        $companyId = $actor?->company_id;

        self::send(self::admins($companyId), $title, $message, $url, $data + [
            'type' => 'employee_to_admin',
            'icon' => 'fa-user-clock',
            'color' => 'info',
        ]);
    }

    public static function notifyEmployees(string $title, string $message, ?string $url = null, array $data = []): void
    {
        self::notifyAllRoles($title, $message, $url, $data + [
            'type' => 'admin_to_employee',
            'icon' => 'fa-shield-halved',
            'color' => 'warning',
        ]);
    }

    public static function notifyAllRoles(string $title, string $message, ?string $url = null, array $data = [], ?int $companyId = null): void
    {
        $actor = auth()->user();
        $companyId ??= $actor?->company_id;

        $companyId = self::companyId($companyId);
        self::send(self::roleUsers($companyId), $title, $message, $url, ['company_id' => $companyId] + $data + [
            'type' => 'erp_activity',
            'icon' => 'fa-bell',
            'color' => 'info',
            'actor_role' => $actor?->role,
            'audience' => 'all_roles',
        ]);
    }

    public static function notifyUser($users, string $title, string $message, ?string $url = null, array $data = []): void
    {
        if ($users instanceof User) {
            $users = collect([$users]);
        } elseif (is_array($users)) {
            $users = User::whereIn('id', $users)->get();
        } elseif (is_numeric($users)) {
            $users = User::where('id', $users)->get();
        }

        self::send($users, $title, $message, $url, $data);
    }

    public static function send($users, string $title, string $message, ?string $url = null, array $data = []): void
    {
        $companyId = self::companyId(isset($data['company_id']) ? (int) $data['company_id'] : null);
        try {
            if (! \Illuminate\Support\Facades\Schema::connection('tenant')->hasTable('notifications')) {
                return;
            }

            collect($users)
                ->filter()
                ->filter(fn (User $user) => (int) $user->company_id === $companyId
                    && $user->getConnection()->getDatabaseName() === \Illuminate\Support\Facades\DB::connection('tenant')->getDatabaseName())
                ->unique('id')
                ->each(function (User $user) use ($title, $message, $url, $data, $companyId) {
                    try {
                        $user->notify(new SystemNotification(['company_id' => $companyId] + $data + [
                            'title' => $title,
                            'message' => $message,
                            'url' => $url,
                            'actor_id' => auth()->id(),
                            'actor_name' => auth()->user()?->name,
                        ]));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("SystemNotification send failed for user {$user->id}: " . $e->getMessage());
                    }
                });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("SystemNotificationService::send top-level failed: " . $e->getMessage());
        }
    }
}

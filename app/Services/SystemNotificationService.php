<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Database\Eloquent\Collection;

class SystemNotificationService
{
    public static function adminsAndHr(?int $companyId = null): Collection
    {
        return User::query()
            ->whereIn('role', ['admin', 'hr'])
            ->when($companyId, fn ($query) => $query->where(function ($companyQuery) use ($companyId) {
                $companyQuery->where('company_id', $companyId)->orWhereNull('company_id');
            }))
            ->get();
    }

    public static function adminsHrManagers(?int $companyId = null): Collection
    {
        return User::query()
            ->whereIn('role', ['admin', 'hr', 'manager'])
            ->when($companyId, fn ($query) => $query->where(function ($companyQuery) use ($companyId) {
                $companyQuery->where('company_id', $companyId)->orWhereNull('company_id');
            }))
            ->get();
    }

    public static function employees(?int $companyId = null): Collection
    {
        return User::query()
            ->where('role', 'employee')
            ->when($companyId, fn ($query) => $query->where(function ($companyQuery) use ($companyId) {
                $companyQuery->where('company_id', $companyId)->orWhereNull('company_id');
            }))
            ->get();
    }

    public static function notifyAdmins(string $title, string $message, ?string $url = null, array $data = []): void
    {
        self::send(self::adminsAndHr(auth()->user()?->company_id), $title, $message, $url, $data + [
            'type' => 'employee_to_admin',
            'icon' => 'fa-user-clock',
            'color' => 'info',
        ]);
    }

    public static function notifyEmployees(string $title, string $message, ?string $url = null, array $data = []): void
    {
        self::send(self::employees(auth()->user()?->company_id), $title, $message, $url, $data + [
            'type' => 'admin_to_employee',
            'icon' => 'fa-shield-halved',
            'color' => 'warning',
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
        collect($users)
            ->filter()
            ->unique('id')
            ->each(function (User $user) use ($title, $message, $url, $data) {
                $user->notify(new SystemNotification($data + [
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                    'actor_id' => auth()->id(),
                    'actor_name' => auth()->user()?->name,
                ]));
            });
    }
}

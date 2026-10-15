<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class WorkforceAccess
{
    public static function isHr(?User $user): bool
    {
        return $user && strtolower((string) $user->role) === 'hr';
    }

    public static function canManageLeaves(?User $user): bool
    {
        return static::isAdmin($user) || static::isHr($user);
    }

    public static function canAccessRecord(?User $actor, User $owner, ?string $category = null): bool
    {
        if (! $actor || (int) $actor->company_id !== (int) $owner->company_id) return false;
        if (static::isAdmin($actor)) return true;
        if ($category === 'authority' || static::isAuthority($owner)) return false;
        return static::isHr($actor) || (int) $actor->id === (int) $owner->id;
    }
    public static function isAdmin(?User $user): bool
    {
        return $user && ! $user->company_staff_role_id
            && in_array(strtolower((string) $user->role), ['admin', 'administrator', 'superadmin'], true);
    }

    public static function isAuthority(User $user): bool
    {
        return (bool) $user->company_staff_role_id
            || in_array(strtolower((string) $user->role), ['hr', 'manager', 'developer', 'dev'], true);
    }

    public static function users(Builder $query, string $category): Builder
    {
        $hasRoles = $query->getModel()->getConnection()->getSchemaBuilder()->hasColumn('users', 'company_staff_role_id');
        return $query->where(function ($q) use ($category, $hasRoles) {
            if ($category === 'authority') {
                $q->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(role)'), ['hr', 'manager', 'developer', 'dev']);
                if ($hasRoles) $q->orWhereNotNull('company_staff_role_id');
            } else {
                $q->whereRaw('LOWER(role) = ?', ['employee']);
                if ($hasRoles) $q->whereNull('company_staff_role_id');
            }
        });
    }

    public static function authorizeActor(): User
    {
        $user = auth()->user();
        abort_unless($user, 401);
        $companyId = TenantScope::companyFilter();
        abort_unless($companyId && (int) $user->company_id === $companyId, 403);
        $company = \App\Models\Company::findOrFail($companyId);
        abort_unless($company->db_name === \Illuminate\Support\Facades\DB::connection('tenant')->getDatabaseName(), 403, 'Company workspace does not match this account.');
        // Older employee accounts may predate these flags. Only an explicit
        // disablement should revoke access for an already authenticated user.
        abort_if($user->archived_at || $user->is_active === false || $user->login_allowed === false, 403, 'This account is inactive or login access has been disabled.');
        if ($user->company_staff_role_id) {
            $role = \App\Models\CompanyStaffRole::where('company_id', $companyId)->findOrFail($user->company_staff_role_id);
            abort_unless($role->access_role === $user->role, 403);
        }
        return $user;
    }

    public static function records(Builder $query, string $category): Builder
    {
        $table = $query->getModel()->getTable();
        if (! $query->getModel()->getConnection()->getSchemaBuilder()->hasColumn($table, 'staff_category')) {
            return $query->whereHas('user', fn ($user) => static::users($user, $category));
        }
        return $query->where(fn ($q) => $q->where($table . '.staff_category', $category)
            ->orWhere(fn ($legacy) => $legacy->whereNull($table . '.staff_category')->whereHas('user', fn ($user) => static::users($user, $category))));
    }

    public static function usersWithHistory(Builder $query, string $category, string $relation): Builder
    {
        return $query->where(function ($q) use ($category, $relation) {
            static::users($q, $category);
            $table = $relation === 'attendances' ? 'attendances' : 'leaves';
            if ($q->getModel()->getConnection()->getSchemaBuilder()->hasColumn($table, 'staff_category')) {
                $q->orWhereHas($relation, fn ($records) => $records->where('staff_category', $category));
            }
        });
    }

    public static function snapshot(User $user): array
    {
        $detail = $user->employeeDetail;
        return [
            'staff_category' => static::isAuthority($user) ? 'authority' : 'employee',
            'staff_role_name' => $user->companyStaffRole?->name ?? ucfirst((string) $user->role),
            'staff_designation' => $detail?->designation?->name,
            'staff_designation_level' => $detail?->designation?->level,
        ];
    }

    public static function currentAttendance(User $user): ?\App\Models\Attendance
    {
        $columns = (new \App\Models\Attendance)->getConnection()->getSchemaBuilder()->getColumnListing('attendances');
        if (array_diff(['id', 'user_id', 'date'], $columns)) return null;
        $timezone = session('attendance_timezone.' . $user->id);
        if (! $timezone || ! in_array($timezone, timezone_identifiers_list(), true)) {
            $lastTz = in_array('clock_in_timezone', $columns, true) ? \App\Models\Attendance::where('user_id', $user->id)
                ->whereNotNull('clock_in_timezone')
                ->latest('id')
                ->value('clock_in_timezone') : null;
            $timezone = ($lastTz && in_array($lastTz, timezone_identifiers_list(), true))
                ? $lastTz
                : config('app.timezone', 'Asia/Kolkata');
        }
        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            $timezone = 'Asia/Kolkata';
        }
        $query = \App\Models\Attendance::where('user_id', $user->id);
        $open = !array_diff(['clock_in', 'clock_out'], $columns)
            ? (clone $query)->whereNotNull('clock_in')->whereNull('clock_out')->latest('date')->latest('id')->first() : null;
        return $open ?? $query->whereDate('date', now($timezone)->toDateString())->latest('id')->first();
    }
}

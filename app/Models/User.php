<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    protected $connection = 'tenant';
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (! empty($user->company_id)) {
                try {
                    $connectionName = $user->getConnectionName() ?: config('database.default', 'mysql');
                    $hasCompaniesTable = \Illuminate\Support\Facades\Schema::connection($connectionName)->hasTable('companies');

                    if ($hasCompaniesTable) {
                        $exists = \Illuminate\Support\Facades\DB::connection($connectionName)
                            ->table('companies')
                            ->where('id', $user->company_id)
                            ->exists();

                        if (! $exists) {
                            $centralCompany = \App\Models\Central\Company::on('central')->find($user->company_id)
                                ?? \App\Models\Company::on('central')->find($user->company_id);

                            if ($centralCompany) {
                                static::syncCompanyToConnection($connectionName, $centralCompany);
                            }
                        }

                        $existsNow = \Illuminate\Support\Facades\DB::connection($connectionName)
                            ->table('companies')
                            ->where('id', $user->company_id)
                            ->exists();

                        if (! $existsNow) {
                            $user->company_id = null;
                        }
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Company check/sync in User model failed: " . $e->getMessage());
                }
            }
        });
    }

    public static function syncCompanyToConnection(string $connectionName, $centralCompany): void
    {
        try {
            $schema = \Illuminate\Support\Facades\Schema::connection($connectionName);
            if (! $schema->hasTable('companies')) {
                return;
            }

            $columns = $schema->getColumnListing('companies');
            $data = [];

            if (in_array('name', $columns)) {
                $data['name'] = $centralCompany->name;
            }
            if (in_array('company_name', $columns)) {
                $data['company_name'] = $centralCompany->name;
            }
            if (in_array('email', $columns)) {
                $data['email'] = $centralCompany->email;
            }
            if (in_array('company_email', $columns)) {
                $data['company_email'] = $centralCompany->email;
            }
            if (in_array('phone', $columns)) {
                $data['phone'] = $centralCompany->phone ?? null;
            }
            if (in_array('company_phone', $columns)) {
                $data['company_phone'] = $centralCompany->phone ?? null;
            }
            if (in_array('website', $columns)) {
                $data['website'] = $centralCompany->website ?? null;
            }
            if (in_array('company_website', $columns)) {
                $data['company_website'] = $centralCompany->website ?? null;
            }
            if (in_array('logo', $columns)) {
                $data['logo'] = $centralCompany->logo ?? null;
            }
            if (in_array('domain', $columns)) {
                $data['domain'] = $centralCompany->domain ?? null;
            }
            if (in_array('subdomain', $columns)) {
                $data['subdomain'] = $centralCompany->subdomain ?? null;
            }
            if (in_array('address', $columns)) {
                $data['address'] = $centralCompany->address ?? null;
            }
            if (in_array('status', $columns)) {
                $data['status'] = $centralCompany->status ?? 'active';
            }
            if (in_array('max_users', $columns)) {
                $data['max_users'] = $centralCompany->max_users ?? 100;
            }
            if (in_array('max_projects', $columns)) {
                $data['max_projects'] = $centralCompany->max_projects ?? 50;
            }
            if (in_array('max_clients', $columns)) {
                $data['max_clients'] = $centralCompany->max_clients ?? 100;
            }
            if (in_array('max_storage_mb', $columns)) {
                $data['max_storage_mb'] = $centralCompany->max_storage_mb ?? 10000;
            }
            if (in_array('created_at', $columns)) {
                $data['created_at'] = now();
            }
            if (in_array('updated_at', $columns)) {
                $data['updated_at'] = now();
            }

            if (! empty($data)) {
                \Illuminate\Support\Facades\DB::connection($connectionName)
                    ->table('companies')
                    ->updateOrInsert(['id' => $centralCompany->id], $data);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("syncCompanyToConnection error on connection {$connectionName}: " . $e->getMessage());
        }
    }

    protected $fillable = [
        'name',
        'company_id',
        'email',
        'password',
        'role',
        'is_active',
        'mobile',
        'designation',
        'gender',
        'dob',
        'government_id_card',
        'government_id_verification_status',
        'marital_status',
        'address',
        'about',
        'country',
        'language',
        'slack_id',
        'email_notify',
        'google_calendar',
        'profile_image',
        'login_allowed',
        'archived_at',
        'email_notifications',
        'employee_welcome_seen_at',
        'reports_to_id',
        'manager_id',
        'hr_id',
        // ADD THESE NEW FIELDS:
        'joining_date',
        'annual_leave_balance',
        'leaves_taken_this_year',
        'remaining_leaves',
        'leave_amount',
        'last_leave_reset',
        'carry_forward_leaves',
        'password_changed_notice',
        'password_changed_by_role',
        'password_changed_at',
        'raw_password',
        'must_change_password',
        'personal_email',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'login_allowed' => 'boolean',
            'must_change_password' => 'boolean',
            'archived_at' => 'datetime',
            'email_notifications' => 'boolean',
            'employee_welcome_seen_at' => 'datetime',
            'password_changed_notice' => 'boolean',
            'password_changed_at' => 'datetime',
            'joining_date' => 'date',
            'last_leave_reset' => 'date',
            'annual_leave_balance' => 'integer',
            'leaves_taken_this_year' => 'integer',
            'remaining_leaves' => 'integer',
            'leave_amount' => 'decimal:2',
            'carry_forward_leaves' => 'integer',
        ];
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function hr()
    {
        return $this->belongsTo(User::class, 'hr_id');
    }

    public function reportsTo()
    {
        return $this->belongsTo(User::class, 'reports_to_id');
    }

    public function directReports()
    {
        return $this->hasMany(User::class, 'reports_to_id');
    }

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'role', 'role');
    }

    public function normalizedRole(): string
    {
        return strtolower((string) $this->role);
    }

    public function hasModulePermission(string $moduleSlug, string $permission = 'view'): bool
    {
        // Platform Super Admin (central guard) bypasses company feature checks
        if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            return true;
        }

        // Check company-level feature entitlement/override for the company
        try {
            $company = app(\App\Services\CompanyContext::class)->current();
            if (! $company && $this->company_id) {
                $company = \App\Models\Company::find($this->company_id);
            }
            if ($company && method_exists($company, 'hasFeature')) {
                if (! $company->hasFeature($moduleSlug)) {
                    return false;
                }
            }
        } catch (\Throwable $e) {}

        if (in_array($this->normalizedRole(), ['admin', 'superadmin'], true)) {
            return true;
        }

        $column = 'can_' . $permission;
        if (! in_array($column, ['can_view', 'can_create', 'can_edit', 'can_delete', 'can_approve', 'can_export', 'can_assign'], true)) {
            return false;
        }

        return RolePermission::query()
            ->where('role', $this->normalizedRole())
            ->where($column, true)
            ->whereHas('module', function ($query) use ($moduleSlug) {
                $query->where('slug', $moduleSlug)->where('is_active', true);
            })
            ->exists();
    }

    public function canViewModule(string $moduleSlug): bool
    {
        return $this->hasModulePermission($moduleSlug, 'view');
    }

    public function visibleEmployeeIds()
    {
        $role = $this->normalizedRole();

        if ($role === 'admin') {
            return User::where('role', 'employee')
                ->when($this->company_id, fn ($query) => $query->where('company_id', $this->company_id))
                ->pluck('id');
        }

        if ($role === 'employee') {
            return collect([$this->id]);
        }

        if ($role === 'hr') {
            return User::where('role', 'employee')
                ->when($this->company_id, fn ($query) => $query->where('company_id', $this->company_id))
                ->where(function ($query) {
                    $query->where('hr_id', $this->id)
                        ->orWhere('reports_to_id', $this->id)
                        ->orWhereHas('employeeDetail', fn ($detail) => $detail->where('reporting_to', $this->id));
                })
                ->pluck('id');
        }

        if ($role === 'manager') {
            $hrIds = User::where('role', 'hr')
                ->when($this->company_id, fn ($query) => $query->where('company_id', $this->company_id))
                ->where(function ($query) {
                    $query->where('manager_id', $this->id)->orWhere('reports_to_id', $this->id);
                })
                ->pluck('id');

            return User::where('role', 'employee')
                ->when($this->company_id, fn ($query) => $query->where('company_id', $this->company_id))
                ->where(function ($query) use ($hrIds) {
                    $query->where('manager_id', $this->id)
                        ->orWhere('reports_to_id', $this->id)
                        ->orWhereHas('employeeDetail', fn ($detail) => $detail->where('reporting_to', $this->id));

                    if ($hrIds->isNotEmpty()) {
                        $query->orWhereIn('hr_id', $hrIds)
                            ->orWhereIn('reports_to_id', $hrIds)
                            ->orWhereHas('employeeDetail', fn ($detail) => $detail->whereIn('reporting_to', $hrIds));
                    }
                })
                ->pluck('id');
        }

        return collect();
    }

    public function employeeDetail()
    {
        return $this->hasOne(EmployeeDetail::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'user_id');
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class, 'user_id');
    }

    public function leaveApologyLetters()
    {
        return $this->hasMany(LeaveApologyLetter::class);
    }

    public function projects()
    {
        return $this->belongsToMany(\App\Models\Project::class, 'project_user', 'user_id', 'project_id')
                    ->withPivot('hourly_rate', 'role')
                    ->withTimestamps();
    }

    // ADD THESE NEW RELATIONSHIPS FOR LEAVE SYSTEM:
    public function leaveBalances()
    {
        return $this->hasMany(\App\Models\LeaveBalance::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(\App\Models\Leave::class, 'user_id');
    }

    public function currentYearBalance()
    {
        $currentYear = date('Y');
        return $this->hasOne(\App\Models\LeaveBalance::class)->where('year', $currentYear);
    }

    /**
     * Calculate pro-rated leaves based on joining date
     */
    public function calculateProRatedLeaves($annualLeaves = 18, $fiscalYearStart = '04-01')
    {
        if (!$this->joining_date) {
            return $annualLeaves; // Full leaves if no joining date
        }

        $joinDate = Carbon::parse($this->joining_date);
        $currentYear = date('Y');

        // Determine fiscal year
        $fiscalStart = Carbon::createFromFormat('m-d', $fiscalYearStart)->year($currentYear);

        // If current date is before fiscal start, use previous year
        if (Carbon::now()->lt($fiscalStart)) {
            $fiscalStart = $fiscalStart->subYear();
        }

        $fiscalEnd = $fiscalStart->copy()->addYear()->subDay();

        // If employee joined after fiscal year start
        if ($joinDate->gt($fiscalStart)) {
            $monthsRemaining = $joinDate->diffInMonths($fiscalEnd);
            if ($monthsRemaining > 0) {
                $proRatedLeaves = floor(($annualLeaves / 12) * $monthsRemaining);
                return max(1, $proRatedLeaves); // At least 1 leave
            }
            return 0;
        }

        return $annualLeaves; // Joined before fiscal year start
    }

    /**
     * Get leave utilization percentage
     */
    public function getLeaveUtilizationPercentage()
    {
        if ($this->annual_leave_balance <= 0) {
            return 0;
        }

        $used = $this->leaves_taken_this_year;
        $total = $this->annual_leave_balance;

        return ($used / $total) * 100;
    }

    /**
     * Get leave status color
     */
    public function getLeaveStatusColor()
    {
        $percentage = $this->getLeaveUtilizationPercentage();

        if ($percentage >= 90) {
            return 'danger';
        } elseif ($percentage >= 75) {
            return 'warning';
        } else {
            return 'success';
        }
    }

    /**
     * Get monetary value of remaining leaves
     */
    public function getRemainingLeaveValue()
    {
        if (!$this->leave_amount) {
            return 0;
        }

        return $this->remaining_leaves * $this->leave_amount;
    }

    /**
     * Check if employee can take paid leave
     */
    public function canTakePaidLeave($requestedDays = 1)
    {
        return $this->remaining_leaves >= $requestedDays;
    }

    /**
     * Get days until next leave reset
     */
    public function getDaysUntilReset()
    {
        if (!$this->last_leave_reset) {
            return 0;
        }

        $nextReset = Carbon::parse($this->last_leave_reset)->addYear();
        return Carbon::now()->diffInDays($nextReset, false);
    }

    /**
     * Check if user account is a Developer role/designation
     */
    public function isDeveloper(): bool
    {
        $role = strtolower($this->role ?? '');
        $designation = strtolower($this->designation ?? '');

        return in_array($role, ['developer', 'dev'], true)
            || str_contains($designation, 'developer')
            || str_contains($designation, 'engineer')
            || str_contains($designation, 'devops')
            || str_contains($designation, 'qa');
    }

    /**
     * Check if developer has any assigned tasks in the system
     */
    public function hasAssignedTasks(): bool
    {
        return \Illuminate\Support\Facades\DB::table('tasks')
            ->where('assigned_to', $this->id)
            ->exists();
    }

    /**
     * CRITICAL FIX: Employee can login BASED ON EXIT DATE
     * - Inactive status but exit date in FUTURE = CAN LOGIN
     * - Active/Inactive with exit date passed = CANNOT LOGIN
     * - Developer MUST HAVE assigned tasks to login to Developer Portal
     */
    public function canLogin()
    {
        // First check login_allowed
        if (!$this->login_allowed) {
            return false;
        }

        // Developer login check: Only developers with assigned tasks can log in
        if ($this->isDeveloper() && !$this->hasAssignedTasks()) {
            return false;
        }

        $employeeStatus = $this->employeeDetail ? $this->employeeDetail->status : 'Active';

        // Check if employee has exit date
        if ($this->employeeDetail && $this->employeeDetail->exit_date) {
            $today = Carbon::today();
            $exitDate = Carbon::parse($this->employeeDetail->exit_date);

            // LOGIC: Can login ONLY if today < exit_date
            // (BEFORE exit date, NOT ON or AFTER)
            return $today->lt($exitDate); // $today < $exit_date
        }

        // If no exit date:
        // - Active status = CAN login
        // - Inactive status = CANNOT login
        return $employeeStatus === 'Active';
    }

    /**
     * Get specific error message for login restriction
     */
    public function getLoginErrorMessage()
    {
        $loginAllowed = (bool) $this->login_allowed;
        $employeeStatus = $this->employeeDetail ? $this->employeeDetail->status : 'Active';

        // Check login_allowed first
        if (!$loginAllowed) {
            return 'Your account is active but login is blocked by admin. Please contact administrator.';
        }

        // Developer task assignment check
        if ($this->isDeveloper() && !$this->hasAssignedTasks()) {
            return 'Access Denied: Only developers with assigned tasks can access the Developer Portal. Please contact your manager or admin to assign work.';
        }

        // Check exit date logic
        if ($this->employeeDetail && $this->employeeDetail->exit_date) {
            $today = Carbon::today();
            $exitDate = Carbon::parse($this->employeeDetail->exit_date);

            if ($today->gte($exitDate)) { // $today >= $exitDate
                return 'Your account access has ended as per your exit date (' . $exitDate->format('d/m/Y') . '). Please contact HR.';
            }

            // If today < exit_date but still can't login
            if ($employeeStatus === 'Inactive') {
                return 'Your account is marked as Inactive but you can still login until your exit date (' . $exitDate->format('d/m/Y') . ').';
            }
        }

        // Status based messages
        if ($employeeStatus === 'Inactive') {
            return 'Your account is inactive. Please contact administrator.';
        }

        return 'Your account is not active or login is not allowed.';
    }

    public function getCanLoginAttribute()
    {
        return $this->canLogin();
    }

    public function hasExitDatePassed()
    {
        if (!$this->employeeDetail || !$this->employeeDetail->exit_date) {
            return false;
        }

        $today = Carbon::today();
        $exitDate = Carbon::parse($this->employeeDetail->exit_date);

        return $today->gte($exitDate); // $today >= $exitDate
    }

    /**
     * Get current fiscal year
     */
    public function getCurrentFiscalYear()
    {
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Assuming fiscal year starts in April (04)
        if ($currentMonth >= 4) {
            return $currentYear . '-' . ($currentYear + 1);
        } else {
            return ($currentYear - 1) . '-' . $currentYear;
        }
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'user_id');
    }

    public function employeeDocuments()
    {
        return $this->hasMany(EmployeeDocument::class, 'user_id');
    }

    public function hrDocuments()
    {
        return $this->hasMany(HrDocument::class, 'user_id');
    }

    public function managerDocuments()
    {
        return $this->hasMany(ManagerDocument::class, 'user_id');
    }

    public function adminDocuments()
    {
        return $this->hasMany(AdminDocument::class, 'user_id');
    }
}

<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\GovernmentIdVerification;
use App\Models\ParentDepartment;
use App\Models\User;
use App\Notifications\EmployeeCreatedNotification;
use App\Services\GovernmentIdDobVerifier;
use App\Services\SystemNotificationService;
use App\Support\CountryPhone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait EmployeeOnboarding
{
    private function validateJoiningAge(Request $request): void
    {
        $request->validate([
            'joining_date' => 'required|date',
            'dob' => 'required|date',
        ]);

        app(\App\Services\EmployeeCreationService::class)->validateJoiningAge(
            (string) $request->input('dob'),
            (string) $request->input('joining_date')
        );
    }

    /**
     * Show create employee form.
     */
    public function create()
    {
        $this->ensureAdmin();

        // compute preview id (does NOT reserve it — store() will recompute to avoid races)
        $company = $this->employeeCompany();
        $nextEmployeeId = $this->computeNextEmployeeIdWithLock($company->id);
        $companies = collect([$company]);

        return view('admin.employees.create', [
            'companies'        => $companies,
            'designations'    => Designation::withinLevelLimit()->orderBy('name')->get(),
            'departments'     => Department::with('parent')
                                    ->when(Schema::hasColumn('departments', 'dpt_name'), fn ($q) => $q->orderBy('dpt_name'), fn ($q) => $q->orderBy('id'))
                                    ->get(),
            'prtdepartments'  => ParentDepartment::when(Schema::hasColumn('parent_departments', 'dpt_name'), fn ($q) => $q->orderBy('dpt_name'), fn ($q) => $q->orderBy('id'))
                                    ->get(),
            'users'           => User::whereIn('role', ['admin', 'manager', 'hr', 'employee', 'administrator', 'superadmin'])
                                    ->where('company_id', $company->id)
                                    ->whereNull('archived_at')
                                    ->orderBy('name')
                                    ->get(),
            'countries'       => Country::forForms(),
            'employee'        => null,
            'nextEmployeeId'  => $nextEmployeeId,
        ]);
    }

    /**
     * AJAX endpoint: check mobile number uniqueness
     */
    public function checkMobile(Request $request)
    {
        $this->ensureAdmin();

        $dialCode = $request->mobile_country_code ?: '+91';
        $countryRules = CountryPhone::getDigitRules($dialCode);
        $minDigits = $countryRules['min_digits'] ?? 6;
        $maxDigits = $countryRules['max_digits'] ?? 15;

        $request->validate([
            'mobile' => ['required', 'string', 'regex:/^[1-9]\d*$/', "min:{$minDigits}", "max:{$maxDigits}"],
            'mobile_country_code' => 'nullable|string|regex:/^\+\d{1,4}$/',
            'employee_id' => 'nullable|integer|exists:users,id'
        ]);

        $mobile = $request->mobile;
        $mobileWithCode = $dialCode . $mobile;
        $currentId = $request->employee_id;

        $query = User::where('mobile', $mobileWithCode);

        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'mobile' => $mobile
        ]);
    }

    /**
     * AJAX endpoint: return next employee id (json)
     */
    public function nextId(Request $request)
    {
        $this->ensureAdmin(); // keep same guard as other endpoints
        $companyId = $request->integer('company_id') ?: null;

        return response()->json(['next' => $this->computeNextEmployeeIdWithLock($companyId)]);
    }

    /**
     * Store a new employee.
     */
    public function store(Request $request, GovernmentIdDobVerifier $governmentIdDobVerifier)
    {
        $this->ensureAdmin();
        $this->guardEmployeeRole($request);
        $actor = auth()->user();
        $creationService = app(\App\Services\EmployeeCreationService::class);
        $company = $this->employeeCompany($request->integer('company_id') ?: null);
        $request->merge(['company_id' => $company->id]);

        try {
            $result = $creationService->createFromValidatedRequest(
                $request,
                $actor,
                (int) $company->id,
                $governmentIdDobVerifier,
                ['onboarding_approval' => false]
            );
            $user = $result['user'];
            $plainPassword = $result['plain_password'] ?? '';

            try {
                Notification::sendNow($user, new EmployeeCreatedNotification($user, $plainPassword));
                Log::info('EmployeeCreatedNotification sent immediately', [
                    'email' => $user->email,
                    'user_id' => $user->id,
                ]);
            } catch (\Exception $mailEx) {
                Log::error('Employee creation email failed', [
                    'email' => $user->email,
                    'error' => $mailEx->getMessage(),
                ]);
            }

            try {
                SystemNotificationService::notifyAdmins(
                    'Employee Added',
                    $actor->name . ' added employee ' . $user->name . '.',
                    route('employees.show', $user->id),
                    ['employee_id' => $user->id, 'type' => 'employee_created', 'icon' => 'fa-user-plus']
                );
            } catch (\Throwable $notifEx) {
                Log::warning('Employee creation notification failed', ['error' => $notifEx->getMessage()]);
            }

            if ($request->filled('return_url')) {
                $returnUrl = $request->input('return_url');
                if (Str::startsWith($returnUrl, [url('/'), '/'])) {
                    return redirect($returnUrl)
                        ->with('success', 'Employee added successfully and notified.');
                }
            }

            return redirect()->route('employees.index')
                ->with('success', 'Employee added successfully and notified.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        } catch (\Exception $e) {
            Log::error('Store employee error', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }

    private function computeNextEmployeeIdWithLock(?int $companyId = null): string
    {
        return app(\App\Services\EmployeeCreationService::class)->computeNextEmployeeIdWithLock($companyId, auth()->user());
    }
}

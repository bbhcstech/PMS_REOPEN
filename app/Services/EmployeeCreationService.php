<?php

namespace App\Services;

use App\Models\Central\Company as CentralCompany;
use App\Models\Company;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\GovernmentIdVerification;
use App\Models\ParentDepartment;
use App\Models\User;
use App\Support\CountryPhone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeCreationService
{
    public function resolveCompany(?int $requestedId = null, ?User $actor = null): Company
    {
        $actor ??= auth()->user();
        $isSuperAdmin = strtolower((string) ($actor?->role ?? '')) === 'superadmin';
        $companyId = $isSuperAdmin
            ? ($requestedId ?: app(CompanyContext::class)->id())
            : (int) $actor?->company_id;

        abort_unless($companyId, 403, 'No company is assigned to this account.');
        abort_if(! $isSuperAdmin && $requestedId && $requestedId !== $companyId, 403, 'You can only manage employees for your company.');

        $company = Company::find($companyId);
        if ($company) {
            return $company;
        }

        $central = CentralCompany::on('central')->findOrFail($companyId);
        $fallback = new Company;
        $fallback->forceFill($central->only([
            'id', 'name', 'short_name', 'email', 'phone', 'website', 'logo', 'address', 'db_name', 'employee_id_prefix', 'status',
        ]));
        $fallback->exists = true;

        return $fallback;
    }

    public function validateJoiningAge(string $dob, string $joiningDate): void
    {
        $birthDate = Carbon::parse($dob)->startOfDay();
        $joining = Carbon::parse($joiningDate)->startOfDay();

        if ($joining->lt($birthDate->copy()->addYearsNoOverflow(18))) {
            throw ValidationException::withMessages([
                'joining_date' => 'The employee must be at least 18 years old on the joining date. The joining date cannot be before the date of birth.',
            ]);
        }
    }

    public function sanitizeSkills(?string $skills): ?string
    {
        if ($skills === null) {
            return null;
        }

        $rawSkills = array_filter(array_map('trim', explode(',', str_replace(['·', '|'], ',', $skills))));
        $safeSkills = array_filter(
            $rawSkills,
            fn ($s) => strlen($s) > 0 && strlen($s) <= 60 && ! preg_match('/(SQLSTATE|Column not found|INSERT INTO|SELECT |`)/i', $s)
        );

        return ! empty($safeSkills) ? implode(', ', $safeSkills) : null;
    }

    public function computeNextEmployeeIdWithLock(?int $companyId = null, ?User $actor = null): string
    {
        $company = $this->resolveCompany($companyId, $actor);
        $companyId = (int) $company->id;
        $prefix = trim((string) $company->employee_id_prefix);
        if ($prefix === '') {
            $prefix = strtoupper(Str::slug($company->short_name ?: $company->name)) . '-EMP';
        }
        $prefix = rtrim($prefix, '-');
        $digits = 4;
        $like = $prefix . '-%';
        $conn = (new EmployeeDetail)->getConnectionName() ?: config('database.default', 'mysql');

        $last = EmployeeDetail::where('employee_id', 'LIKE', $like)
            ->when($companyId && Schema::connection($conn)->hasColumn('employee_details', 'company_id'), fn ($query) => $query->where('company_id', $companyId))
            ->whereHas('user', fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        if ($last && preg_match('/(\d+)$/', $last->employee_id, $m)) {
            $nextNumber = intval($m[1]) + 1;
        } else {
            $nextNumber = 1;
        }

        $candidate = $prefix . '-' . str_pad((string) $nextNumber, $digits, '0', STR_PAD_LEFT);
        while (EmployeeDetail::where('employee_id', $candidate)->exists()) {
            $nextNumber++;
            $candidate = $prefix . '-' . str_pad((string) $nextNumber, $digits, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    public function generateNextParentDepartmentCode(): string
    {
        $prefix = 'DEP-';

        return DB::transaction(function () use ($prefix) {
            $last = ParentDepartment::where('dpt_code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) ? ((int) $m[1] + 1) : 1;
            $code = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            while (ParentDepartment::where('dpt_code', $code)->exists()) {
                $nextNumber++;
                $code = $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            }

            return $code;
        }, 5);
    }

    public function generateNextSubDepartmentCode(): string
    {
        $prefix = 'SUB-';

        return DB::transaction(function () use ($prefix) {
            $last = Department::where('dpt_code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            $nextNumber = ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) ? ((int) $m[1] + 1) : 1;
            $pad = $nextNumber > 99 ? 3 : 2;
            $code = $prefix . str_pad((string) $nextNumber, $pad, '0', STR_PAD_LEFT);
            while (Department::where('dpt_code', $code)->exists()) {
                $nextNumber++;
                $pad = $nextNumber > 99 ? 3 : $pad;
                $code = $prefix . str_pad((string) $nextNumber, $pad, '0', STR_PAD_LEFT);
            }

            return $code;
        }, 5);
    }

    /**
     * Validate and create an official User + EmployeeDetail record.
     * Shared by direct manual creation and HR onboarding approval.
     *
     * @param  array<string, mixed>  $options
     * @return array{user: User, detail: EmployeeDetail, plain_password: ?string}
     */
    public function createFromValidatedRequest(
        Request $request,
        User $actor,
        int $companyId,
        GovernmentIdDobVerifier $governmentIdDobVerifier,
        array $options = []
    ): array {
        $isOnboardingApproval = (bool) ($options['onboarding_approval'] ?? false);
        $privateFiles = $options['private_files'] ?? [];
        $privateDisk = $options['private_disk'] ?? config('employee_onboarding.private_disk', 'local');

        $request->merge(['company_id' => $companyId]);

        $this->validateJoiningAge(
            (string) $request->input('dob'),
            (string) $request->input('joining_date')
        );

        if ($request->status === 'Inactive') {
            if (empty($request->exit_date)) {
                throw ValidationException::withMessages(['exit_date' => 'Exit date is required when status is Inactive.']);
            }
            $exitDate = Carbon::parse($request->exit_date)->startOfDay();
            $joiningDate = Carbon::parse($request->joining_date)->startOfDay();
            if ($exitDate->lte($joiningDate)) {
                throw ValidationException::withMessages(['exit_date' => 'Exit date must be after joining date.']);
            }
            $request->merge(['login_allowed' => Carbon::today()->gte($exitDate) ? 0 : 1]);
        } else {
            $request->merge(['exit_date' => null]);
        }

        if (! $request->filled('mobile_country_code')) {
            $request->merge(['mobile_country_code' => '+91']);
        }
        if (! $request->filled('reporting_to')) {
            $defaultReporting = $actor->id ?: User::value('id');
            if ($defaultReporting) {
                $request->merge(['reporting_to' => $defaultReporting]);
            }
        }
        if (! $request->filled('status')) {
            $request->merge(['status' => 'Active']);
        }
        if (! $request->has('login_allowed')) {
            $request->merge(['login_allowed' => 1]);
        }
        if (! $request->filled('employment_type')) {
            $request->merge(['employment_type' => 'full_time']);
        }
        if (! $request->filled('business_address')) {
            $request->merge(['business_address' => $request->address ?: 'Head Office']);
        }

        $phoneRules = CountryPhone::getDigitRules($request->mobile_country_code ?? '+91');
        $minDigits = $phoneRules['min_digits'] ?? 6;
        $maxDigits = $phoneRules['max_digits'] ?? 15;
        $phoneCountryName = $phoneRules['name'] ?? 'selected country';

        $rules = [
            'name' => 'required|string|max:191',
            'company_id' => 'required|exists:central.companies,id',
            'email' => 'required|email|unique:users,email',
            'mobile_country_code' => 'required|string|regex:/^\+\d{1,4}$/',
            'mobile' => ['required', 'string', 'regex:/^[1-9]\d*$/', "min:{$minDigits}", "max:{$maxDigits}"],
            'joining_date' => 'required|date',
            'reporting_to' => 'nullable|integer|exists:users,id',
            'business_address' => 'nullable|string',
            'status' => 'required|in:Active,Inactive',
            'exit_date' => 'nullable|date',
            'user_role' => 'nullable|string|in:employee,admin,manager,hr',
            'login_allowed' => 'required|in:0,1',
            'email_notifications' => 'nullable|in:0,1',
            'employment_type' => 'required|in:full_time,part_time,on_contract,internship,trainee',
            'designation_id' => 'required',
            'parent_dpt_id' => 'required',
            'department_id' => 'nullable',
            'new_designation' => 'required_if:designation_id,new|nullable|string|max:191',
            'new_designation_level' => 'required_if:designation_id,new|nullable|integer|min:0|max:' . DesignationLevels::maximum(),
            'new_department' => 'required_if:parent_dpt_id,new|nullable|string|max:191',
            'new_sub_department' => 'required_if:department_id,new|nullable|string|max:191',
            'dob' => 'required|date',
            'probation_end_date' => 'nullable|date',
            'notice_start_date' => 'nullable|date',
            'notice_end_date' => 'nullable|date',
            'directory_about' => 'nullable|string|max:3000',
            'linkedin_url' => 'nullable|url|max:255',
            'portfolio_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'x_url' => 'nullable|url|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'government_id_card' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:4096',
        ];

        if (! $isOnboardingApproval) {
            $rules['password'] = [
                'nullable',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (! $value) {
                        return;
                    }
                    if (! preg_match('/[A-Z]/', $value)) {
                        $fail('The password must contain at least 1 uppercase letter.');
                    }
                    if (! preg_match('/[a-z]/', $value)) {
                        $fail('The password must contain at least 1 lowercase letter.');
                    }
                    if (! preg_match('/[0-9]/', $value)) {
                        $fail('The password must contain at least 1 number.');
                    }
                    if (! preg_match('/[^A-Za-z0-9]/', $value)) {
                        $fail('The password must contain at least 1 special character.');
                    }
                },
            ];
        }

        $messages = [
            'mobile.regex' => 'The mobile number must contain digits only and cannot start with 0.',
            'mobile.min' => $minDigits === $maxDigits
                ? "The mobile number for {$phoneCountryName} must be exactly {$minDigits} digits."
                : "The mobile number for {$phoneCountryName} must be between {$minDigits} and {$maxDigits} digits.",
            'mobile.max' => $minDigits === $maxDigits
                ? "The mobile number for {$phoneCountryName} must be exactly {$minDigits} digits."
                : "The mobile number for {$phoneCountryName} must be between {$minDigits} and {$maxDigits} digits.",
        ];

        $request->validate($rules, $messages);

        $mobileWithCode = $request->mobile_country_code . $request->mobile;
        if (User::where('mobile', $mobileWithCode)->exists()) {
            throw ValidationException::withMessages(['mobile' => 'This mobile number is already registered.']);
        }

        if ($request->designation_id !== 'new' && ! Designation::withinLevelLimit()->whereKey($request->designation_id)->exists()) {
            throw ValidationException::withMessages(['designation_id' => 'Please select a designation within your company level limit.']);
        }
        if ($request->parent_dpt_id !== 'new' && ! ParentDepartment::whereKey($request->parent_dpt_id)->exists()) {
            throw ValidationException::withMessages(['parent_dpt_id' => 'Please select a valid department.']);
        }
        if ($request->filled('department_id') && $request->department_id !== 'new' && ! Department::whereKey($request->department_id)->exists()) {
            throw ValidationException::withMessages(['department_id' => 'Please select a valid sub department.']);
        }

        $profileImagePath = null;
        if ($request->hasFile('profile_picture')) {
            $profileImagePath = $this->movePublicUpload($request->file('profile_picture'), 'admin/uploads/profile-images', 'emp_');
        } elseif (! empty($privateFiles['profile_picture_path'])) {
            $profileImagePath = $this->promotePrivateFile(
                $privateDisk,
                $privateFiles['profile_picture_path'],
                $privateFiles['profile_picture_name'] ?? null,
                'admin/uploads/profile-images',
                'emp_'
            );
        }

        $governmentIdCardPath = null;
        $governmentIdVerification = null;
        if ($request->hasFile('government_id_card')) {
            $governmentIdCardPath = $this->movePublicUpload($request->file('government_id_card'), 'admin/uploads/government-id-cards', 'government-id-');
        } elseif (! empty($privateFiles['government_id_card_path'])) {
            $governmentIdCardPath = $this->promotePrivateFile(
                $privateDisk,
                $privateFiles['government_id_card_path'],
                $privateFiles['government_id_card_name'] ?? null,
                'admin/uploads/government-id-cards',
                'government-id-'
            );
        }
        if ($governmentIdCardPath && file_exists(public_path($governmentIdCardPath))) {
            $governmentIdVerification = $governmentIdDobVerifier->verify(
                public_path($governmentIdCardPath),
                (string) $request->dob
            );
        }

        $cvPath = null;
        if ($request->hasFile('cv_file')) {
            $cvPath = $this->movePublicUpload($request->file('cv_file'), 'admin/uploads/employee-cvs', 'cv_');
        } elseif (! empty($privateFiles['cv_file_path'])) {
            $cvPath = $this->promotePrivateFile(
                $privateDisk,
                $privateFiles['cv_file_path'],
                $privateFiles['cv_file_name'] ?? null,
                'admin/uploads/employee-cvs',
                'cv_'
            );
        }

        // Direct creation uses entered/temporary password for legacy EmployeeCreatedNotification;
        // self-onboarding approval uses a random CSPRNG hash without storing plaintext password.
        $plainPassword = $isOnboardingApproval ? null : ($request->filled('password') ? (string) $request->password : Str::random(12));
        $passwordHash = Hash::make($plainPassword ?? Str::random(40));

        $conn = (new EmployeeDetail)->getConnectionName() ?: config('database.default', 'mysql');
        $createdOutside = DB::connection($conn)->transactionLevel() > 0;
        if (! $createdOutside) {
            DB::connection($conn)->beginTransaction();
        }

        try {
            $userData = [
                'name' => trim((string) $request->name),
                'company_id' => $companyId,
                'email' => strtolower(trim((string) $request->email)),
                'mobile' => $mobileWithCode,
                'password' => $passwordHash,
                'role' => $request->input('user_role', $request->input('role', 'employee')),
                'login_allowed' => (int) ($request->login_allowed ?? 1),
            ];

            if (Schema::connection($conn)->hasColumn('users', 'raw_password')) {
                $userData['raw_password'] = $plainPassword;
            }
            if (Schema::connection($conn)->hasColumn('users', 'profile_image')) {
                $userData['profile_image'] = $profileImagePath;
            }
            if (Schema::connection($conn)->hasColumn('users', 'email_notifications')) {
                $userData['email_notifications'] = (int) ($request->email_notifications ?? 1);
            }
            if ($request->filled('reporting_to') && Schema::connection($conn)->hasColumn('users', 'reports_to_id')) {
                $userData['reports_to_id'] = (int) $request->reporting_to;
            }
            $viewerRole = $actor->normalizedRole();
            if ($viewerRole === 'hr' && Schema::connection($conn)->hasColumn('users', 'hr_id')) {
                $userData['hr_id'] = $actor->id;
            } elseif ($viewerRole === 'manager' && Schema::connection($conn)->hasColumn('users', 'manager_id')) {
                $userData['manager_id'] = $actor->id;
            }
            if ($isOnboardingApproval && Schema::connection($conn)->hasColumn('users', 'email_verified_at')) {
                $userData['email_verified_at'] = now();
            }

            $user = User::create($userData);

            $employeeData = $request->only([
                'designation_id', 'parent_dpt_id', 'department_id', 'employee_id',
                'salutation', 'country', 'gender', 'joining_date', 'dob', 'reporting_to',
                'language', 'address', 'about', 'directory_about',
                'hourly_rate', 'slack_member_id', 'skills',
                'linkedin_url', 'portfolio_url', 'facebook_url', 'instagram_url', 'x_url',
                'probation_end_date', 'notice_start_date', 'notice_end_date',
                'employment_type', 'marital_status', 'business_address', 'status',
            ]);
            unset($employeeData['user_role']);

            $employeeData['exit_date'] = ($request->status !== 'Active' && $request->filled('exit_date'))
                ? Carbon::parse($request->exit_date)->format('Y-m-d')
                : null;
            $employeeData['login_allowed'] = (bool) ($request->login_allowed ?? 1);
            $employeeData['email_notifications'] = (bool) ($request->email_notifications ?? 1);
            if (isset($employeeData['skills'])) {
                $employeeData['skills'] = $this->sanitizeSkills((string) $employeeData['skills']);
            }
            $employeeData['mobile'] = $request->mobile;
            $employeeData['user_id'] = $user->id;
            if (Schema::connection($conn)->hasColumn('employee_details', 'company_id')) {
                $employeeData['company_id'] = $companyId;
            }
            $employeeData['government_id_card'] = $governmentIdCardPath;
            $employeeData['government_id_verification_status'] = $governmentIdVerification['status'] ?? null;
            $employeeData['cv_path'] = $cvPath;
            $employeeData['reporting_to'] = $request->filled('reporting_to') ? (int) $request->reporting_to : null;
            $employeeData['hourly_rate'] = $request->filled('hourly_rate') ? $request->hourly_rate : null;

            if ($request->designation_id === 'new' && $request->filled('new_designation')) {
                $designation = Designation::firstOrCreate(
                    array_filter([
                        'name' => trim((string) $request->new_designation),
                        'company_id' => Schema::connection($conn)->hasColumn('designations', 'company_id') ? $companyId : null,
                    ], fn ($value) => ! is_null($value)),
                    [
                        'company_id' => Schema::connection($conn)->hasColumn('designations', 'company_id') ? $companyId : null,
                        'level' => $request->new_designation_level ?? 0,
                        'status' => 'Active',
                        'added_by' => $actor->id,
                        'last_updated_by' => $actor->id,
                    ]
                );
                if (! Designation::withinLevelLimit()->whereKey($designation->id)->exists()) {
                    throw ValidationException::withMessages([
                        'designation_id' => 'This designation exceeds your company level limit.',
                    ]);
                }
                $employeeData['designation_id'] = $designation->id;
            } else {
                $employeeData['designation_id'] = $request->filled('designation_id') ? $request->designation_id : null;
            }

            if ($request->parent_dpt_id === 'new' && $request->filled('new_department')) {
                $department = ParentDepartment::firstOrCreate(
                    array_filter([
                        'dpt_name' => trim((string) $request->new_department),
                        'company_id' => Schema::connection($conn)->hasColumn('parent_departments', 'company_id') ? $companyId : null,
                    ], fn ($value) => ! is_null($value)),
                    [
                        'company_id' => Schema::connection($conn)->hasColumn('parent_departments', 'company_id') ? $companyId : null,
                        'dpt_code' => $this->generateNextParentDepartmentCode(),
                        'status' => 'Active',
                    ]
                );
                $employeeData['parent_dpt_id'] = $department->id;
            } else {
                $employeeData['parent_dpt_id'] = $request->filled('parent_dpt_id') ? $request->parent_dpt_id : null;
            }

            if ($request->department_id === 'new' && $request->filled('new_sub_department')) {
                $subDepartment = Department::firstOrCreate(
                    [
                        'dpt_name' => trim((string) $request->new_sub_department),
                        'parent_dpt_id' => $employeeData['parent_dpt_id'],
                        'company_id' => Schema::connection($conn)->hasColumn('departments', 'company_id') ? $companyId : null,
                    ],
                    [
                        'company_id' => Schema::connection($conn)->hasColumn('departments', 'company_id') ? $companyId : null,
                        'dpt_code' => $this->generateNextSubDepartmentCode(),
                        'status' => 'Active',
                    ]
                );
                $employeeData['department_id'] = $subDepartment->id;
            } else {
                $employeeData['department_id'] = ($request->filled('department_id') && $request->department_id !== 'new') ? $request->department_id : null;
            }

            if ($request->country === 'new' && $request->filled('new_country')) {
                $country = Country::firstOrCreate(
                    ['name' => trim((string) $request->new_country)],
                    ['status' => 'Active', 'added_by' => $actor->id]
                );
                $employeeData['country'] = $country->name;
            } else {
                $employeeData['country'] = $request->country;
            }

            if (! empty($employeeData['probation_end_date'])) {
                $employeeData['notice_start_date'] = null;
                $employeeData['notice_end_date'] = null;
            } elseif (! empty($employeeData['notice_start_date']) || ! empty($employeeData['notice_end_date'])) {
                $employeeData['probation_end_date'] = null;
            }

            if ($request->input('employee_id_option') === 'custom' && $request->filled('employee_id')) {
                $customId = trim((string) $request->input('employee_id'));
                if (EmployeeDetail::where('employee_id', $customId)->exists()) {
                    throw ValidationException::withMessages([
                        'employee_id' => 'The Employee ID "' . $customId . '" is already in use. Please provide a unique ID.',
                    ]);
                }
                $employeeData['employee_id'] = $customId;
            } else {
                $employeeData['employee_id'] = $this->computeNextEmployeeIdWithLock($companyId, $actor);
            }

            $detailCols = Schema::connection($conn)->getColumnListing('employee_details');
            $employeeData = array_intersect_key($employeeData, array_flip($detailCols));

            $tries = 0;
            $detail = null;
            do {
                try {
                    $detail = EmployeeDetail::create($employeeData);
                } catch (\Illuminate\Database\QueryException $qe) {
                    $tries++;
                    $duplicateId = in_array((int) ($qe->errorInfo[1] ?? 0), [1062, 19], true)
                        && str_contains(strtolower($qe->getMessage()), 'employee_id');
                    if (! $duplicateId || $request->input('employee_id_option') === 'custom' || $tries > 5) {
                        throw $qe;
                    }
                    Log::warning('employee_id collision on create, retrying', [
                        'employee_id' => $employeeData['employee_id'],
                        'tries' => $tries,
                    ]);
                    $employeeData['employee_id'] = $this->computeNextEmployeeIdWithLock($companyId, $actor);
                    usleep(100000);
                }
            } while (! $detail);

            if ($governmentIdVerification && $governmentIdCardPath && Schema::connection($conn)->hasTable('government_id_verifications')) {
                GovernmentIdVerification::create([
                    'user_id' => $user->id,
                    'employee_detail_id' => $detail->id,
                    'submitted_dob' => $request->dob,
                    'image_path' => $governmentIdCardPath,
                    'ocr_text' => $governmentIdVerification['ocr_text'],
                    'ocr_detected_dob' => $governmentIdVerification['detected_dob'],
                    'verification_status' => $governmentIdVerification['status'],
                    'ocr_message' => $governmentIdVerification['message'],
                    'ocr_errors' => $governmentIdVerification['errors'],
                    'reviewed_at' => $governmentIdVerification['approved'] ? now() : null,
                ]);
            }

            if (! $createdOutside) {
                DB::connection($conn)->commit();
            }

            return [
                'user' => $user,
                'detail' => $detail,
                'plain_password' => $plainPassword,
            ];
        } catch (\Throwable $e) {
            if (! $createdOutside && DB::connection($conn)->transactionLevel() > 0) {
                DB::connection($conn)->rollBack();
            }
            throw $e;
        }
    }

    private function movePublicUpload(UploadedFile $file, string $relativeDir, string $prefix = ''): string
    {
        $directory = public_path($relativeDir);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $ext = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $fileName = time() . '-' . $prefix . Str::random(8) . '.' . $ext;
        $file->move($directory, $fileName);

        return $relativeDir . '/' . $fileName;
    }

    private function promotePrivateFile(string $disk, string $privatePath, ?string $originalName, string $relativeDir, string $prefix = ''): ?string
    {
        if (! Storage::disk($disk)->exists($privatePath)) {
            return null;
        }
        $directory = public_path($relativeDir);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $ext = pathinfo((string) ($originalName ?: $privatePath), PATHINFO_EXTENSION) ?: 'bin';
        $fileName = time() . '-' . $prefix . Str::random(8) . '.' . strtolower($ext);
        $contents = Storage::disk($disk)->get($privatePath);
        file_put_contents($directory . DIRECTORY_SEPARATOR . $fileName, $contents);

        return $relativeDir . '/' . $fileName;
    }
}

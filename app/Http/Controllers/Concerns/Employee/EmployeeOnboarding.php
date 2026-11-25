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

        $birthDate = Carbon::parse($request->input('dob'))->startOfDay();
        $joiningDate = Carbon::parse($request->input('joining_date'))->startOfDay();

        if ($joiningDate->lt($birthDate->copy()->addYearsNoOverflow(18))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'joining_date' => 'The employee must be at least 18 years old on the joining date. The joining date cannot be before the date of birth.',
            ]);
        }
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
        $company = $this->employeeCompany($request->integer('company_id') ?: null);
        $request->merge(['company_id' => $company->id]);
        $this->validateJoiningAge($request);

        // ==============================================
        // CRITICAL FIX: EXIT DATE VALIDATION LOGIC
        // ==============================================
        if ($request->status === 'Inactive') {
            if (empty($request->exit_date)) {
                return back()->withErrors(['exit_date' => 'Exit date is required when status is Inactive.'])->withInput();
            }

            $exitDate = Carbon::parse($request->exit_date)->startOfDay();
            $joiningDate = Carbon::parse($request->joining_date)->startOfDay();

            // Exit date must be after joining date
            if ($exitDate->lte($joiningDate)) {
                return back()->withErrors(['exit_date' => 'Exit date must be after joining date.'])->withInput();
            }

            // Auto-set login_allowed based on exit date
            $today = Carbon::today();
            if ($today->gte($exitDate)) {
                // Exit date passed - block login
                $request->merge(['login_allowed' => 0]);
            } else {
                // Exit date future - allow login
                $request->merge(['login_allowed' => 1]);
            }
        } else {
            // When status is Active, clear exit_date
            $request->merge(['exit_date' => null]);
        }

        // Auto-fill fallback values for fields if omitted
        if (! $request->filled('mobile_country_code')) {
            $request->merge(['mobile_country_code' => '+91']);
        }
        if (! $request->filled('reporting_to')) {
            $defaultReporting = auth()->id() ?: User::value('id');
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

        // Prepare validation rules
        $validationRules = [
            'name'              => 'required|string',
            'company_id'        => 'required|exists:central.companies,id',
            'email'             => 'required|email|unique:users,email',
            'mobile_country_code' => 'required|string|regex:/^\+\d{1,4}$/',
            'mobile'            => ['required', 'string', 'regex:/^[1-9]\d*$/', "min:{$minDigits}", "max:{$maxDigits}"],
            'joining_date'      => 'required|date',
            'reporting_to'      => 'nullable|integer|exists:users,id',
            'business_address'  => 'nullable|string',
            'status'            => 'required|in:Active,Inactive',
            'exit_date'         => 'nullable|date',
            'user_role'         => 'nullable|string|in:employee,admin,manager,hr',
            'login_allowed'     => 'required|in:0,1',
            'employment_type'   => 'required|in:full_time,part_time,on_contract,internship,trainee',
            'password'          => [
                'nullable',
                'string',
                'min:8',
                function ($attribute, $value, $fail) {
                    if (!$value) return;
                    if (!preg_match('/[A-Z]/', $value)) {
                        $fail('The password must contain at least 1 uppercase letter.');
                    }
                    if (!preg_match('/[a-z]/', $value)) {
                        $fail('The password must contain at least 1 lowercase letter.');
                    }
                    if (!preg_match('/[0-9]/', $value)) {
                        $fail('The password must contain at least 1 number.');
                    }
                    if (!preg_match('/[^A-Za-z0-9]/', $value)) {
                        $fail('The password must contain at least 1 special character.');
                    }
                },
            ],
            'profile_picture'   => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'government_id_card' => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
            'designation_id'    => 'required',
            'parent_dpt_id'     => 'required',
            'department_id'     => 'nullable',
            'new_designation'   => 'required_if:designation_id,new|nullable|string|max:191',
            'new_designation_level' => 'required_if:designation_id,new|nullable|integer|min:0|max:' . \App\Services\DesignationLevels::maximum(),
            'new_department'    => 'required_if:parent_dpt_id,new|nullable|string|max:191',
            'new_sub_department' => 'required_if:department_id,new|nullable|string|max:191',

            'dob'                => 'required|date',
            'probation_end_date' => 'nullable|date',
            'notice_start_date'  => 'nullable|date',
            'notice_end_date'    => 'nullable|date',
            'directory_about'    => 'nullable|string|max:3000',
            'linkedin_url'       => 'nullable|url|max:255',
            'portfolio_url'      => 'nullable|url|max:255',
            'facebook_url'       => 'nullable|url|max:255',
            'instagram_url'      => 'nullable|url|max:255',
            'x_url'              => 'nullable|url|max:255',
            'cv_file'            => 'nullable|file|mimes:pdf,doc,docx|max:4096',
        ];

        $customMessages = [
            'mobile.regex' => 'The mobile number must contain digits only and cannot start with 0.',
            'mobile.min'   => $minDigits === $maxDigits
                                ? "The mobile number for {$phoneCountryName} must be exactly {$minDigits} digits."
                                : "The mobile number for {$phoneCountryName} must be between {$minDigits} and {$maxDigits} digits.",
            'mobile.max'   => $minDigits === $maxDigits
                                ? "The mobile number for {$phoneCountryName} must be exactly {$minDigits} digits."
                                : "The mobile number for {$phoneCountryName} must be between {$minDigits} and {$maxDigits} digits.",
        ];

        // If editing, adjust unique rules
        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $userId = $request->route('employee');
            $validationRules['email'] = 'required|email|unique:users,email,' . $userId;
            $validationRules['mobile'] = ['required', 'string', 'regex:/^[1-9]\d*$/', "min:{$minDigits}", "max:{$maxDigits}"];
        }

        $request->validate($validationRules, $customMessages);

        $mobileWithCodeForValidation = $request->mobile_country_code . $request->mobile;
        $mobileQuery = User::where('mobile', $mobileWithCodeForValidation);
        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $mobileQuery->where('id', '!=', $request->route('employee'));
        }
        if ($mobileQuery->exists()) {
            return back()->withErrors(['mobile' => 'This mobile number is already registered.'])->withInput();
        }

        if ($request->designation_id !== 'new' && ! Designation::withinLevelLimit()->whereKey($request->designation_id)->exists()) {
            return back()->withErrors(['designation_id' => 'Please select a designation within your company level limit.'])->withInput();
        }

        if ($request->parent_dpt_id !== 'new' && ! ParentDepartment::whereKey($request->parent_dpt_id)->exists()) {
            return back()->withErrors(['parent_dpt_id' => 'Please select a valid department.'])->withInput();
        }

        if ($request->filled('department_id') && $request->department_id !== 'new' && ! Department::whereKey($request->department_id)->exists()) {
            return back()->withErrors(['department_id' => 'Please select a valid sub department.'])->withInput();
        }

        $profileImagePath = null;
        $governmentIdCardPath = null;
        $cvPath = null;
        $governmentIdVerification = null;

        if ($request->hasFile('profile_picture')) {
            $image = $request->file('profile_picture');
            $fileName = time() . '-' . $image->getClientOriginalName();
            $profileImageDirectory = public_path('admin/uploads/profile-images');
            if (! is_dir($profileImageDirectory)) {
                mkdir($profileImageDirectory, 0755, true);
            }
            $image->move($profileImageDirectory, $fileName);
            $profileImagePath = 'admin/uploads/profile-images/' . $fileName;
        }

        if ($request->hasFile('government_id_card')) {
            $image = $request->file('government_id_card');
            $fileName = time() . '-government-id-' . $image->getClientOriginalName();
            $governmentIdDirectory = public_path('admin/uploads/government-id-cards');
            if (! is_dir($governmentIdDirectory)) {
                mkdir($governmentIdDirectory, 0755, true);
            }
            $image->move($governmentIdDirectory, $fileName);
            $governmentIdCardPath = 'admin/uploads/government-id-cards/' . $fileName;

            $governmentIdVerification = $governmentIdDobVerifier->verify(
                public_path($governmentIdCardPath),
                $request->dob
            );
        }

        $cvPath = $this->storeEmployeeCv($request);

        // generate a plain temporary password (if request provided use it, else random)
        $plainPassword = $request->filled('password') ? $request->password : Str::random(12);
        $passwordHash = Hash::make($plainPassword);

        $conn = (new EmployeeDetail)->getConnectionName() ?: config('database.default', 'mysql');
        DB::connection($conn)->beginTransaction();
        try {
            $mobileWithCode = $request->mobile_country_code . $request->mobile;

            // Create user
            $userData = [
                'name'          => $request->name,
                'company_id'    => $request->company_id,
                'email'         => $request->email,
                'mobile'        => $mobileWithCode,
                'password'      => $passwordHash,
                'raw_password'  => $plainPassword,
                'role'          => $request->input('user_role', $request->input('role', 'employee')),
                'profile_image' => $profileImagePath,
                'login_allowed' => $request->login_allowed ?? 1,
                'email_notifications' => $request->email_notifications ?? 1,
            ];

            if ($request->filled('reporting_to')) {
                $userData['reports_to_id'] = (int) $request->reporting_to;
            }
            $viewer = auth()->user();
            if ($viewer && $viewer->normalizedRole() === 'hr') {
                $userData['hr_id'] = $viewer->id;
            } elseif ($viewer && $viewer->normalizedRole() === 'manager') {
                $userData['manager_id'] = $viewer->id;
            }

            $user = User::create($userData);

            // Prepare employee detail payload
            $employeeData = $request->only([
                'designation_id', 'parent_dpt_id', 'department_id', 'employee_id',
                'salutation', 'country', 'gender', 'joining_date', 'dob', 'reporting_to',
                'language', 'address', 'about', 'directory_about',
                'hourly_rate', 'slack_member_id', 'skills',
                'linkedin_url', 'portfolio_url', 'facebook_url', 'instagram_url', 'x_url',
                'probation_end_date', 'notice_start_date', 'notice_end_date',
                'employment_type', 'marital_status', 'business_address', 'status'
            ]);
            unset($employeeData['user_role']);

            if ($request->status === 'Active') {
                $employeeData['exit_date'] = null;
            } elseif ($request->filled('exit_date')) {
                $employeeData['exit_date'] = Carbon::parse($request->exit_date)->format('Y-m-d');
            } else {
                $employeeData['exit_date'] = null;
            }
            $employeeData['login_allowed'] = (bool) ($request->login_allowed ?? 1);

            if (isset($employeeData['skills'])) {
                $rawSkills = array_filter(array_map('trim', explode(',', str_replace(['·', '|'], ',', (string) $employeeData['skills']))));
                $safeSkills = array_filter($rawSkills, fn($s) => strlen($s) > 0 && strlen($s) <= 60 && !preg_match('/(SQLSTATE|Column not found|INSERT INTO|SELECT |`)/i', $s));
                $employeeData['skills'] = !empty($safeSkills) ? implode(', ', $safeSkills) : null;
            }

            // Add mobile without prefix for employee detail
            $employeeData['mobile'] = $request->mobile;
            $employeeData['user_id'] = $user->id;
            if (Schema::connection($conn)->hasColumn('employee_details', 'company_id')) {
                $employeeData['company_id'] = $request->company_id;
            }
            $employeeData['government_id_card'] = $governmentIdCardPath;
            $employeeData['government_id_verification_status'] = $governmentIdVerification['status'] ?? null;
            $employeeData['cv_path'] = $cvPath;
            $employeeData['reporting_to'] = $request->filled('reporting_to') ? (int) $request->reporting_to : null;
            $employeeData['hourly_rate'] = $request->filled('hourly_rate') ? $request->hourly_rate : null;

            // ================================================
            // FIXED: Handle new designation with firstOrCreate - WITH LEVEL
            // ================================================
            if ($request->designation_id === 'new' && $request->filled('new_designation')) {
                $designation = Designation::firstOrCreate(
                    array_filter([
                        'name' => trim($request->new_designation),
                        'company_id' => Schema::connection($conn)->hasColumn('designations', 'company_id') ? $request->company_id : null,
                    ], fn ($value) => ! is_null($value)),
                    [
                        'company_id' => Schema::connection($conn)->hasColumn('designations', 'company_id') ? $request->company_id : null,
                        'level' => $request->new_designation_level ?? 0, // ADDED LEVEL FIELD
                        'status' => 'Active',
                        'added_by' => auth()->id(),
                        'last_updated_by' => auth()->id(),
                    ]
                );
                if (! Designation::withinLevelLimit()->whereKey($designation->id)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'designation_id' => 'This designation exceeds your company level limit.',
                    ]);
                }
                $employeeData['designation_id'] = $designation->id;
            } else {
                $employeeData['designation_id'] = $request->filled('designation_id') ? $request->designation_id : null;
            }

            // ================================================
            // FIXED: Handle new department with firstOrCreate
            // ================================================
            if ($request->parent_dpt_id === 'new' && $request->filled('new_department')) {
                $department = ParentDepartment::firstOrCreate(
                    array_filter([
                        'dpt_name' => trim($request->new_department),
                        'company_id' => Schema::connection($conn)->hasColumn('parent_departments', 'company_id') ? $request->company_id : null,
                    ], fn ($value) => ! is_null($value)),
                    [
                        'company_id' => Schema::connection($conn)->hasColumn('parent_departments', 'company_id') ? $request->company_id : null,
                        'dpt_code' => $this->generateNextParentDepartmentCode(),
                        'status' => 'Active'
                    ]
                );
                $employeeData['parent_dpt_id'] = $department->id;
            } else {
                $employeeData['parent_dpt_id'] = $request->filled('parent_dpt_id') ? $request->parent_dpt_id : null;
            }

            // ================================================
            // FIXED: Handle new sub-department with firstOrCreate
            // ================================================
            if ($request->department_id === 'new' && $request->filled('new_sub_department')) {
                $subDepartment = Department::firstOrCreate(
                    [
                        'dpt_name' => trim($request->new_sub_department),
                        'parent_dpt_id' => $employeeData['parent_dpt_id'],
                        'company_id' => Schema::connection($conn)->hasColumn('departments', 'company_id') ? $request->company_id : null,
                    ],
                    [
                        'company_id' => Schema::connection($conn)->hasColumn('departments', 'company_id') ? $request->company_id : null,
                        'dpt_code' => $this->generateNextSubDepartmentCode(),
                        'status' => 'Active'
                    ]
                );
                $employeeData['department_id'] = $subDepartment->id;
            } else {
                $employeeData['department_id'] = ($request->filled('department_id') && $request->department_id !== 'new') ? $request->department_id : null;
            }

            // ================================================
            // FIXED: Handle new country with firstOrCreate
            // ================================================
            if ($request->country === 'new' && $request->filled('new_country')) {
                $country = Country::firstOrCreate(
                    ['name' => trim($request->new_country)],
                    [
                        'status' => 'Active',
                        'added_by' => auth()->id()
                    ]
                );
                $employeeData['country'] = $country->name;
            } else {
                $employeeData['country'] = $request->country;
            }

            // Normalize: if probation_end_date provided, clear notice dates; if notice provided, clear probation
            if (!empty($employeeData['probation_end_date'])) {
                $employeeData['notice_start_date'] = null;
                $employeeData['notice_end_date'] = null;
            } elseif (!empty($employeeData['notice_start_date']) || !empty($employeeData['notice_end_date'])) {
                $employeeData['probation_end_date'] = null;
            }

            // Handle Employee ID (custom vs auto-generate)
            if ($request->input('employee_id_option') === 'custom' && $request->filled('employee_id')) {
                $customId = trim($request->input('employee_id'));
                if (EmployeeDetail::where('employee_id', $customId)->exists()) {
                    DB::connection($conn)->rollBack();
                    return back()->withErrors(['employee_id' => 'The Employee ID "' . $customId . '" is already in use. Please provide a unique ID.'])->withInput();
                }
                $employeeData['employee_id'] = $customId;
            } else {
                $employeeData['employee_id'] = $this->computeNextEmployeeIdWithLock((int) $request->company_id);
            }

            // Create EmployeeDetail with retry in case of concurrency
            $tries = 0;
            $created = false;
            do {
                try {
                    $detail = EmployeeDetail::create($employeeData);
                    $created = true;
                } catch (\Illuminate\Database\QueryException $qe) {
                    $tries++;
                    $duplicateId = in_array((int) ($qe->errorInfo[1] ?? 0), [1062, 19], true)
                        && str_contains(strtolower($qe->getMessage()), 'employee_id');
                    if (!$duplicateId || $request->input('employee_id_option') === 'custom' || $tries > 5) {
                        throw $qe;
                    }
                    Log::warning('employee_id collision on create, retrying', [
                        'employee_id' => $employeeData['employee_id'],
                        'tries' => $tries,
                        'error' => $qe->getMessage()
                    ]);
                    $employeeData['employee_id'] = $this->computeNextEmployeeIdWithLock((int) $request->company_id);
                    usleep(100000); // 100ms backoff
                }
            } while (! $created);

            if ($governmentIdVerification && $governmentIdCardPath) {
                GovernmentIdVerification::create([
                    'user_id' => $user->id,
                    'employee_detail_id' => $detail?->id,
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

            // Release employee-ID locks before SMTP; a slow mail server must not
            // block other employee creations or email credentials for a rolled-back user.
            DB::connection($conn)->commit();

            // Send email to employee immediately (synchronous) so mail goes to employee account now
            try {
                // send immediately, bypassing queue (useful if you want immediate delivery)
                Notification::sendNow($user, new EmployeeCreatedNotification($user, $plainPassword));

                Log::info('EmployeeCreatedNotification sent immediately', [
                    'email' => $user->email,
                    'user_id' => $user->id
                ]);
            } catch (\Exception $mailEx) {
                Log::error('Employee creation email failed', [
                    'email' => $user->email,
                    'error' => $mailEx->getMessage()
                ]);
                // proceed without failing the whole operation; client will still see success
            }

            try {
                SystemNotificationService::notifyAdmins(
                    'Employee Added',
                    auth()->user()->name . ' added employee ' . $user->name . '.',
                    route('employees.show', $user->id),
                    ['employee_id' => $user->id, 'type' => 'employee_created', 'icon' => 'fa-user-plus']
                );
            } catch (\Throwable $notifEx) {
                Log::warning('Employee creation notification failed', ['error' => $notifEx->getMessage()]);
            }

            if ($request->filled('return_url')) {
                $returnUrl = $request->input('return_url');
                if (\Illuminate\Support\Str::startsWith($returnUrl, [url('/'), '/'])) {
                    return redirect($returnUrl)
                        ->with('success', 'Employee added successfully and notified.');
                }
            }

            return redirect()->route('employees.index')
                ->with('success', 'Employee added successfully and notified.');
        } catch (\Exception $e) {
            if (DB::connection($conn)->transactionLevel() > 0) DB::connection($conn)->rollBack();
            Log::error('Store employee error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }

    private function computeNextEmployeeIdWithLock(?int $companyId = null): string
    {
        $company = $this->employeeCompany($companyId);
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

        $candidate = $prefix . '-' . str_pad($nextNumber, $digits, '0', STR_PAD_LEFT);
        while (EmployeeDetail::where('employee_id', $candidate)->exists()) {
            $nextNumber++;
            $candidate = $prefix . '-' . str_pad($nextNumber, $digits, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }
}

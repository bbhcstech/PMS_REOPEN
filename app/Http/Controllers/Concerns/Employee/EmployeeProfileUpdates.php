<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Models\Company;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\EmployeeDetail;
use App\Models\ParentDepartment;
use App\Models\User;
use App\Services\SystemNotificationService;
use App\Support\CountryPhone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

trait EmployeeProfileUpdates
{
    /**
     * Edit form.
     */
    public function edit($id)
    {
        $this->ensureAdmin();

        $employee = User::with('employeeDetail')->findOrFail($id);
        if ($employee->company_staff_role_id) {
            abort_unless(in_array(auth()->user()?->normalizedRole(), ['admin', 'administrator'], true)
                && (int) $employee->company_id === (int) auth()->user()?->company_id, 403);
            return redirect()->route('admin.upper-level-employees.index', ['edit' => $employee->id]);
        }

        return view('admin.employees.edit', [
            'employee' => $employee,
            'companies' => Company::where('status', 'active')
                ->orWhere('id', $employee->company_id)
                ->orderBy('name')
                ->get(),
            'designations' => Designation::withinLevelLimit()->orderBy('name')->get(),
            'departments' => Department::all(),
            'users' => User::where('role', 'employee')
                ->whereHas('employeeDetail', function ($q) {
                    $q->where('status', 'Active');
                })
                ->orderBy('name')->get(),
            'countries' => Country::forForms(),
            'prtdepartments' => ParentDepartment::latest()->get(),
        ]);
    }

    /**
     * Update employee.
     */
    public function update(Request $request, $id)
    {
        $this->ensureAdmin();
        $this->validateJoiningAge($request);

        // load user & detail first so we can build validation rules that ignore current records
        $user = User::findOrFail($id);
        abort_if(auth()->user()->normalizedRole() !== 'superadmin' && (int) $user->company_id !== (int) auth()->user()->company_id, 403);
        $this->guardEmployeeRole($request, $user);
        $detail = $user->employeeDetail;

        // ==============================================
        // CRITICAL FIX: EXIT DATE VALIDATION LOGIC
        // ==============================================
        if ($request->status === 'Inactive') {
            if (empty($request->exit_date)) {
                return back()->withErrors(['exit_date' => 'Exit date is required when status is Inactive.'])->withInput();
            }

            $exitDate = Carbon::parse($request->exit_date)->startOfDay();
            $joiningDateValue = $request->joining_date ?: ($detail?->joining_date ?? $user->joining_date);
            if ($joiningDateValue) {
                $joiningDate = Carbon::parse($joiningDateValue)->startOfDay();
                if ($exitDate->lte($joiningDate)) {
                    return back()->withErrors(['exit_date' => 'Exit date must be after joining date.'])->withInput();
                }
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

        // Only check email uniqueness when the email is actually being changed.
        $emailUniqueRule = 'required|email|max:255';
        if (strtolower(trim((string) $request->email)) !== strtolower(trim((string) $user->email))) {
            $emailUniqueRule .= '|unique:users,email,' . $user->id;
        }
        $dialCode = $request->mobile_country_code ?: '+91';
        $phoneRules = CountryPhone::getDigitRules($dialCode);
        $minDigits = $phoneRules['min_digits'] ?? 6;
        $maxDigits = $phoneRules['max_digits'] ?? 15;
        $phoneCountryName = $phoneRules['name'] ?? 'selected country';

        $mobileRule = ['required', 'string', 'regex:/^[1-9]\d*$/', "min:{$minDigits}", "max:{$maxDigits}"];
        $employeeIdRule = 'required|string';
        if ($detail) {
            $employeeIdRule .= '|unique:employee_details,employee_id,' . $detail->id;
        } else {
            $employeeIdRule .= '|unique:employee_details,employee_id';
        }

        $customMessages = [
            'mobile.regex' => 'The mobile number must contain digits only and cannot start with 0.',
            'mobile.min'   => $minDigits === $maxDigits
                                ? "The mobile number for {$phoneCountryName} must be exactly {$minDigits} digits."
                                : "The mobile number for {$phoneCountryName} must be between {$minDigits} and {$maxDigits} digits.",
            'mobile.max'   => $minDigits === $maxDigits
                                ? "The mobile number for {$phoneCountryName} must be exactly {$minDigits} digits."
                                : "The mobile number for {$phoneCountryName} must be between {$minDigits} and {$maxDigits} digits.",
        ];

        $request->validate([
            'employee_id'        => $employeeIdRule,
            'designation_id'     => 'required',
            'new_designation'    => 'required_if:designation_id,new|nullable|string|max:191',
            'new_designation_level' => 'required_if:designation_id,new|nullable|integer|min:0|max:' . \App\Services\DesignationLevels::maximum(),
            'name'               => 'required|string',
            'company_id'         => 'required|exists:companies,id',
            'email'              => $emailUniqueRule,
            'mobile_country_code' => 'required|string|regex:/^\+\d{1,4}$/',
            'mobile'             => $mobileRule,
            'business_address'   => 'nullable|string',
            'status'             => 'required|in:Active,Inactive',
            'exit_date'          => 'nullable|date',
            'user_role'          => 'nullable|string|in:employee,admin,manager,hr',
            'login_allowed'      => 'required|in:0,1',
            'department_id'      => 'nullable|exists:departments,id',
            'profile_picture'    => 'nullable|image|max:2048',
            'probation_end_date' => 'nullable|date',
            'notice_start_date'  => 'nullable|date',
            'notice_end_date'    => 'nullable|date',
            'reporting_to'       => 'nullable|integer|exists:users,id',
            'directory_about'    => 'nullable|string|max:3000',
            'linkedin_url'       => 'nullable|url|max:255',
            'portfolio_url'      => 'nullable|url|max:255',
            'facebook_url'       => 'nullable|url|max:255',
            'instagram_url'      => 'nullable|url|max:255',
            'x_url'              => 'nullable|url|max:255',
            'cv_file'            => 'nullable|file|mimes:pdf,doc,docx|max:4096',
        ], $customMessages);

        if ($request->designation_id !== 'new' && ! Designation::withinLevelLimit()->whereKey($request->designation_id)->exists()) {
            return back()->withErrors(['designation_id' => 'Please select a designation within your company level limit.'])->withInput();
        }

        $mobileWithCodeForValidation = $dialCode . $request->mobile;
        if (User::where('mobile', $mobileWithCodeForValidation)->where('id', '!=', $user->id)->exists()) {
            return back()->withErrors(['mobile' => 'This mobile number is already registered.'])->withInput();
        }

        // Small helper: check if $potentialAncestorId is an ancestor (manager chain) of $startUserId
        $isAncestor = function (int $potentialAncestorId, int $startUserId): bool {
            $current = $startUserId;
            while ($current) {
                $ed = EmployeeDetail::where('user_id', $current)->first();
                if (! $ed || empty($ed->reporting_to)) {
                    return false;
                }
                if (intval($ed->reporting_to) === intval($potentialAncestorId)) {
                    return true;
                }
                $current = intval($ed->reporting_to);
            }
            return false;
        };

        // Validate reporting_to: cannot report to self or to any of user's subordinates (prevent cycles)
        $reportingTo = $request->input('reporting_to');
        if (!empty($reportingTo)) {
            $reportingTo = intval($reportingTo);
            if ($reportingTo === intval($user->id)) {
                return back()->withErrors(['reporting_to' => 'Employee cannot report to themselves.'])->withInput();
            }

            // if the chosen manager reports (directly or indirectly) to this user -> disallow
            if ($isAncestor($user->id, $reportingTo)) {
                return back()->withErrors(['reporting_to' => 'Cannot set reporting manager to a subordinate (would create a cycle).'])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Format mobile number with country code prefix
            $mobileWithCode = $dialCode . $request->mobile;

            // handle profile image: delete old file if present and save new one
            if ($request->hasFile('profile_picture')) {
                $image = $request->file('profile_picture');
                // delete old
                if ($user->profile_image && file_exists(public_path($user->profile_image))) {
                    @unlink(public_path($user->profile_image));
                }
                $fileName = time() . '-' . $image->getClientOriginalName();
                $image->move(public_path('admin/uploads/profile-images'), $fileName);
                $user->profile_image = 'admin/uploads/profile-images/' . $fileName;
            }

            // update user
            $user->name = $request->name;
            $user->company_id = $request->company_id;
            $user->email = $request->email;
            $user->mobile = $mobileWithCode; // Store with +91 prefix
            $user->login_allowed = $request->login_allowed ?? 1;
            $user->email_notifications = $request->email_notifications ?? 1;
            if ($request->filled('user_role')) {
                $user->role = $request->input('user_role');
            } elseif ($request->filled('role')) {
                $user->role = $request->input('role');
            }
            if ($request->filled('password')) {
                \App\Services\PasswordManagementService::updatePassword(Auth::user(), $user, $request->password);
            }
            $user->save();

            // ensure detail exists
            if (! $detail) {
                $detail = new EmployeeDetail();
                $detail->user_id = $user->id;
            }

            // prepare employee detail payload
            $data = $request->only([
                'designation_id', 'parent_dpt_id', 'department_id', 'employee_id',
                'salutation', 'country', 'gender', 'joining_date', 'dob', 'reporting_to',
                'language', 'address', 'about', 'directory_about',
                'hourly_rate', 'slack_member_id', 'skills',
                'linkedin_url', 'portfolio_url', 'facebook_url', 'instagram_url', 'x_url',
                'probation_end_date', 'notice_start_date', 'notice_end_date',
                'employment_type', 'marital_status', 'business_address', 'status'
            ]);
            unset($data['user_role']);

            if ($request->status === 'Active') {
                $data['exit_date'] = null;
            } elseif ($request->filled('exit_date')) {
                $data['exit_date'] = Carbon::parse($request->exit_date)->format('Y-m-d');
            } else {
                $data['exit_date'] = null;
            }

            $data['login_allowed'] = (bool) $user->login_allowed;

            if (isset($data['skills'])) {
                $rawSkills = array_filter(array_map('trim', explode(',', str_replace(['·', '|'], ',', (string) $data['skills']))));
                $safeSkills = array_filter($rawSkills, fn($s) => strlen($s) > 0 && strlen($s) <= 60 && !preg_match('/(SQLSTATE|Column not found|INSERT INTO|SELECT |`)/i', $s));
                $data['skills'] = !empty($safeSkills) ? implode(', ', $safeSkills) : null;
            }

            // Add mobile without prefix for employee detail
            $data['mobile'] = $request->mobile;
            if (Schema::hasColumn('employee_details', 'company_id')) {
                $data['company_id'] = $request->company_id;
            }

            // normalize: empty department -> null
            if (empty($data['department_id'])) {
                $data['department_id'] = null;
            }

            // FIXED: Handle new designation with level
            if ($request->designation_id === 'new' && $request->filled('new_designation')) {
                $designation = Designation::firstOrCreate(
                    array_filter([
                        'name' => trim($request->new_designation),
                        'company_id' => Schema::hasColumn('designations', 'company_id') ? $request->company_id : null,
                    ], fn ($value) => ! is_null($value)),
                    [
                        'company_id' => Schema::hasColumn('designations', 'company_id') ? $request->company_id : null,
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
                $data['designation_id'] = $designation->id;
            }

            // FIXED: Handle new department with firstOrCreate
            if ($request->parent_dpt_id === 'new' && $request->filled('new_department')) {
                $department = ParentDepartment::firstOrCreate(
                    array_filter([
                        'dpt_name' => trim($request->new_department),
                        'company_id' => Schema::hasColumn('parent_departments', 'company_id') ? $request->company_id : null,
                    ], fn ($value) => ! is_null($value)),
                    [
                        'company_id' => Schema::hasColumn('parent_departments', 'company_id') ? $request->company_id : null,
                        'dpt_code' => $this->generateNextParentDepartmentCode(),
                        'status' => 'Active'
                    ]
                );
                $data['parent_dpt_id'] = $department->id;
            }

            // FIXED: Handle new sub-department with firstOrCreate
            if ($request->department_id === 'new' && $request->filled('new_sub_department')) {
                $subDepartment = Department::firstOrCreate(
                    [
                        'dpt_name' => trim($request->new_sub_department),
                        'parent_dpt_id' => $data['parent_dpt_id'],
                        'company_id' => Schema::hasColumn('departments', 'company_id') ? $request->company_id : null,
                    ],
                    [
                        'company_id' => Schema::hasColumn('departments', 'company_id') ? $request->company_id : null,
                        'dpt_code' => $this->generateNextSubDepartmentCode(),
                        'status' => 'Active'
                    ]
                );
                $data['department_id'] = $subDepartment->id;
            }

            // FIXED: Handle new country with firstOrCreate
            if ($request->country === 'new' && $request->filled('new_country')) {
                $country = Country::firstOrCreate(
                    ['name' => trim($request->new_country)],
                    [
                        'status' => 'Active',
                        'added_by' => auth()->id()
                    ]
                );
                $data['country'] = $country->name;
            }

            // Normalize dates: if probation_end_date provided, clear notice dates; if notice provided, clear probation
            if (!empty($data['probation_end_date'])) {
                $data['notice_start_date'] = null;
                $data['notice_end_date'] = null;
            } elseif (!empty($data['notice_start_date']) || !empty($data['notice_end_date'])) {
                $data['probation_end_date'] = null;
            }

            // If employee_id is empty for some reason, compute a new one (keeps parity with create/store)
            if (empty($data['employee_id'])) {
                $data['employee_id'] = $this->computeNextEmployeeIdWithLock((int) $request->company_id);
            }

            $newCvPath = $this->storeEmployeeCv($request, $detail->cv_path);
            if ($newCvPath) {
                $data['cv_path'] = $newCvPath;
            }

            // mass-assign allowed fields then save
            $detail->fill($data);
            $detail->save();

            DB::commit();

            SystemNotificationService::notifyUser(
                $user,
                'Profile Updated',
                'Your employee profile was updated by ' . auth()->user()->name . '.',
                route('employees.show', $user->id),
                ['employee_id' => $user->id, 'type' => 'employee_updated', 'icon' => 'fa-user-pen']
            );

            if ($request->filled('return_url')) {
                $returnUrl = $request->input('return_url');
                if (\Illuminate\Support\Str::startsWith($returnUrl, [url('/'), '/'])) {
                    return redirect($returnUrl)
                        ->with('success', 'Employee updated successfully.');
                }
            }

            return redirect()->route('employees.index')
                ->with('success', 'Employee updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update employee error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()])->withInput();
        }
    }

    private function storeEmployeeCv(Request $request, ?string $oldPath = null): ?string
    {
        if (! $request->hasFile('cv_file')) {
            return null;
        }

        $file = $request->file('cv_file');
        $directory = public_path('admin/uploads/employee-cvs');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = uniqid('cv_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        if ($oldPath && file_exists(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }

        return 'admin/uploads/employee-cvs/' . $filename;
    }
}

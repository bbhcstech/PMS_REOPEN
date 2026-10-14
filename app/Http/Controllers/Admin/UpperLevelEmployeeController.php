<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Company, CompanyStaffRole, Designation, EmployeeDetail, User};
use App\Services\CompanyStaffSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Hash, Schema};
use Illuminate\Support\Str;
use Illuminate\Validation\{Rule, ValidationException};

class UpperLevelEmployeeController extends Controller
{
    private function company(): Company
    {
        $actor = auth()->user();
        abort_unless($actor && in_array($actor->normalizedRole(), ['admin', 'administrator'], true), 403);
        abort_unless($actor->company_id, 403, 'No company is assigned to your account.');
        $company = Company::findOrFail($actor->company_id);
        // Fail closed if tenant middleware fell back to a different database.
        abort_unless($company->db_name && $company->db_name === config('database.connections.tenant.database'), 403, 'The company database is unavailable.');
        abort_if(session('current_company_id') && (int) session('current_company_id') !== (int) $company->id, 403);
        CompanyStaffSchema::ensure();
        return $company;
    }

    public function index(Request $request)
    {
        $company = $this->company();
        $roles = CompanyStaffRole::where('company_id', $company->id)->orderBy('name')->get();
        $designations = Designation::withinLevelLimit()->orderBy('level')->orderBy('name')->get();
        $accounts = User::where('company_id', $company->id)->whereNull('archived_at')
            ->where(function ($q) {
                $q->whereIn('role', ['hr', 'manager'])->orWhereNotNull('company_staff_role_id');
            })->with(['companyStaffRole', 'employeeDetail.designation'])->orderBy('name')->paginate(20);
        return view('admin.employees.upper-level', compact('company', 'roles', 'designations', 'accounts'));
    }

    public function storeRole(Request $request)
    {
        $company = $this->company();
        $request->merge(['name' => is_string($request->name) ? trim($request->name) : $request->name]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('tenant.company_staff_roles', 'name')->where('company_id', $company->id)],
            'access_role' => ['required', Rule::in(['hr', 'manager', 'employee'])],
        ]);
        CompanyStaffRole::create($data + ['company_id' => $company->id]);
        return redirect()->route('admin.upper-level-employees.index')->with('success', 'Company role added.');
    }

    public function store(Request $request)
    {
        return $this->saveAccount($request);
    }

    public function update(Request $request, int $id)
    {
        return $this->saveAccount($request, $id);
    }

    private function saveAccount(Request $request, ?int $id = null)
    {
        $company = $this->company();
        $user = $id ? User::where('company_id', $company->id)->whereNull('archived_at')
            ->where(function ($q) { $q->whereIn('role', ['hr', 'manager'])->orWhereNotNull('company_staff_role_id'); })
            ->findOrFail($id) : null;
        if (is_string($request->email)) $request->merge(['email' => strtolower(trim($request->email))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('tenant.users', 'email')->ignore($user?->id), function ($attribute, $value, $fail) {
                $reserved = ['hr@company.com', 'manager@company.com', 'admin@company.com', 'admin@gmail.com', 'employee@company.com'];
                $schema = Schema::connection('central');
                if (in_array($value, $reserved, true)
                    || ($schema->hasTable('companies') && DB::connection('central')->table('companies')->where('email', $value)->exists())
                    || ($schema->hasTable('super_admins') && DB::connection('central')->table('super_admins')->where('email', $value)->exists())) {
                    $fail('This email is reserved for an existing system or company administrator account.');
                }
            }],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'max:255', 'confirmed'],
            'company_staff_role_id' => ['required', 'integer', Rule::exists('tenant.company_staff_roles', 'id')->where('company_id', $company->id)],
            'designation_id' => ['required', 'integer', Rule::in(Designation::withinLevelLimit()->pluck('id')->all())],
            'joining_date' => ['required', 'date'],
            'login_allowed' => ['required', 'boolean'],
        ]);
        DB::connection('tenant')->transaction(function () use ($data, $company, $user) {
            // All company admins lock the same row for account/ID allocation.
            User::where('company_id', $company->id)->orderBy('id')->lockForUpdate()->firstOrFail();
            $role = CompanyStaffRole::where('company_id', $company->id)->lockForUpdate()->findOrFail($data['company_staff_role_id']);
            $designation = Designation::withinLevelLimit()->findOrFail($data['designation_id']);
            if (! $user && $company->max_users > 0 && User::where('company_id', $company->id)->whereNull('archived_at')->count() >= $company->max_users) {
                throw ValidationException::withMessages(['name' => 'Your company user limit has been reached.']);
            }
            $account = $user ?? new User;
            $account->fill([
                'name' => $data['name'], 'email' => $data['email'], 'company_id' => $company->id,
                'role' => $role->access_role, 'company_staff_role_id' => $role->id,
                'login_allowed' => $data['login_allowed'], 'is_active' => $data['login_allowed'],
            ]);
            if (! empty($data['password'])) $account->password = Hash::make($data['password']);
            if (Schema::connection('tenant')->hasColumn('users', 'raw_password')) $account->raw_password = null;
            $account->save();
            if ((int) $account->company_id !== (int) $company->id) throw new \RuntimeException('Company ownership could not be persisted.');
            $detail = $account->employeeDetail ?? new EmployeeDetail(['user_id' => $account->id]);
            $detail->fill(['designation_id' => $designation->id, 'joining_date' => $data['joining_date'], 'login_allowed' => $data['login_allowed']]);
            if (! $detail->exists) {
                $prefix = rtrim(trim((string) $company->employee_id_prefix) ?: strtoupper(Str::slug($company->short_name ?: $company->name)) . '-EMP', '-');
                $number = 1;
                foreach (EmployeeDetail::where('employee_id', 'like', $prefix . '-%')->pluck('employee_id') as $code) {
                    if (preg_match('/(\d+)$/', $code, $m)) $number = max($number, (int) $m[1] + 1);
                }
                $detail->employee_id = $prefix . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
            $schema = Schema::connection('tenant');
            if ($schema->hasColumn('employee_details', 'company_id')) $detail->company_id = $company->id;
            if (! $detail->exists && $schema->hasColumn('employee_details', 'business_address')) $detail->business_address = '';
            if ($schema->hasColumn('employee_details', 'status')) $detail->status = $data['login_allowed'] ? 'Active' : 'Inactive';
            $detail->save();
        }, 5);
        return redirect()->route('admin.upper-level-employees.index')->with('success', $user ? 'Employee account updated.' : 'Upper level employee added.');
    }
}

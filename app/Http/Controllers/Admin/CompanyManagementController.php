<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyManagementController extends Controller
{
    public function index(): View
    {
        $this->authorizeAdmin();

        $companies = Company::orderBy('name')->paginate(15);

        return view('admin.companies.index', compact('companies'));
    }

    public function create(): View
    {
        $this->authorizeAdmin();

        return view('admin.companies.form', ['company' => new Company()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $this->validated($request);
        $data['logo'] = $this->upload($request, 'logo');
        $data['favicon'] = $this->upload($request, 'favicon');
        $data['theme'] = $this->themePayload($request);
        $data['status'] = $data['status'] ?? 'active';

        Company::create($data);

        return redirect()->route('admin.companies.index')->with('success', 'Company created successfully.');
    }

    public function edit(Company $company): View
    {
        $this->authorizeAdmin();

        return view('admin.companies.form', compact('company'));
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $this->authorizeAdmin();

        $data = $this->validated($request, $company);

        if ($logo = $this->upload($request, 'logo')) {
            $data['logo'] = $logo;
        }

        if ($favicon = $this->upload($request, 'favicon')) {
            $data['favicon'] = $favicon;
        }

        $data['theme'] = $this->themePayload($request);
        $company->update($data);

        return redirect()->route('admin.companies.index')->with('success', 'Company updated successfully.');
    }

    public function activate(Company $company): RedirectResponse
    {
        $this->authorizeAdmin();

        $company->update(['status' => 'active']);

        return back()->with('success', 'Company activated.');
    }

    public function deactivate(Company $company): RedirectResponse
    {
        $this->authorizeAdmin();

        $company->update(['status' => 'inactive']);

        return back()->with('success', 'Company deactivated.');
    }

    private function validated(Request $request, ?Company $company = null): array
    {
        $companyId = $company?->id;

        return $request->validate([
            'company_code' => ['required', 'string', 'max:50', Rule::unique('companies', 'company_code')->ignore($companyId)],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('companies', 'email')->ignore($companyId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string'],
            'gst_number' => ['nullable', 'string', 'max:100'],
            'pan_number' => ['nullable', 'string', 'max:100'],
            'registration_number' => ['nullable', 'string', 'max:150'],
            'employee_id_prefix' => ['required', 'string', 'max:50'],
            'leave_prefix' => ['required', 'string', 'max:50'],
            'payroll_prefix' => ['required', 'string', 'max:50'],
            'payslip_prefix' => ['required', 'string', 'max:50'],
            'greeting_message' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive,suspended,trial'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
        ]);
    }

    private function upload(Request $request, string $field): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $directory = public_path('admin/uploads/companies');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = uniqid($field . '_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'admin/uploads/companies/' . $filename;
    }

    private function themePayload(Request $request): array
    {
        return [
            'primary_color' => $request->input('primary_color', '#7C3AED'),
            'secondary_color' => $request->input('secondary_color', '#8B5CF6'),
        ];
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'admin', 403);
    }
}

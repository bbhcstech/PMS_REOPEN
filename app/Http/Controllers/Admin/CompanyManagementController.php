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

        $countryMap = \App\Support\CountryPhone::map();
        $selectedCountryCode = '+91';
        $selectedCountry = 'India';
        $phoneDigits = '';

        return view('admin.companies.form', [
            'company' => new Company(),
            'countryMap' => $countryMap,
            'selectedCountryCode' => $selectedCountryCode,
            'selectedCountry' => $selectedCountry,
            'phoneDigits' => $phoneDigits,
        ]);
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

        $countryMap = \App\Support\CountryPhone::map();
        $selectedCountryCode = '+91';
        $selectedCountry = 'India';
        $phoneDigits = $company->phone ?? '';

        if (!empty($phoneDigits)) {
            $matched = false;
            $sortedCodes = [];
            foreach ($countryMap as $cName => $meta) {
                $sortedCodes[$meta['dial_code']] = strlen($meta['dial_code']);
            }
            arsort($sortedCodes);

            foreach ($sortedCodes as $dCode => $len) {
                if (str_starts_with($phoneDigits, $dCode)) {
                    $selectedCountryCode = $dCode;
                    $phoneDigits = trim(substr($phoneDigits, strlen($dCode)));
                    $matchedMeta = \App\Support\CountryPhone::findByDialCode($dCode);
                    if ($matchedMeta) {
                        $selectedCountry = $matchedMeta['name'];
                    }
                    $matched = true;
                    break;
                }
            }

            if (!$matched && preg_match('/^(\+\d{1,4})\s*(.*)$/', $phoneDigits, $matches)) {
                $selectedCountryCode = $matches[1];
                $phoneDigits = trim($matches[2]);
                $matchedMeta = \App\Support\CountryPhone::findByDialCode($matches[1]);
                if ($matchedMeta) {
                    $selectedCountry = $matchedMeta['name'];
                }
            }
        }

        return view('admin.companies.form', compact('company', 'countryMap', 'selectedCountryCode', 'selectedCountry', 'phoneDigits'));
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

        // Dispatch notification to Super Admin Alert Center
        try {
            if (class_exists(\App\Models\Central\CentralNotification::class)) {
                $updaterName = auth()->user()?->name ?? 'Company Admin';
                \App\Models\Central\CentralNotification::createNotification([
                    'company_id'        => $company->id,
                    'type'              => 'company_profile_updated',
                    'title'             => 'Tenant Company Updated: ' . $company->name,
                    'message'           => "Tenant company '{$company->name}' details were updated by {$updaterName}.",
                    'severity'          => 'info',
                    'related_module'    => 'company_profile',
                    'related_record_id' => $company->id,
                    'action_url'        => route('super-admin.companies.show', $company->id),
                    'target_audience'   => 'super_admin',
                    'is_read'           => false,
                ]);
            }
        } catch (\Throwable $e) {}

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

        // Normalize country and phone inputs
        if ($request->filled('phone_country_code') || $request->filled('phone_country_name')) {
            $countryIdentifier = $request->input('phone_country_name') ?: $request->input('phone_country_code');
            $cRules = \App\Support\CountryPhone::getDigitRules($countryIdentifier);
            $dialCode = $cRules['dial_code'];
            $request->merge([
                'phone_country_code' => $dialCode,
                'phone_country_name' => $cRules['name'],
            ]);
        }

        if ($request->filled('phone_country_code') && $request->filled('phone_number')) {
            $cleanNumber = preg_replace('/[^\d\s\-()]/', '', (string) $request->input('phone_number'));
            $fullPhone = trim($request->input('phone_country_code')) . ' ' . $cleanNumber;
            $request->merge([
                'phone' => $fullPhone,
                'phone_number' => $cleanNumber,
            ]);
        } elseif ($request->filled('phone') && !$request->filled('phone_number')) {
            $phone = trim($request->input('phone'));
            if (preg_match('/^(\+\d{1,4})\s*(.*)$/', $phone, $m)) {
                $request->merge([
                    'phone_country_code' => $m[1],
                    'phone_number' => $m[2],
                ]);
            } else {
                $cleanPhone = preg_replace('/[^\d\s\-()]/', '', $phone);
                $request->merge([
                    'phone_country_code' => '+91',
                    'phone_number' => $cleanPhone,
                    'phone' => '+91 ' . $cleanPhone,
                ]);
            }
        }

        $validated = $request->validate([
            'company_code' => ['required', 'string', 'max:50', Rule::unique('companies', 'company_code')->ignore($companyId)],
            'name' => ['required', 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('companies', 'email')->ignore($companyId)],
            'phone_country_code' => ['nullable', 'string', 'regex:/^\+\d{1,4}$/'],
            'phone_number' => ['nullable', 'string', 'regex:/^[0-9\s\-()]+$/'],
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

        // Country-specific phone digit validation if phone entered
        if ($request->filled('phone_number')) {
            $countryLookup = $request->input('phone_country_name') ?: ($validated['phone_country_code'] ?? '+91');
            $countryRules = \App\Support\CountryPhone::getDigitRules($countryLookup);
            $minDigits = $countryRules['min_digits'] ?? 10;
            $maxDigits = $countryRules['max_digits'] ?? 10;
            $countryName = $countryRules['name'] ?? 'India';
            $dialCode = $countryRules['dial_code'] ?? ($validated['phone_country_code'] ?? '+91');

            $digitsOnly = preg_replace('/\D/', '', (string) $validated['phone_number']);
            $digitCount = strlen($digitsOnly);

            if ($digitCount === 0 || $digitsOnly === str_repeat('0', $digitCount)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'phone_number' => "Please enter a valid phone number for {$countryName} ({$dialCode}).",
                ]);
            }

            if ($digitCount < $minDigits || $digitCount > $maxDigits) {
                $expectedText = ($minDigits === $maxDigits)
                    ? "must be exactly {$minDigits} digits"
                    : "must be between {$minDigits} and {$maxDigits} digits";

                throw \Illuminate\Validation\ValidationException::withMessages([
                    'phone_number' => "Phone number for {$countryName} ({$dialCode}) {$expectedText}. You entered {$digitCount} digits.",
                ]);
            }

            $validated['phone'] = $dialCode . ' ' . $digitsOnly;
        } else {
            $validated['phone'] = null;
        }

        unset($validated['phone_country_code'], $validated['phone_number']);

        return $validated;
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
        $user = auth()->user();
        $isAdmin = \Illuminate\Support\Facades\Auth::guard('super_admin')->check() || ($user && in_array(strtolower((string) $user->role), ['admin', 'superadmin'], true));
        abort_unless($isAdmin, 403);
    }
}

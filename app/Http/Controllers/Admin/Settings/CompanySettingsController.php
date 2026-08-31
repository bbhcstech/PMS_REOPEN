<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Services\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CompanySettingsController extends Controller
{
    // Show company settings page
    public function index()
    {
        $company = CompanySetting::first();
        $currentCompany = app(CompanyContext::class)->current();

        if (!$company) {
            $company = new CompanySetting();
        }

        // Fallback logo and details from central Company if tenant settings are empty
        if (empty($company->company_logo) && !empty($currentCompany?->logo)) {
            $company->company_logo = $currentCompany->logo;
        }
        if (empty($company->company_name) && !empty($currentCompany?->name)) {
            $company->company_name = $currentCompany->name;
        }
        if (empty($company->company_email) && !empty($currentCompany?->email)) {
            $company->company_email = $currentCompany->email;
        }
        if (empty($company->company_phone) && !empty($currentCompany?->phone)) {
            $company->company_phone = $currentCompany->phone;
        }
        if (empty($company->company_website) && !empty($currentCompany?->website)) {
            $company->company_website = $currentCompany->website;
        }

        $countryMap = \App\Support\CountryPhone::map();

        // Parse existing phone into country code and number
        $selectedCountryCode = '+91';
        $phoneDigits = $company->company_phone ?? '';

        if (!empty($phoneDigits)) {
            $matched = false;
            // Match longest dial codes first to avoid false prefixes (e.g. +971 vs +9)
            $sortedCodes = [];
            foreach ($countryMap as $cName => $meta) {
                $sortedCodes[$meta['dial_code']] = strlen($meta['dial_code']);
            }
            arsort($sortedCodes);

            foreach ($sortedCodes as $dCode => $len) {
                if (str_starts_with($phoneDigits, $dCode)) {
                    $selectedCountryCode = $dCode;
                    $phoneDigits = trim(substr($phoneDigits, strlen($dCode)));
                    $matched = true;
                    break;
                }
            }

            if (!$matched && preg_match('/^(\+\d{1,4})\s*(.*)$/', $phoneDigits, $matches)) {
                $selectedCountryCode = $matches[1];
                $phoneDigits = trim($matches[2]);
            }
        }

        return view('admin.settings.company', compact('company', 'countryMap', 'selectedCountryCode', 'phoneDigits'));
    }

    // Store or update company settings
    public function store(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized. Only administrators can update company settings.');
        }

        // Normalize website if provided without scheme
        if ($request->filled('company_website')) {
            $website = trim($request->input('company_website'));
            if (!preg_match('/^https?:\/\//i', $website)) {
                $website = 'https://' . $website;
                $request->merge(['company_website' => $website]);
            }
        }

        // Assemble phone if submitted via split country code + number
        if ($request->filled('company_country_code') && $request->filled('company_phone_number')) {
            $countryCode = trim($request->input('company_country_code'));
            $phoneNumber = trim($request->input('company_phone_number'));
            $cleanNumber = preg_replace('/[^\d\s\-()]/', '', $phoneNumber);
            $fullPhone = $countryCode . ' ' . $cleanNumber;
            $request->merge([
                'company_phone' => $fullPhone,
                'company_phone_number' => $cleanNumber,
            ]);
        } elseif ($request->filled('company_phone') && !$request->filled('company_phone_number')) {
            $phone = trim($request->input('company_phone'));
            if (preg_match('/^(\+\d{1,4})\s*(.*)$/', $phone, $m)) {
                $request->merge([
                    'company_country_code' => $m[1],
                    'company_phone_number' => $m[2],
                ]);
            } else {
                $request->merge([
                    'company_country_code' => '+91',
                    'company_phone_number' => $phone,
                    'company_phone' => '+91 ' . $phone,
                ]);
            }
        }

        $validated = $request->validate([
            'company_name'         => 'required|string|max:255',
            'company_email'        => [
                'required',
                'string',
                'email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                'max:255'
            ],
            'company_country_code' => ['required', 'string', 'regex:/^\+\d{1,4}$/'],
            'company_phone_number' => ['required', 'string', 'regex:/^[0-9\s\-()]+$/'],
            'company_phone'        => ['required', 'string', 'max:35'],
            'company_website'      => [
                'nullable',
                'string',
                'url',
                'regex:/^(https?:\/\/)?([a-zA-Z0-9]([a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z]{2,}(\/.*)?$/i',
                'max:255'
            ],
            'company_location'     => 'nullable|string|max:255',
            'company_logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
        ], [
            'company_name.required'         => 'Company name is required.',
            'company_email.required'        => 'Company email is required.',
            'company_email.email'           => 'Please enter a valid email address.',
            'company_email.regex'           => 'Please enter a valid email address with a valid domain (e.g. info@company.com).',
            'company_country_code.required' => 'Please select a country code.',
            'company_country_code.regex'    => 'Invalid country code format.',
            'company_phone_number.required' => 'Company phone number is required.',
            'company_phone_number.regex'    => 'Phone number contains invalid characters.',
            'company_phone.required'        => 'Company phone is required.',
            'company_website.url'           => 'Please enter a valid website URL (e.g. https://example.com).',
            'company_website.regex'         => 'Please enter a valid website domain URL (e.g. https://example.com).',
            'company_logo.image'            => 'Uploaded logo must be an image file.',
            'company_logo.mimes'            => 'Allowed logo formats: PNG, JPG, JPEG, GIF, SVG, WEBP.',
            'company_logo.max'              => 'Company logo size must not exceed 3MB.',
        ]);

        // Country-specific phone digit validation
        $countryRules = \App\Support\CountryPhone::getDigitRules($validated['company_country_code']);
        $minDigits = $countryRules['min_digits'] ?? 6;
        $maxDigits = $countryRules['max_digits'] ?? 15;
        $countryName = $countryRules['name'] ?? 'Selected Country';
        $dialCode = $countryRules['dial_code'] ?? $validated['company_country_code'];

        $digitsOnly = preg_replace('/\D/', '', (string) $validated['company_phone_number']);
        $digitCount = strlen($digitsOnly);

        if ($digitCount < $minDigits || $digitCount > $maxDigits) {
            $expectedText = ($minDigits === $maxDigits)
                ? "must be exactly {$minDigits} digits"
                : "must be between {$minDigits} and {$maxDigits} digits";

            return back()->withInput()->withErrors([
                'company_phone_number' => "Phone number for {$countryName} ({$dialCode}) {$expectedText}. You entered {$digitCount} digits.",
            ]);
        }

        $company = CompanySetting::first() ?? new CompanySetting();

        if ($request->hasFile('company_logo')) {
            // Delete existing logo file if present
            if ($company->company_logo && File::exists(public_path($company->company_logo))) {
                File::delete(public_path($company->company_logo));
            }

            $file = $request->file('company_logo');
            $filename = 'company_logo_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/company-logo');
            
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $validated['company_logo'] = 'uploads/company-logo/' . $filename;
        } else {
            // Preserve existing logo if no new file uploaded
            if (!empty($company->company_logo)) {
                $validated['company_logo'] = $company->company_logo;
            }
        }

        // Save tenant CompanySetting record
        $company->fill($validated);
        $company->save();

        // Sync with central Company model so header, sidebar, and dashboards retain logo across reloads
        $currentCompany = app(CompanyContext::class)->current();
        if ($currentCompany) {
            $centralUpdate = [
                'name'    => $validated['company_name'],
                'email'   => $validated['company_email'],
                'phone'   => $validated['company_phone'],
                'website' => $validated['company_website'] ?? null,
            ];
            if (isset($validated['company_logo'])) {
                $centralUpdate['logo'] = $validated['company_logo'];
            }
            $currentCompany->update($centralUpdate);
        }

        // Broadcast notification to Admin, HR, Manager, and Employees
        \App\Services\SystemNotificationService::notifyAllRoles(
            'Company Profile Updated',
            'Company profile and contact details have been updated by ' . (auth()->user()?->name ?? 'Admin') . '.',
            route('settings.company'),
            [
                'type' => 'setting_update',
                'setting_module' => 'company-profile',
                'icon' => 'fa-building',
                'color' => 'success',
            ]
        );

        return back()->with('success', 'Company settings updated successfully');
    }

    public function destroy()
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized. Only administrators can reset company settings.');
        }

        $company = CompanySetting::first();
        if ($company && $company->company_logo && File::exists(public_path($company->company_logo))) {
            File::delete(public_path($company->company_logo));
        }

        CompanySetting::query()->delete(); // reset all tenant settings

        // Also reset central Company logo if set
        $currentCompany = app(CompanyContext::class)->current();
        if ($currentCompany) {
            if ($currentCompany->logo && File::exists(public_path($currentCompany->logo))) {
                File::delete(public_path($currentCompany->logo));
            }
            $currentCompany->update(['logo' => null]);
        }

        // Broadcast notification to Admin, HR, Manager, and Employees
        \App\Services\SystemNotificationService::notifyAllRoles(
            'Company Profile Reset',
            'Company profile settings have been reset by ' . (auth()->user()?->name ?? 'Admin') . '.',
            route('settings.company'),
            [
                'type' => 'setting_update',
                'setting_module' => 'company-profile',
                'icon' => 'fa-rotate-left',
                'color' => 'warning',
            ]
        );

        return redirect()
            ->route('settings.company')
            ->with('success', 'Company settings reset successfully. Please add again.');
    }
}

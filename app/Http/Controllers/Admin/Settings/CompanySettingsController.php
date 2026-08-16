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

        return view('admin.settings.company', compact('company'));
    }

    // Store or update company settings
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name'     => 'required|string|max:255',
            'company_email'    => 'required|email|max:255',
            'company_phone'    => 'required|string|max:25',
            'company_website'  => 'nullable|url|max:255',
            'company_location' => 'nullable|string|max:255',
            'company_logo'     => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:3072',
        ]);

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

        return back()->with('success', 'Company settings updated successfully');
    }

    public function destroy()
    {
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

        return redirect()
            ->route('settings.company')
            ->with('success', 'Company settings reset successfully. Please add again.');
    }
}

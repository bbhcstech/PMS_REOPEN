<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class OrganizationDetailsController extends Controller
{
    public function index()
    {
        $page = 'organization-details';
        $settings = [
            'industry' => AppSetting::valueFor('org_industry', 'Information Technology'),
            'company_size' => AppSetting::valueFor('org_company_size', '50-100'),
            'registration_number' => AppSetting::valueFor('org_registration_number', 'REG-2026-98765'),
            'tax_id' => AppSetting::valueFor('org_tax_id', 'TAX-99887766'),
            'vat_number' => AppSetting::valueFor('org_vat_number', 'VAT-12345678'),
            'financial_year_start' => AppSetting::valueFor('org_financial_year_start', 'January'),
        ];

        return view('admin.settings.organization-details', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'industry' => 'required|string',
            'company_size' => 'required|string',
            'registration_number' => 'nullable|string',
            'tax_id' => 'nullable|string',
            'vat_number' => 'nullable|string',
            'financial_year_start' => 'required|string',
        ]);

        $fields = ['industry', 'company_size', 'registration_number', 'tax_id', 'vat_number', 'financial_year_start'];

        foreach ($fields as $field) {
            AppSetting::updateOrCreate(
                ['key' => 'org_' . $field],
                [
                    'label' => ucwords(str_replace('_', ' ', $field)),
                    'value' => $request->input($field, ''),
                    'page' => 'organization-details',
                    'section' => 'Organization',
                    'type' => 'text'
                ]
            );
        }

        return back()->with('success', 'Organization details updated successfully!');
    }
}

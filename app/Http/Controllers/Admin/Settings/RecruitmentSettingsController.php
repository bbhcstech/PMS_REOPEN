<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class RecruitmentSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'job_categories' => AppSetting::valueFor('recruit_job_categories', 'Engineering, Design, Marketing, Sales, HR'),
            'pipeline_stages' => AppSetting::valueFor('recruit_pipeline_stages', 'Applied, Screening, Technical Interview, HR Interview, Offered, Hired'),
            'auto_reply' => AppSetting::valueFor('recruit_auto_reply', '1'),
            'max_resume_size_mb' => AppSetting::valueFor('recruit_max_resume_size', '5'),
            'allowed_file_types' => AppSetting::valueFor('recruit_allowed_file_types', 'pdf,doc,docx'),
            'hiring_sla_days' => AppSetting::valueFor('recruit_hiring_sla_days', '30'),
            'probation_period_months' => AppSetting::valueFor('recruit_probation_period_months', '3'),
        ];

        return view('admin.settings.recruitment', compact('settings'));
    }

    public function update(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized. Only administrators can update recruitment settings.');
        }

        $request->validate([
            'job_categories' => 'required|string',
            'pipeline_stages' => 'required|string',
            'max_resume_size_mb' => 'required|numeric|min:1|max:50',
            'allowed_file_types' => 'required|string',
            'hiring_sla_days' => 'required|numeric|min:1|max:365',
            'probation_period_months' => 'required|numeric|min:0|max:24',
        ]);

        $probationMonths = (int) $request->probation_period_months;
        $slaDays = (int) $request->hiring_sla_days;

        $data = [
            'job_categories' => $request->job_categories,
            'pipeline_stages' => $request->pipeline_stages,
            'auto_reply' => $request->has('auto_reply') ? '1' : '0',
            'max_resume_size' => $request->max_resume_size_mb,
            'allowed_file_types' => $request->allowed_file_types,
            'hiring_sla_days' => $slaDays,
            'probation_period_months' => $probationMonths,
            'hiring_sla' => $slaDays . ' Days from Requirement posting to Offer',
            'probation_period' => $probationMonths . ' Months (' . ($probationMonths * 30) . ' Days standard evaluation)',
        ];

        foreach ($data as $key => $val) {
            AppSetting::updateOrCreate(
                ['key' => 'recruit_' . $key],
                [
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'value' => (string) $val,
                    'page' => 'recruitment-settings',
                    'section' => 'Recruitment',
                    'type' => 'text'
                ]
            );
        }

        // Broadcast notification to Admin, HR, Manager, and Employees
        \App\Services\SystemNotificationService::notifyAllRoles(
            'Recruitment Pipeline & Governance Settings Updated',
            'Recruitment SLA targets, probation periods, and pipeline settings updated by ' . (auth()->user()?->name ?? 'Admin') . '.',
            route('admin.settings.recruitment'),
            [
                'type' => 'setting_update',
                'setting_module' => 'recruitment',
                'icon' => 'fa-user-plus',
                'color' => 'success',
            ]
        );

        return back()->with('success', 'Recruitment SLA, Probation, and Pipeline settings updated successfully!');
    }
}

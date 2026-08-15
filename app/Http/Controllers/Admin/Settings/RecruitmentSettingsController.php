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
        ];

        return view('admin.settings.recruitment', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'job_categories' => 'required|string',
            'pipeline_stages' => 'required|string',
            'max_resume_size_mb' => 'required|numeric|min:1|max:50',
            'allowed_file_types' => 'required|string',
        ]);

        $data = [
            'job_categories' => $request->job_categories,
            'pipeline_stages' => $request->pipeline_stages,
            'auto_reply' => $request->has('auto_reply') ? '1' : '0',
            'max_resume_size' => $request->max_resume_size_mb,
            'allowed_file_types' => $request->allowed_file_types,
        ];

        foreach ($data as $key => $val) {
            AppSetting::updateOrCreate(
                ['key' => 'recruit_' . $key],
                [
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'value' => $val,
                    'page' => 'recruitment-settings',
                    'section' => 'Recruitment',
                    'type' => 'text'
                ]
            );
        }

        return back()->with('success', 'Recruitment settings updated successfully!');
    }
}

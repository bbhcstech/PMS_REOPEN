<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class PerformanceSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'appraisal_cycle' => AppSetting::valueFor('perf_appraisal_cycle', 'Quarterly'),
            'rating_scale' => AppSetting::valueFor('perf_rating_scale', '1-5 Star Scale'),
            'self_assessment' => AppSetting::valueFor('perf_self_assessment', '1'),
            'peer_review' => AppSetting::valueFor('perf_peer_review', '1'),
            'manager_review_required' => AppSetting::valueFor('perf_manager_review_required', '1'),
            'key_metrics' => AppSetting::valueFor('perf_key_metrics', 'Task Completion Rate, Code Quality, Teamwork, Punctuality'),
        ];
        $settings = array_merge($settings, app(\App\Services\AppraisalPolicy::class)->settings(), [
            'rating_scale' => '1-10 Numerical Scale', 'self_assessment' => '0', 'peer_review' => '0',
            'key_metrics' => 'Attendance, Work delivery, Cultural behaviour',
        ]);

        return view('admin.settings.performance', compact('settings'));
    }

    public function update(Request $request)
    {
        if (!\App\Services\WorkforceAccess::isAdmin(auth()->user())) {
            abort(403, 'Unauthorized. Only administrators can update performance settings.');
        }

        $request->validate([
            'appraisal_cycle' => ['required', 'in:Monthly,Quarterly,Bi-Annually,Annually'],
            'work_weight' => ['required', 'integer', 'between:1,98'],
            'attendance_weight' => ['required', 'integer', 'between:1,98'],
            'behaviour_weight' => ['required', 'integer', 'between:1,98'],
            'admin_approval_required' => ['sometimes', 'boolean'], 'increment_enabled' => ['sometimes', 'boolean'],
            'increment_a_plus' => ['required', 'numeric', 'between:0,100'], 'increment_a' => ['required', 'numeric', 'between:0,100'],
            'increment_b' => ['required', 'numeric', 'between:0,100'], 'increment_c' => ['required', 'numeric', 'between:0,100'],
            'increment_d' => ['required', 'numeric', 'between:0,100'],
            'attendance_full_marks_at' => ['sometimes', 'required', 'numeric', 'between:1,100'],
            'work_full_marks_at' => ['sometimes', 'required', 'numeric', 'between:1,100'],
            'behaviour_full_marks_at' => ['sometimes', 'required', 'numeric', 'between:1,10'],
        ]);
        if ($request->integer('work_weight') + $request->integer('attendance_weight') + $request->integer('behaviour_weight') !== 100) {
            throw \Illuminate\Validation\ValidationException::withMessages(['work_weight' => 'Attendance, work and cultural behaviour weights must total 100%.']);
        }

        $data = [
            'appraisal_cycle' => $request->appraisal_cycle,
            'rating_scale' => '1-10 Numerical Scale', 'self_assessment' => '0', 'peer_review' => '0',
            'manager_review_required' => '0', 'key_metrics' => 'Attendance, Work delivery, Cultural behaviour',
            'work_weight' => $request->integer('work_weight'), 'attendance_weight' => $request->integer('attendance_weight'),
            'behaviour_weight' => $request->integer('behaviour_weight'),
            'admin_approval_required' => $request->boolean('admin_approval_required') ? '1' : '0',
            'increment_enabled' => $request->boolean('increment_enabled') ? '1' : '0',
        ];
        foreach (['a_plus', 'a', 'b', 'c', 'd'] as $grade) $data['increment_'.$grade] = round((float) $request->input('increment_'.$grade), 2);
        foreach (['attendance', 'work', 'behaviour'] as $criterion) {
            $key = $criterion.'_full_marks_at';
            if ($request->has($key)) $data[$key] = round((float) $request->input($key), 2);
        }

        \Illuminate\Support\Facades\DB::connection('tenant')->transaction(function () use ($data) {
        foreach ($data as $key => $val) {
            AppSetting::updateOrCreate(
                ['key' => 'perf_' . $key],
                [
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'value' => $val,
                    'page' => 'performance-settings',
                    'section' => 'Performance',
                    'type' => 'text'
                ]
            );
        }
        });

        // Broadcast notification to Admin, HR, Manager, and Employees
        \App\Services\SystemNotificationService::notifyAllRoles(
            'Performance Appraisal Settings Updated',
            'Performance appraisal cycles, rating criteria, and review workflow have been updated by ' . (auth()->user()?->name ?? 'Admin') . '.',
            route('admin.settings.performance'),
            [
                'type' => 'setting_update',
                'setting_module' => 'performance',
                'icon' => 'fa-chart-line',
                'color' => 'info',
            ]
        );

        return back()->with('success', 'Performance settings updated successfully!');
    }
}

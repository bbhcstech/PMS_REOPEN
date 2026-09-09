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

        return view('admin.settings.performance', compact('settings'));
    }

    public function update(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized. Only administrators can update performance settings.');
        }

        $request->validate([
            'appraisal_cycle' => 'required|string',
            'rating_scale' => 'required|string',
            'key_metrics' => 'required|string',
        ]);

        $data = [
            'appraisal_cycle' => $request->appraisal_cycle,
            'rating_scale' => $request->rating_scale,
            'self_assessment' => $request->has('self_assessment') ? '1' : '0',
            'peer_review' => $request->has('peer_review') ? '1' : '0',
            'manager_review_required' => $request->has('manager_review_required') ? '1' : '0',
            'key_metrics' => $request->key_metrics,
        ];

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

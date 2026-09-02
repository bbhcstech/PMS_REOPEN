<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class LeaveSettingsController extends Controller
{
    public function index()
    {
        return redirect()->route('leaves.index', ['open_policy' => 1]);
    }

    public function update(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized. Only administrators can update leave settings.');
        }

        $request->validate([
            'annual_casual_leave' => 'required|numeric|min:0',
            'annual_sick_leave' => 'required|numeric|min:0',
            'carry_forward_limit' => 'required|numeric|min:0',
        ]);

        $fields = [
            'casual_allowance' => $request->annual_casual_leave,
            'sick_allowance' => $request->annual_sick_leave,
            'carry_forward_limit' => $request->carry_forward_limit,
            'enable_encashment' => $request->has('enable_encashment') ? '1' : '0',
            'require_approval' => $request->has('require_approval') ? '1' : '0',
            'probation_allowed' => $request->has('probation_leave_allowed') ? '1' : '0',
        ];

        foreach ($fields as $key => $val) {
            AppSetting::updateOrCreate(
                ['key' => 'leave_' . $key],
                [
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'value' => $val,
                    'page' => 'leave-settings',
                    'section' => 'Leave Settings',
                    'type' => 'text'
                ]
            );
        }

        // Broadcast notification to Admin, HR, Manager, and Employees
        \App\Services\SystemNotificationService::notifyAllRoles(
            'Leave Policy & Settings Updated',
            'Company leave policies, allowances, and carry-forward rules have been updated by ' . (auth()->user()?->name ?? 'Admin') . '.',
            route('admin.settings.leave'),
            [
                'type' => 'setting_update',
                'setting_module' => 'leave-settings',
                'icon' => 'fa-time-five',
                'color' => 'success',
            ]
        );

        return back()->with('success', 'Leave settings updated successfully!');
    }
}

<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class EmployeeIdSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'prefix' => AppSetting::valueFor('emp_id_prefix', 'EMP-'),
            'start_number' => AppSetting::valueFor('emp_id_start_number', '1001'),
            'number_digits' => AppSetting::valueFor('emp_id_number_digits', '4'),
            'auto_generate' => AppSetting::valueFor('emp_id_auto_generate', '1'),
            'separator' => AppSetting::valueFor('emp_id_separator', '-'),
        ];

        return view('admin.settings.employee-id', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'prefix' => 'required|string',
            'start_number' => 'required|numeric',
            'number_digits' => 'required|numeric|min:2|max:8',
            'auto_generate' => 'nullable|boolean',
            'separator' => 'required|string',
        ]);

        $data = [
            'prefix' => $request->prefix,
            'start_number' => $request->start_number,
            'number_digits' => $request->number_digits,
            'auto_generate' => $request->has('auto_generate') ? '1' : '0',
            'separator' => $request->separator,
        ];

        foreach ($data as $field => $val) {
            AppSetting::updateOrCreate(
                ['key' => 'emp_id_' . $field],
                [
                    'label' => ucwords(str_replace('_', ' ', $field)),
                    'value' => $val,
                    'page' => 'employee-id',
                    'section' => 'Employee ID',
                    'type' => 'text'
                ]
            );
        }

        return back()->with('success', 'Employee ID settings updated successfully!');
    }
}

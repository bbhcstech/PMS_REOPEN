<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class SecuritySettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'min_password_length' => AppSetting::valueFor('sec_min_password_length', '8'),
            'require_special_char' => AppSetting::valueFor('sec_require_special_char', '1'),
            'require_numbers' => AppSetting::valueFor('sec_require_numbers', '1'),
            'require_uppercase' => AppSetting::valueFor('sec_require_uppercase', '1'),
            'require_lowercase' => AppSetting::valueFor('sec_require_lowercase', '1'),
            'session_timeout_mins' => AppSetting::valueFor('sec_session_timeout_mins', '120'),
            'enable_2fa' => AppSetting::valueFor('sec_enable_2fa', '0'),
            'max_login_attempts' => AppSetting::valueFor('sec_max_login_attempts', '5'),
            'lockout_duration_mins' => AppSetting::valueFor('sec_lockout_duration_mins', '15'),
        ];

        return view('admin.settings.security', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'min_password_length' => 'required|numeric|min:6|max:32',
            'session_timeout_mins' => 'required|numeric|min:5|max:1440',
            'max_login_attempts' => 'required|numeric|min:3|max:10',
            'lockout_duration_mins' => 'required|numeric|min:1|max:1440',
        ]);

        $data = [
            'min_password_length' => $request->min_password_length,
            'require_special_char' => $request->has('require_special_char') ? '1' : '0',
            'require_numbers' => $request->has('require_numbers') ? '1' : '0',
            'require_uppercase' => $request->has('require_uppercase') ? '1' : '0',
            'require_lowercase' => $request->has('require_lowercase') ? '1' : '0',
            'session_timeout_mins' => $request->session_timeout_mins,
            'enable_2fa' => $request->has('enable_2fa') ? '1' : '0',
            'max_login_attempts' => $request->max_login_attempts,
            'lockout_duration_mins' => $request->lockout_duration_mins,
        ];

        foreach ($data as $key => $val) {
            AppSetting::updateOrCreate(
                ['key' => 'sec_' . $key],
                [
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'value' => $val,
                    'page' => 'security-settings',
                    'section' => 'Security',
                    'type' => 'text'
                ]
            );
        }

        return back()->with('success', 'Security settings updated successfully!');
    }
}

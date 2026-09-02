<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;

class LocalizationController extends Controller
{
    public function index()
    {
        $settings = [
            'currency' => AppSetting::valueFor('loc_currency', 'USD'),
            'currency_symbol' => AppSetting::valueFor('loc_currency_symbol', '$'),
            'currency_position' => AppSetting::valueFor('loc_currency_position', 'left'),
            'timezone' => AppSetting::valueFor('loc_timezone', 'UTC'),
            'date_format' => AppSetting::valueFor('loc_date_format', 'Y-m-d'),
            'time_format' => AppSetting::valueFor('loc_time_format', 'h:i A'),
            'language' => AppSetting::valueFor('loc_language', 'en'),
        ];

        return view('admin.settings.localization', compact('settings'));
    }

    public function update(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403, 'Unauthorized. Only administrators can modify settings.');
        }

        $request->validate([
            'currency' => 'required|string',
            'currency_symbol' => 'required|string',
            'currency_position' => 'required|in:left,right',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
            'language' => 'required|string',
        ]);

        $data = [
            'currency' => $request->currency,
            'currency_symbol' => $request->currency_symbol,
            'currency_position' => $request->currency_position,
            'timezone' => $request->timezone,
            'date_format' => $request->date_format,
            'time_format' => $request->time_format,
            'language' => $request->language,
        ];

        foreach ($data as $key => $val) {
            AppSetting::updateOrCreate(
                ['key' => 'loc_' . $key],
                [
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'value' => $val,
                    'page' => 'localization',
                    'section' => 'Localization',
                    'type' => 'text'
                ]
            );
        }

        \App\Services\SystemNotificationService::notifyAllRoles(
            'Localization & Regional Settings Updated',
            (auth()->user()->name ?? 'Administrator') . ' updated the system currency, timezone, and date/time formatting settings.',
            route('admin.settings.localization'),
            [
                'type' => 'setting_update',
                'setting_module' => 'localization',
                'updated_by' => auth()->id(),
            ]
        );

        return back()->with('success', 'Localization settings updated successfully!');
    }
}

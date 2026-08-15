<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Mail;

class EmailSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'mail_mailer' => AppSetting::valueFor('mail_mailer', config('mail.default', 'smtp')),
            'mail_host' => AppSetting::valueFor('mail_host', config('mail.mailers.smtp.host', 'smtp.gmail.com')),
            'mail_port' => AppSetting::valueFor('mail_port', config('mail.mailers.smtp.port', '587')),
            'mail_username' => AppSetting::valueFor('mail_username', config('mail.mailers.smtp.username', '')),
            'mail_password' => AppSetting::valueFor('mail_password', ''),
            'mail_encryption' => AppSetting::valueFor('mail_encryption', config('mail.mailers.smtp.encryption', 'tls')),
            'mail_from_address' => AppSetting::valueFor('mail_from_address', config('mail.from.address', 'noreply@company.com')),
            'mail_from_name' => AppSetting::valueFor('mail_from_name', config('mail.from.name', 'PMS Application')),
        ];

        return view('admin.settings.email', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mail_mailer' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|numeric',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        $fields = ['mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_encryption', 'mail_from_address', 'mail_from_name'];

        foreach ($fields as $field) {
            AppSetting::updateOrCreate(
                ['key' => $field],
                [
                    'label' => ucwords(str_replace('_', ' ', $field)),
                    'value' => $request->input($field, ''),
                    'page' => 'email-settings',
                    'section' => 'Email',
                    'type' => 'text'
                ]
            );
        }

        if ($request->filled('mail_password')) {
            AppSetting::updateOrCreate(
                ['key' => 'mail_password'],
                [
                    'label' => 'Mail Password',
                    'value' => $request->mail_password,
                    'page' => 'email-settings',
                    'section' => 'Email',
                    'type' => 'password'
                ]
            );
        }

        return back()->with('success', 'Email SMTP settings updated successfully!');
    }

    public function testEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email'
        ]);

        try {
            Mail::raw('This is a test email sent from your PMS Admin Settings.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('PMS SMTP Settings Test');
            });

            return back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }
}

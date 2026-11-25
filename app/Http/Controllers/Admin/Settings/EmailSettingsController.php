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
            'mail_mailer' => AppSetting::valueFor('mail_mailer', 'smtp'),
            'mail_host' => AppSetting::valueFor('mail_host', config('mail.mailers.smtp.host', 'smtp.gmail.com')),
            'mail_port' => AppSetting::valueFor('mail_port', config('mail.mailers.smtp.port', '587')),
            'mail_username' => AppSetting::valueFor('mail_username', config('mail.mailers.smtp.username', '')),
            'mail_password' => '',
            'has_mail_password' => (bool) AppSetting::valueFor('mail_password', ''),
            'mail_encryption' => AppSetting::valueFor('mail_encryption', config('mail.mailers.smtp.encryption', 'tls')),
            'mail_from_address' => AppSetting::valueFor('mail_from_address', config('mail.from.address', 'noreply@company.com')),
            'mail_from_name' => AppSetting::valueFor('mail_from_name', config('mail.from.name', 'PMS Application')),
        ];

        return view('admin.settings.email', compact('settings'));
    }

    public function update(Request $request)
    {
        if (! \App\Services\WorkforceAccess::isAdmin(auth()->user())) {
            abort(403, 'Unauthorized. Only administrators can update email settings.');
        }

        $request->validate([
            'mail_mailer' => 'required|in:smtp,sendmail,log',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|between:1,65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:4096',
            'mail_encryption' => 'required|in:tls,ssl,none',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
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
                    'value' => 'encrypted:'.\Illuminate\Support\Facades\Crypt::encryptString($request->mail_password),
                    'page' => 'email-settings',
                    'section' => 'Email',
                    'type' => 'password'
                ]
            );
        }

        app(\App\Services\CompanyMailSettings::class)->apply();

        // Broadcast notification to Admin, HR, Manager, and Employees
        \App\Services\SystemNotificationService::notifyAllRoles(
            'Email SMTP Settings Updated',
            'System email gateway and outbound SMTP configurations have been updated by ' . (auth()->user()?->name ?? 'Admin') . '.',
            route('admin.settings.email'),
            [
                'type' => 'setting_update',
                'setting_module' => 'email-settings',
                'icon' => 'fa-envelope',
                'color' => 'info',
            ]
        );

        return back()->with('success', 'Email SMTP settings updated successfully!');
    }

    public function testEmail(Request $request)
    {
        abort_unless(\App\Services\WorkforceAccess::isAdmin(auth()->user()), 403);
        $request->validate([
            'test_email' => 'required|email'
        ]);

        try {
            app(\App\Services\CompanyMailSettings::class)->requireDelivery();
            Mail::raw('This is a test email sent from your PMS Admin Settings.', function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject('PMS SMTP Settings Test');
            });

            return back()->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', $e->errors()['email'][0]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SMTP test failed', ['exception' => get_class($e)]);
            return back()->with('error', 'Test email could not be sent. Check the SMTP host, port, encryption, credentials and sender address, then try again.');
        }
    }
}

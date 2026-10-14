<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\{Crypt, Mail};
use Illuminate\Validation\ValidationException;

final class CompanyMailSettings
{
    public function __construct(private array $baseline) {}

    public function apply(): void
    {
        // Reset transports before switching companies or reloading saved credentials.
        config(['mail' => $this->baseline]);
        Mail::forgetMailers();
        if (! auth()->user()?->company_id) return;

        try {
            $settings = AppSetting::whereIn('key', [
                'mail_mailer', 'mail_host', 'mail_port', 'mail_username', 'mail_password',
                'mail_encryption', 'mail_from_address', 'mail_from_name',
            ])->pluck('value', 'key')->all();
        } catch (\Throwable $e) {
            return; // Older tenants without app_settings retain environment configuration.
        }
        if (! $settings) return;

        $smtp = $this->baseline['mailers']['smtp'];
        foreach (['host', 'port', 'username', 'password'] as $key) {
            if (array_key_exists('mail_'.$key, $settings)) $smtp[$key] = $settings['mail_'.$key];
        }
        if (str_starts_with((string) ($smtp['password'] ?? ''), 'encrypted:')) {
            $smtp['password'] = Crypt::decryptString(substr($smtp['password'], 10));
        }
        if (array_key_exists('mail_encryption', $settings)) {
            $encryption = $settings['mail_encryption'];
            $smtp['scheme'] = $encryption === 'ssl' ? 'smtps' : 'smtp';
            $smtp['auto_tls'] = $encryption !== 'none';
            $smtp['require_tls'] = $encryption === 'tls';
        }
        // A platform MAIL_URL must not override a company's saved host/credentials.
        $smtp['url'] = null;
        $smtp['port'] = (int) $smtp['port'];
        config([
            'mail.default' => $settings['mail_mailer'] ?? 'smtp',
            'mail.mailers.smtp' => $smtp,
            'mail.from.address' => $settings['mail_from_address'] ?? $this->baseline['from']['address'],
            'mail.from.name' => $settings['mail_from_name'] ?? $this->baseline['from']['name'],
        ]);
    }

    public function requireDelivery(): void
    {
        $this->apply();
        $mailer = config('mail.default');
        if (in_array($mailer, ['log', 'array', 'failover', 'roundrobin'], true)
            || ($mailer === 'smtp' && (! config('mail.mailers.smtp.host')
                || (config('mail.mailers.smtp.username') && ! config('mail.mailers.smtp.password'))
                || ! filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL)))) {
            throw ValidationException::withMessages([
                'email' => 'Email delivery is not configured. Ask your administrator to add SMTP details in Settings > Email and send a test email. You can use Invite by Link meanwhile.',
            ]);
        }
    }
}

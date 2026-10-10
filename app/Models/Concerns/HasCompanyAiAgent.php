<?php

namespace App\Models\Concerns;

trait HasCompanyAiAgent
{
    protected static function bootHasCompanyAiAgent(): void
    {
        static::saving(function ($company) {
            $settings = $company->settings ?? [];
            $agent = $settings['ai_agent'] ?? [];
            $settings['ai_agent'] = [
                'id' => $company->getOriginal('settings') ? (data_get($company->getOriginal('settings'), 'ai_agent.id') ?: ($agent['id'] ?? (string) \Illuminate\Support\Str::uuid())) : ($agent['id'] ?? (string) \Illuminate\Support\Str::uuid()),
                'name' => trim((string) $company->name) . ' Assistant',
                'provider' => 'groq',
            ];
            $company->settings = $settings;
        });
    }
}

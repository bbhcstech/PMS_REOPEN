<?php

namespace App\Console\Commands;

use App\Exceptions\AiProviderUnavailable;
use App\Services\GroundedAiResponse;
use Illuminate\Console\Command;

class CheckAssistantProvider extends Command
{
    protected $signature = 'assistant:check';

    protected $description = 'Check the configured assistant AI service using synthetic records';

    public function handle(GroundedAiResponse $provider): int
    {
        $this->line('Model: '.(config('company_ai.model') ?: '(not set)'));
        $this->line('Configuration cache: '.(app()->configurationIsCached() ? 'in use' : 'off'));

        try {
            $answer = $provider->answer('How many holidays are there this week?', [[
                'id' => 'live:holidays', 'type' => 'live', 'label' => 'Synthetic holiday record',
                'facts' => ['holidays_this_week_count' => 0, 'holidays_this_week' => 'None'],
            ]], 'Assistant diagnostic', 'company');
        } catch (AiProviderUnavailable $error) {
            $this->error($error->reason.': '.$error->getMessage());

            return self::FAILURE;
        }

        if (($answer['sources'][0]['id'] ?? null) !== 'live:holidays') {
            $this->error('The provider responded but did not return a grounded diagnostic answer.');

            return self::FAILURE;
        }

        $this->info('The configured AI service returned a grounded answer.');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Services\SubscriptionNotificationEngine;
use Illuminate\Console\Command;

class CheckSubscriptionExpirations extends Command
{
    protected $signature = 'subscriptions:check-expirations';
    protected $description = 'Evaluate daily subscription & free trial expirations, automatically suspend expired accounts at day 0, and dispatch daily warnings / suspension reminders.';

    public function handle(SubscriptionNotificationEngine $notificationEngine): int
    {
        $this->info('Starting automated daily subscription expiration check and notification engine...');

        try {
            $generatedAlertsCount = $notificationEngine->scanAndGenerateAlerts();
            $this->info("Completed daily subscription expiration processing. Notifications generated: {$generatedAlertsCount}");
        } catch (\Throwable $e) {
            $this->error('Failed to run subscription expiration scan: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}

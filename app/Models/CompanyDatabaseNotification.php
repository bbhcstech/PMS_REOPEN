<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class CompanyDatabaseNotification extends DatabaseNotification
{
    protected $connection = 'tenant';

    protected static function booted(): void
    {
        static::creating(function (self $notification) {
            $recipient = $notification->notifiable;
            if (!$recipient instanceof User || !$recipient->company_id) {
                throw new \LogicException('Tenant notification recipient must belong to a company.');
            }
            $current = \App\Services\TenantScope::companyId();
            if ($current && (int) $recipient->company_id !== $current) {
                throw new \LogicException('Tenant notifications cannot cross company workspaces.');
            }
            $data = $notification->data ?? [];
            if (isset($data['company_id']) && (int) $data['company_id'] !== (int) $recipient->company_id) {
                throw new \LogicException('Notification company does not match its recipient.');
            }
            $notification->data = ['company_id' => (int) $recipient->company_id] + $data;
        });
    }
}

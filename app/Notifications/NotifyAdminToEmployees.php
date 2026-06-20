<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotifyAdminToEmployees extends Notification
{
    use Queueable;

    public function __construct(private array $data)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return $this->data + [
            'type' => 'admin_to_employee',
            'title' => 'Notification',
            'message' => '',
            'url' => null,
            'icon' => 'fa-shield-halved',
            'color' => 'warning',
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}

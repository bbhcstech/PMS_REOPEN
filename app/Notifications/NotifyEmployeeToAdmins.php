<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NotifyEmployeeToAdmins extends Notification
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
            'type' => 'employee_to_admin',
            'title' => 'Notification',
            'message' => '',
            'url' => null,
            'icon' => 'fa-user-clock',
            'color' => 'info',
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}

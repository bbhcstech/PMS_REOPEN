<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActionNotification extends Notification
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
            'type' => 'action',
            'title' => 'Notification',
            'message' => '',
            'url' => null,
            'icon' => 'fa-bell',
            'color' => 'primary',
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}

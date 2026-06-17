<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
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
        return [
            'type' => $this->data['type'] ?? 'system',
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'] ?? '',
            'url' => $this->data['url'] ?? null,
            'icon' => $this->data['icon'] ?? 'fa-bell',
            'color' => $this->data['color'] ?? 'primary',
            'actor_id' => $this->data['actor_id'] ?? auth()->id(),
            'actor_name' => $this->data['actor_name'] ?? auth()->user()?->name,
            'entity_type' => $this->data['entity_type'] ?? null,
            'entity_id' => $this->data['entity_id'] ?? null,
            'ticket_id' => $this->data['ticket_id'] ?? null,
            'task_id' => $this->data['task_id'] ?? null,
            'project_id' => $this->data['project_id'] ?? null,
            'employee_id' => $this->data['employee_id'] ?? null,
        ];
    }
}

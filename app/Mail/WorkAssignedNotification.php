<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkAssignedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public User $developer;
    public string $taskTitle;
    public string $priority;
    public string $dueDate;
    public ?string $instructions;
    public string $portalUrl;

    public function __construct(User $developer, string $taskTitle, string $priority, string $dueDate, ?string $instructions, string $portalUrl)
    {
        $this->developer = $developer;
        $this->taskTitle = $taskTitle;
        $this->priority = $priority;
        $this->dueDate = $dueDate;
        $this->instructions = $instructions;
        $this->portalUrl = $portalUrl;
    }

    public function build(): self
    {
        return $this->subject('New Development Task Assigned: ' . $this->taskTitle)
            ->markdown('emails.work_assigned_notification')
            ->with([
                'developer' => $this->developer,
                'taskTitle' => $this->taskTitle,
                'priority' => ucfirst($this->priority),
                'dueDate' => $this->dueDate,
                'instructions' => $this->instructions,
                'portalUrl' => $this->portalUrl,
            ]);
    }
}

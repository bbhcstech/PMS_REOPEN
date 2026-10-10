<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class EmployeeInvite extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $messageText;
    public $inviteLink; // matches your blade
    public $companyName;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\User  $user
     * @param  string|null  $messageText
     * @param  string  $inviteLink
     */
    public function __construct(User $user, ?string $messageText, string $inviteLink, ?string $companyName = null)
    {
        $this->companyName = $companyName;
        $this->user = $user;
        $this->messageText = $messageText;
        $this->inviteLink = $inviteLink;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('You are invited to join ' . ($this->companyName ?: config('app.name')))
                    ->markdown('emails.employee_invite')   // use your markdown view
                    ->with([
                        'user' => $this->user,
                        'messageText' => $this->messageText,
                        'inviteLink' => $this->inviteLink, // provide the same name the blade expects
                        'companyName' => $this->companyName ?: config('app.name'),
                    ]);
    }
}

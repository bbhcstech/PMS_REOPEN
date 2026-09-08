<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeveloperAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $tempPassword;
    public string $loginUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $tempPassword, string $loginUrl)
    {
        $this->user = $user;
        $this->tempPassword = $tempPassword;
        $this->loginUrl = $loginUrl;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Your Developer Dashboard Account')
            ->markdown('emails.developer_account_created')
            ->with([
                'user' => $this->user,
                'tempPassword' => $this->tempPassword,
                'loginUrl' => $this->loginUrl,
            ]);
    }
}

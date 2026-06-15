<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $newPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Восстановление пароля - M2Profi',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: [
                'userName' => $this->user->name,
                'login' => $this->user->login,
                'newPassword' => $this->newPassword,
                'loginUrl' => route('dashboard.index'),
            ],
        );
    }
}

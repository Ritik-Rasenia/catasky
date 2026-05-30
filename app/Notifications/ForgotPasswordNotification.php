<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ForgotPasswordNotification extends Notification
{
    use Queueable;

    public string $token;
    public string $email;

    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = route('subscriber.reset-password', [
            'token' => $this->token,
            'email' => $this->email,
        ]);

        return (new MailMessage)
                    ->subject('Reset Your CataSky Password 🔑')
                    ->greeting('Hello,')
                    ->line('You are receiving this email because we received a password reset request for your subscriber account.')
                    ->action('Reset Password', $resetUrl)
                    ->line('This password reset link will expire in 60 minutes.')
                    ->line('If you did not request a password reset, no further action is required.')
                    ->line('Thank you for using CataSky!');
    }
}

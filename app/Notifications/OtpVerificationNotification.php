<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpVerificationNotification extends Notification
{
    use Queueable;

    public string $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Verify Your CataSky Account 🔒')
                    ->greeting('Hello,')
                    ->line('Thank you for registering a subscriber account on CataSky.')
                    ->line('To complete your registration and verify your business email, please use the following 6-digit verification code:')
                    ->line('**' . $this->otp . '**')
                    ->line('This code is valid for 15 minutes. If you did not request this code, please ignore this email.')
                    ->line('Thank you for partnering with CataSky!');
    }
}

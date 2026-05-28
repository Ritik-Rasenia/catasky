<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification
{
    use Queueable;

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject($this->data['subject'] ?? 'B2B Compliance Account Approved')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line($this->data['message'] ?? 'Congratulations! Your B2B Compliance Account has been reviewed and approved by the administration.')
                    ->action('Go to Dashboard', url('/dashboard'))
                    ->line('Thank you for partnering with CataSky!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'bi-shield-check',
            'title' => $this->data['title'] ?? 'Account Approved',
            'message' => $this->data['message'] ?? 'Your B2B Compliance Account has been approved.',
            'action_url' => route('dashboard'),
        ];
    }
}

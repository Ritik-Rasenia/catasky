<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriberRegistrationNotification extends Notification
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
                    ->subject($this->data['subject'] ?? 'New Subscriber Registration')
                    ->greeting('Hello Compliance Admin,')
                    ->line($this->data['message'] ?? 'A new B2B subscriber has registered and is waiting for review.')
                    ->action('Go to Approvals', url('/dashboard/saas/approvals'))
                    ->line('Please review their credentials and company details.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'bi-person-plus-fill',
            'title' => $this->data['title'] ?? 'New B2B Registration',
            'message' => $this->data['message'] ?? 'A new B2B subscriber has registered and is waiting for review.',
            'action_url' => route('admin.saas.approvals.index'),
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiryNotification extends Notification
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
                    ->subject($this->data['subject'] ?? 'Subscription Expiry Notification')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line($this->data['message'] ?? 'This is a notice regarding your B2B subscription status.')
                    ->action('Renew Subscription', url('/dashboard/subscription/plans'))
                    ->line('Please update your plan to continue uninterrupted services.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'bi-credit-card-2-front',
            'title' => $this->data['title'] ?? 'Subscription Notice',
            'message' => $this->data['message'] ?? 'Notice regarding your B2B subscription status.',
            'action_url' => route('subscriber.subscription.index'),
        ];
    }
}

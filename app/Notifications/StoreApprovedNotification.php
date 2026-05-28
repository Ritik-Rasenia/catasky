<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StoreApprovedNotification extends Notification
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
                    ->subject($this->data['subject'] ?? 'Store Configuration Approved')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line($this->data['message'] ?? 'Good news! Your B2B store configuration and branding have been reviewed and approved.')
                    ->action('View Subscriber Panel', url('/dashboard'))
                    ->line('Your storefront is now live!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'bi-shop-window',
            'title' => $this->data['title'] ?? 'Store Branding Live',
            'message' => $this->data['message'] ?? 'Your store configuration has been approved and is now live.',
            'action_url' => route('dashboard'),
        ];
    }
}

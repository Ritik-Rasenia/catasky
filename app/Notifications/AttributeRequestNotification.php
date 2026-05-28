<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttributeRequestNotification extends Notification
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
                    ->subject($this->data['subject'] ?? 'Custom Attribute Request')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line($this->data['message'] ?? 'There is an update regarding a B2B custom attribute request on the platform.')
                    ->action('Go to Approvals', $this->data['action_url'] ?? url('/dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => $this->data['icon'] ?? 'bi-sliders',
            'title' => $this->data['title'] ?? 'Attribute Update',
            'message' => $this->data['message'] ?? 'A B2B custom attribute has been updated.',
            'action_url' => $this->data['action_url'] ?? route('dashboard'),
        ];
    }
}

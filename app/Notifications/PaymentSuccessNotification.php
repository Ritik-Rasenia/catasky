<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification
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
                    ->subject($this->data['subject'] ?? 'Subscription Payment Successful')
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line($this->data['message'] ?? 'Thank you for your payment. Your B2B subscription has been processed successfully.')
                    ->action('View Billing History', url('/dashboard/subscription'))
                    ->line('A receipt has been generated in your account.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon' => 'bi-cash-stack',
            'title' => $this->data['title'] ?? 'Payment Successful',
            'message' => $this->data['message'] ?? 'Payment processed successfully.',
            'action_url' => route('subscriber.subscription.index'),
        ];
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductApprovedNotification extends Notification
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
        $statusText = ($this->data['status'] ?? 'approved') === 'approved' ? 'approved' : 'rejected';
        return (new MailMessage)
                    ->subject('Product Submission: ' . ucfirst($statusText))
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('Your product "' . ($this->data['product_name'] ?? 'Product') . '" has been ' . $statusText . ' by the compliance team.')
                    ->action('View Products', url('/dashboard/products'))
                    ->line('If you have any questions, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        $statusText = ($this->data['status'] ?? 'approved') === 'approved' ? 'Approved' : 'Rejected';
        $icon = ($this->data['status'] ?? 'approved') === 'approved' ? 'bi-box-seam' : 'bi-x-octagon';
        return [
            'icon' => $icon,
            'title' => $this->data['title'] ?? 'Product ' . $statusText,
            'message' => $this->data['message'] ?? 'Product "' . ($this->data['product_name'] ?? 'Product') . '" has been ' . strtolower($statusText) . '.',
            'action_url' => route('subscriber.products.index'),
        ];
    }
}

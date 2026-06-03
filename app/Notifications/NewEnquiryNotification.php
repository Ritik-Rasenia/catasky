<?php

namespace App\Notifications;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEnquiryNotification extends Notification
{
    use Queueable;

    protected Enquiry $enquiry;

    public function __construct(Enquiry $enquiry)
    {
        $this->enquiry = $enquiry;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $productName = $this->enquiry->subscriberProduct 
            ? $this->enquiry->subscriberProduct->name 
            : ($this->enquiry->product ? $this->enquiry->product->name : 'General');

        $isSubscriber = $notifiable->hasRole('Subscriber');
        $actionUrl = $isSubscriber 
            ? route('dashboard') 
            : route('admin.enquiries.show', $this->enquiry->id);

        return [
            'icon' => 'bi-chat-left-text-fill',
            'title' => 'New B2B Enquiry',
            'message' => 'New enquiry received from ' . $this->enquiry->name . ' for ' . $productName,
            'action_url' => $actionUrl,
            'type' => 'enquiry',
        ];
    }
}

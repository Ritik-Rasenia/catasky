<?php

namespace App\Events\Analytics;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderLogged
{
    use Dispatchable, SerializesModels;

    public $visitLogId;
    public $subscriberShareLinkId;
    public $subscriberProductId;
    public $quantity;
    public $totalPrice;
    public $customerName;
    public $customerPhone;
    public $customerEmail;
    public $message;

    public function __construct(
        $visitLogId,
        $subscriberShareLinkId,
        $subscriberProductId,
        int $quantity,
        ?float $totalPrice,
        ?string $customerName,
        ?string $customerPhone,
        ?string $customerEmail,
        ?string $message
    ) {
        $this->visitLogId = $visitLogId;
        $this->subscriberShareLinkId = $subscriberShareLinkId;
        $this->subscriberProductId = $subscriberProductId;
        $this->quantity = $quantity;
        $this->totalPrice = $totalPrice;
        $this->customerName = $customerName;
        $this->customerPhone = $customerPhone;
        $this->customerEmail = $customerEmail;
        $this->message = $message;
    }
}

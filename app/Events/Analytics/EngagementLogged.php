<?php

namespace App\Events\Analytics;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EngagementLogged
{
    use Dispatchable, SerializesModels;

    public $visitLogId;
    public $subscriberShareLinkId;
    public $userId;
    public $eventType;
    public $productId;
    public $metadata;

    public function __construct(
        $visitLogId,
        $subscriberShareLinkId,
        $userId,
        string $eventType,
        $productId = null,
        ?array $metadata = null
    ) {
        $this->visitLogId = $visitLogId;
        $this->subscriberShareLinkId = $subscriberShareLinkId;
        $this->userId = $userId;
        $this->eventType = $eventType;
        $this->productId = $productId;
        $this->metadata = $metadata;
    }
}

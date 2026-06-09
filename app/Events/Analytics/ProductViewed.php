<?php

namespace App\Events\Analytics;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductViewed
{
    use Dispatchable, SerializesModels;

    public $visitLogId;
    public $subscriberProductId;
    public $duration;
    public $browseOrder;
    public $viewedAt;

    public function __construct(
        $visitLogId,
        $subscriberProductId,
        int $duration,
        int $browseOrder,
        $viewedAt = null
    ) {
        $this->visitLogId = $visitLogId;
        $this->subscriberProductId = $subscriberProductId;
        $this->duration = $duration;
        $this->browseOrder = $browseOrder;
        $this->viewedAt = $viewedAt ?: now();
    }
}

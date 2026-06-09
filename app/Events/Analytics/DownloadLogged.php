<?php

namespace App\Events\Analytics;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DownloadLogged
{
    use Dispatchable, SerializesModels;

    public $visitLogId;
    public $subscriberShareLinkId;
    public $userId;
    public $ipAddress;
    public $fileType;
    public $downloadedAt;

    public function __construct(
        $visitLogId,
        $subscriberShareLinkId,
        $userId,
        ?string $ipAddress,
        string $fileType,
        $downloadedAt = null
    ) {
        $this->visitLogId = $visitLogId;
        $this->subscriberShareLinkId = $subscriberShareLinkId;
        $this->userId = $userId;
        $this->ipAddress = $ipAddress;
        $this->fileType = $fileType;
        $this->downloadedAt = $downloadedAt ?: now();
    }
}

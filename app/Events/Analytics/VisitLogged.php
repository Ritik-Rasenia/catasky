<?php

namespace App\Events\Analytics;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitLogged
{
    use Dispatchable, SerializesModels;

    public $subscriberShareLinkId;
    public $shareTrackId;
    public $sessionId;
    public $visitorUuid;
    public $ipAddress;
    public $userAgent;
    public $referrer;
    public $openedAt;

    public function __construct(
        $subscriberShareLinkId,
        $shareTrackId,
        string $sessionId,
        ?string $visitorUuid,
        ?string $ipAddress,
        ?string $userAgent,
        ?string $referrer,
        $openedAt = null
    ) {
        $this->subscriberShareLinkId = $subscriberShareLinkId;
        $this->shareTrackId = $shareTrackId;
        $this->sessionId = $sessionId;
        $this->visitorUuid = $visitorUuid;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->referrer = $referrer;
        $this->openedAt = $openedAt ?: now();
    }
}

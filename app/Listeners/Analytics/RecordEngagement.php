<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\EngagementLogged;
use App\Models\EngagementLog;
use App\Models\VisitLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecordEngagement
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(EngagementLogged $event): void
    {
        try {
            EngagementLog::create([
                'visit_log_id'             => $event->visitLogId,
                'subscriber_share_link_id' => $event->subscriberShareLinkId,
                'user_id'                  => $event->userId,
                'event_type'               => $event->eventType,
                'subscriber_product_id'    => $event->productId,
                'metadata'                 => $event->metadata,
            ]);

            // Mark visit as non-bounce since they engaged
            if ($event->visitLogId) {
                $visit = VisitLog::find($event->visitLogId);
                if ($visit && $visit->bounce) {
                    $visit->update(['bounce' => false]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Analytics Engagement Logging failed: ' . $e->getMessage());
        }
    }
}

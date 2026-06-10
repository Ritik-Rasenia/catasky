<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\DownloadLogged;
use App\Models\DownloadLog;
use App\Models\VisitLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecordDownload
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(DownloadLogged $event): void
    {
        try {
            DownloadLog::create([
                'visit_log_id'             => $event->visitLogId,
                'subscriber_share_link_id' => $event->subscriberShareLinkId,
                'user_id'                  => $event->userId,
                'ip_address'               => $event->ipAddress,
                'file_type'                => $event->fileType,
                'downloaded_at'            => $event->downloadedAt,
            ]);

            // Mark the visit log bounce as false since they downloaded a file!
            if ($event->visitLogId) {
                $visit = VisitLog::find($event->visitLogId);
                if ($visit && $visit->bounce) {
                    $visit->update(['bounce' => false]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Analytics Download Logging failed: ' . $e->getMessage());
        }
    }
}

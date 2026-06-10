<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\ProductViewed;
use App\Models\ProductViewLog;
use App\Models\VisitLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecordProductView
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ProductViewed $event): void
    {
        try {
            // Check if this product view already exists for this visit (to prevent duplicates if user toggles quick specs)
            // Or we can just log a new view to build a full sequence. The sequence is defined by browse_order.
            ProductViewLog::create([
                'visit_log_id'          => $event->visitLogId,
                'subscriber_product_id' => $event->subscriberProductId,
                'viewed_at'             => $event->viewedAt,
                'duration'              => $event->duration,
                'browse_order'          => $event->browseOrder,
            ]);

            // Mark the visit log bounce as false since they interacted with a product!
            $visit = VisitLog::find($event->visitLogId);
            if ($visit && $visit->bounce) {
                $visit->update(['bounce' => false]);
            }
        } catch (\Exception $e) {
            Log::error('Analytics Product View Logging failed: ' . $e->getMessage());
        }
    }
}

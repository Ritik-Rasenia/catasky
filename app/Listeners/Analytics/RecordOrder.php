<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\OrderLogged;
use App\Models\OrderLog;
use App\Models\VisitLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecordOrder
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderLogged $event): void
    {
        try {
            OrderLog::create([
                'visit_log_id'             => $event->visitLogId,
                'subscriber_share_link_id' => $event->subscriberShareLinkId,
                'subscriber_product_id'    => $event->subscriberProductId,
                'quantity'                 => $event->quantity,
                'total_price'              => $event->totalPrice,
                'customer_name'            => $event->customerName,
                'customer_phone'           => $event->customerPhone,
                'customer_email'           => $event->customerEmail,
                'message'                  => $event->message,
                'status'                   => 'pending',
            ]);

            // Mark the visit log bounce as false since they placed an order!
            if ($event->visitLogId) {
                $visit = VisitLog::find($event->visitLogId);
                if ($visit && $visit->bounce) {
                    $visit->update(['bounce' => false]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Analytics Order Logging failed: ' . $e->getMessage());
        }
    }
}

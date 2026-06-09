<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SubscriberShareLink;
use App\Models\ShareTrack;
use App\Models\VisitLog;
use App\Models\ProductViewLog;
use App\Models\DownloadLog;
use App\Models\EngagementLog;
use App\Models\SubscriberProduct;
use App\Events\Analytics\VisitLogged;
use App\Events\Analytics\ProductViewed;
use App\Events\Analytics\DownloadLogged;
use App\Events\Analytics\OrderLogged;
use App\Events\Analytics\EngagementLogged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AnalyticsApiController extends Controller
{
    /**
     * Start a visitor session (log initial visit).
     */
    public function logVisit(Request $request)
    {
        $request->validate([
            'token'        => 'required|string|exists:subscriber_share_links,token',
            'track_token'  => 'nullable|string',
            'visitor_uuid' => 'required|string',
            'session_id'   => 'required|string',
            'referrer'     => 'nullable|string',
        ]);

        $shareLink = SubscriberShareLink::where('token', $request->token)->firstOrFail();

        // Resolve share track (tracking token)
        $shareTrack = null;
        if ($request->filled('track_token')) {
            $shareTrack = ShareTrack::where('tracking_token', $request->track_token)->first();
        }

        // If no tracking token matches, or none provided, resolve/create a default Direct Link track
        if (!$shareTrack) {
            $shareTrack = ShareTrack::firstOrCreate(
                [
                    'subscriber_share_link_id' => $shareLink->id,
                    'channel'                  => 'direct_link',
                    'user_id'                  => $shareLink->user_id,
                ],
                [
                    'tracking_token'           => 'DL_' . Str::random(12),
                    'shared_at'                => now(),
                ]
            );
        }

        // Dispatch asynchronous visit logger event
        VisitLogged::dispatch(
            $shareLink->id,
            $shareTrack->id,
            $request->session_id,
            $request->visitor_uuid,
            $request->ip(),
            $request->userAgent(),
            $request->referrer,
            now()
        );

        // Increment the legacy view count on the share link for backwards compatibility
        $shareLink->incrementView();

        return response()->json([
            'success'    => true,
            'message'    => 'Visit logged successfully',
            'session_id' => $request->session_id,
        ]);
    }

    /**
     * Record a heartbeat update.
     * Keeps session alive, updates duration, and adds time to active product.
     */
    public function heartbeat(Request $request)
    {
        $request->validate([
            'session_id'        => 'required|string',
            'active_product_id' => 'nullable|integer',
            'seconds'           => 'nullable|integer',
        ]);

        $seconds = $request->input('seconds', 10);
        $sessionId = $request->session_id;

        // Try to update VisitLog. Note that the record might be queued, so we query it.
        $visitLog = VisitLog::where('session_id', $sessionId)->first();

        if ($visitLog) {
            // Update session active timers
            $visitLog->closed_at = now();
            $visitLog->total_time_spent += $seconds;
            
            // If they stayed for longer than 10 seconds, it's not a bounce
            if ($visitLog->total_time_spent >= 10) {
                $visitLog->bounce = false;
            }
            $visitLog->save();

            // Update product view duration if currently viewing a product details view
            if ($request->filled('active_product_id')) {
                $productView = ProductViewLog::where('visit_log_id', $visitLog->id)
                    ->where('subscriber_product_id', $request->active_product_id)
                    ->orderBy('viewed_at', 'desc')
                    ->first();

                if ($productView) {
                    $productView->duration += $seconds;
                    $productView->save();
                }
            }

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Session log not found'], 404);
    }

    /**
     * Log a product details view.
     */
    public function logProductView(Request $request)
    {
        $request->validate([
            'session_id'   => 'required|string',
            'product_id'   => 'required|integer|exists:subscriber_products,id',
            'browse_order' => 'required|integer',
        ]);

        $visitLog = VisitLog::where('session_id', $request->session_id)->first();

        if ($visitLog) {
            ProductViewed::dispatch(
                $visitLog->id,
                $request->product_id,
                0, // start with 0 duration, updated via heartbeat
                $request->browse_order,
                now()
            );

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Session log not found'], 404);
    }

    /**
     * Log a download click.
     * Supports both share-link-based downloads (with token) and frontend store downloads (with user_id).
     * Writes to BOTH download_logs (for Downloads KPI) and engagement_logs (for Engagement Events).
     */
    public function logDownload(Request $request)
    {
        $request->validate([
            'session_id' => 'nullable|string',
            'token'      => 'nullable|string',
            'user_id'    => 'nullable|integer',
            'file_type'  => 'required|string|in:pdf,brochure,catalog,image',
        ]);

        Log::info('[DownloadTracking] Download API hit', [
            'session_id' => $request->session_id,
            'token'      => $request->token ? 'present' : 'null',
            'user_id'    => $request->user_id,
            'file_type'  => $request->file_type,
            'ip'         => $request->ip(),
        ]);

        $visitLog = null;
        if ($request->filled('session_id')) {
            $visitLog = VisitLog::where('session_id', $request->session_id)->first();
        }

        $shareLinkId = null;
        $userId = $request->input('user_id');

        if ($request->filled('token')) {
            $shareLink = SubscriberShareLink::where('token', $request->token)->first();
            if ($shareLink) {
                $shareLinkId = $shareLink->id;
                $userId = $userId ?: $shareLink->user_id;
            }
        }

        // ── Write directly to download_logs table (for Downloads KPI) ───────
        try {
            DownloadLog::create([
                'visit_log_id'             => $visitLog?->id,
                'subscriber_share_link_id' => $shareLinkId,
                'user_id'                  => $userId,
                'ip_address'               => $request->ip(),
                'file_type'                => $request->file_type,
                'downloaded_at'            => now(),
            ]);

            Log::info('[DownloadTracking] DownloadLog created successfully', [
                'user_id'    => $userId,
                'file_type'  => $request->file_type,
                'share_link' => $shareLinkId,
            ]);
        } catch (\Exception $e) {
            Log::error('[DownloadTracking] Failed to create DownloadLog: ' . $e->getMessage());
        }

        // ── Write to engagement_logs table (for Engagement Events) ──────────
        try {
            $eventType = $request->file_type === 'image' ? 'image_download' : 'pdf_download';
            EngagementLog::create([
                'visit_log_id'             => $visitLog?->id,
                'subscriber_share_link_id' => $shareLinkId,
                'user_id'                  => $userId,
                'event_type'               => $eventType,
                'subscriber_product_id'    => null,
                'metadata'                 => [
                    'file_type' => $request->file_type,
                    'ip'        => $request->ip(),
                    'source'    => 'frontend_store_download',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[DownloadTracking] Failed to create EngagementLog: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }

    /**
     * Log a checkout / order click.
     */
    public function logOrder(Request $request)
    {
        $request->validate([
            'session_id'     => 'required|string',
            'token'          => 'required|string|exists:subscriber_share_links,token',
            'product_id'     => 'nullable|integer|exists:subscriber_products,id',
            'quantity'       => 'required|integer|min:1',
            'customer_name'  => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'message'        => 'nullable|string',
        ]);

        $visitLog = VisitLog::where('session_id', $request->session_id)->first();
        $shareLink = SubscriberShareLink::where('token', $request->token)->firstOrFail();

        $totalPrice = null;
        if ($request->filled('product_id')) {
            $product = SubscriberProduct::find($request->product_id);
            if ($product) {
                $price = $product->offer_price ?: ($product->mrp ?: 0);
                $totalPrice = $price * $request->quantity;
            }
        }

        OrderLogged::dispatch(
            $visitLog?->id,
            $shareLink->id,
            $request->product_id,
            $request->quantity,
            $totalPrice,
            $request->customer_name,
            $request->customer_phone,
            $request->customer_email,
            $request->message
        );

        return response()->json(['success' => true]);
    }

    /**
     * Log an engagement event (whatsapp_click, call_click, enquiry_submit, etc.).
     * Supports both share-link-based and frontend store-based tracking.
     */
    public function logEngagement(Request $request)
    {
        $request->validate([
            'session_id' => 'nullable|string',
            'token'      => 'nullable|string',
            'user_id'    => 'nullable|integer',
            'event_type' => 'required|string|in:whatsapp_click,call_click,enquiry_submit,catalogue_open,product_detail_open,email_click,direct_link,copy_link,pdf_share,image_share,pdf_download,image_download,whatsapp_pdf_share,whatsapp_image_share',
            'product_id' => 'nullable|integer|exists:subscriber_products,id',
            'product_ids' => 'nullable|array',
            'metadata'   => 'nullable|array',
        ]);

        $visitLog = null;
        if ($request->filled('session_id')) {
            $visitLog = VisitLog::where('session_id', $request->session_id)->first();
        }

        // Resolve share link from token, or resolve user_id for frontend store tracking
        $shareLinkId = null;
        $userId = $request->input('user_id');

        if ($request->filled('token')) {
            $shareLink = SubscriberShareLink::where('token', $request->token)->first();
            if ($shareLink) {
                $shareLinkId = $shareLink->id;
                $userId = $userId ?: $shareLink->user_id;
            }
        }

        EngagementLogged::dispatch(
            $visitLog?->id,
            $shareLinkId,
            $userId,
            $request->event_type,
            $request->product_id,
            array_merge($request->metadata ?? [], [
                'product_ids' => $request->input('product_ids'),
                'source' => 'frontend_store',
            ])
        );

        return response()->json(['success' => true]);
    }
}

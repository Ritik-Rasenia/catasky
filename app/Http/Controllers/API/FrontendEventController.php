<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Unified frontend event ingestion endpoint.
 *
 * Route: POST /api/track-event  (public, no auth)
 *
 * Accepts ALL frontend-driven analytics events through a single endpoint.
 * Replaces the legacy fragmented multi-endpoint tracking architecture.
 *
 * Event taxonomy (strict):
 *   session_start  | session_end   | catalogue_open  | product_view
 *   share_whatsapp | share_any     | download_pdf     | download_image
 *   enquiry_submit | order_create  | link_click
 */
class FrontendEventController extends Controller
{
    /**
     * Allowed event types — anything else is rejected.
     */
    private const ALLOWED_EVENTS = [
        'session_start',
        'session_end',
        'catalogue_open',
        'product_view',
        'share_whatsapp',
        'share_any',
        'download_pdf',
        'download_image',
        'enquiry_submit',
        'order_create',
        'link_click',
    ];

    /**
     * Store a frontend tracking event.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type'   => ['required', 'string', 'in:' . implode(',', self::ALLOWED_EVENTS)],
            'session_id'   => ['required', 'string', 'max:64'],
            'product_id'   => ['nullable', 'integer'],
            'catalogue_id' => ['nullable', 'integer'],
            'user_id'      => ['nullable', 'integer'],
            'device'       => ['nullable', 'string', 'max:16'],
            'url'          => ['nullable', 'string', 'max:2048'],
            'referrer'     => ['nullable', 'string', 'max:2048'],
            'meta'         => ['nullable', 'array'],
        ]);

        try {
            // Auto-detect device from User-Agent if not provided by frontend
            $device = $validated['device'] ?? $this->detectDevice($request->userAgent());

            // Build the event payload
            $eventData = [
                'user_id'      => $validated['user_id']      ?? null,
                'session_id'   => $validated['session_id'],
                'event_type'   => $validated['event_type'],
                'product_id'   => $validated['product_id']   ?? null,
                'catalogue_id' => $validated['catalogue_id'] ?? null,
                'device'       => $device,
                'url'          => $validated['url']          ?? $request->header('referer'),
                'referrer'     => $validated['referrer']     ?? null,
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
                'meta'         => $validated['meta']         ?? null,
                'created_at'   => now(),
            ];

            // ── Session lifecycle handling ──────────────────────────────
            if ($validated['event_type'] === 'session_end') {
                $this->handleSessionEnd($validated['session_id'], $eventData);
            } else {
                AnalyticsEvent::create($eventData);
            }

            return response()->json(['ok' => true], 200);
        } catch (\Exception $e) {
            Log::error('[Analytics] Failed to store event: ' . $e->getMessage(), [
                'event_type' => $validated['event_type'] ?? 'unknown',
                'session_id' => $validated['session_id'] ?? null,
                'ip'         => $request->ip(),
            ]);

            return response()->json(['ok' => false], 500);
        }
    }

    /**
     * Handle session_end: update the matching session_start record's meta
     * with duration and bounce info, then insert the session_end event.
     */
    private function handleSessionEnd(string $sessionId, array $eventData): void
    {
        // Find the session_start for this session
        $sessionStart = AnalyticsEvent::where('session_id', $sessionId)
            ->where('event_type', 'session_start')
            ->orderBy('created_at', 'asc')
            ->first();

        $duration = 0;
        $isBounce = true;

        if ($sessionStart) {
            $startTs = $sessionStart->created_at->timestamp;
            $endTs   = now()->timestamp;
            $duration = max(0, $endTs - $startTs);

            // Bounce = session lasted less than 10 seconds OR only had session_start + session_end
            $otherEvents = AnalyticsEvent::where('session_id', $sessionId)
                ->whereNotIn('event_type', ['session_start', 'session_end'])
                ->count();

            $isBounce = $duration < 10 || $otherEvents === 0;

            // Update session_start meta with final duration + bounce flag
            $meta = $sessionStart->meta ?? [];
            $meta['duration'] = $duration;
            $meta['bounce']   = $isBounce;
            $sessionStart->meta = $meta;
            $sessionStart->save();
        }

        // Store the session_end event with duration metadata
        $meta = $eventData['meta'] ?? [];
        $meta['duration'] = $duration;
        $meta['bounce']   = $isBounce;
        $eventData['meta'] = $meta;

        AnalyticsEvent::create($eventData);
    }

    /**
     * Detect device type from User-Agent string.
     */
    private function detectDevice(?string $ua): string
    {
        if (!$ua) {
            return 'desktop';
        }

        $ua = strtolower($ua);

        if (preg_match('/mobile|android.*mobile|iphone|ipod|opera mini|iemobile/i', $ua)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad|android(?!.*mobile)|kindle|silk/i', $ua)) {
            return 'tablet';
        }

        return 'desktop';
    }
}

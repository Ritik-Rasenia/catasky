<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\FrontendEvent;
use App\Models\SubscriberShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Unified frontend event ingestion endpoint.
 *
 * Route: POST /api/track-event  (public, no auth)
 *
 * Tracks all frontend interactions into frontend_events as the single source of truth.
 */
class FrontendEventController extends Controller
{
    /**
     * Store or update a frontend tracking event.
     */
    public function store(Request $request)
    {
        // ── 1. Heartbeat Update Flow ─────────────────────────────────────
        if ($request->has('event_id') && $request->has('heartbeat')) {
            $eventId = (int) $request->input('event_id');
            $duration = (int) $request->input('duration_seconds', 0);

            try {
                $event = FrontendEvent::find($eventId);
                if ($event) {
                    $event->update([
                        'duration_seconds' => $duration
                    ]);
                    return response()->json(['success' => true, 'updated' => $eventId], 200);
                }
                return response()->json(['success' => false, 'message' => 'Event not found'], 404);
            } catch (\Exception $e) {
                Log::error('[Analytics Heartbeat] Failed: ' . $e->getMessage());
                return response()->json(['success' => false], 500);
            }
        }

        // ── 2. Create Event Flow ─────────────────────────────────────────
        $validated = $request->validate([
            'event_type'    => ['required', 'string'],
            'session_id'    => ['nullable', 'string', 'max:64'],
            'product_id'    => ['nullable', 'integer'],
            'catalogue_id'  => ['nullable', 'integer'],
            'subscriber_id' => ['nullable', 'integer'],
            'duration_seconds' => ['nullable', 'integer'],
            'meta'          => ['nullable', 'array'],
            // browser-sent extra data
            'url'           => ['nullable', 'string', 'max:2048'],
            'referrer'      => ['nullable', 'string', 'max:2048'],
        ]);

        try {
            $ua = $request->userAgent();
            $device = $this->detectDevice($ua);
            $browser = $this->detectBrowser($ua);
            $country = $request->header('CF-IPCountry') ?: ($request->server('HTTP_CF_IPCOUNTRY') ?: null);

            $canonicalEvent = FrontendEvent::resolveEventType($validated['event_type']);

            // Resolve subscriber_id from catalogue if not provided
            $subscriberId = $validated['subscriber_id'] ?? null;
            $catalogueId = $validated['catalogue_id'] ?? null;
            if (!$subscriberId && $catalogueId) {
                $link = SubscriberShareLink::find($catalogueId);
                if ($link) {
                    $subscriberId = $link->user_id;
                }
            }

            // Compile meta json
            $metaJson = array_merge($validated['meta'] ?? [], [
                'url'       => $validated['url'] ?? $request->header('referer'),
                'referrer'  => $validated['referrer'] ?? null,
                'device'    => $device,
                'browser'   => $browser,
                'country'   => $country,
            ]);

            // Save to frontend_events (Single Source of Truth)
            $event = FrontendEvent::create([
                'subscriber_id'    => $subscriberId,
                'catalogue_id'     => $catalogueId,
                'product_id'       => $validated['product_id'] ?? null,
                'session_id'       => $validated['session_id'] ?? null,
                'event_type'       => $canonicalEvent,
                'ip_address'       => $request->ip(),
                'user_agent'       => substr($ua, 0, 512),
                'duration_seconds' => $validated['duration_seconds'] ?? 0,
                'meta_json'        => $metaJson,
            ]);

            // ── 3. Write to Legacy analytics_events (For Backwards Compatibility) ──
            try {
                $legacyType = strtolower($canonicalEvent);
                $legacyTypeMap = [
                    'pdf_download'     => 'download_pdf',
                    'image_download'   => 'download_image',
                    'catalogue_share'  => 'share_any',
                    'product_view'     => 'product_view',
                    'catalogue_open'   => 'catalogue_open',
                ];
                if (isset($legacyTypeMap[$legacyType])) {
                    $legacyType = $legacyTypeMap[$legacyType];
                }

                if ($validated['session_id']) {
                    AnalyticsEvent::create([
                        'user_id'      => $subscriberId,
                        'session_id'   => $validated['session_id'],
                        'event_type'   => $legacyType,
                        'product_id'   => $validated['product_id'] ?? null,
                        'catalogue_id' => $catalogueId,
                        'device'       => $device,
                        'url'          => $validated['url'] ?? $request->header('referer'),
                        'referrer'     => $validated['referrer'] ?? null,
                        'ip_address'   => $request->ip(),
                        'user_agent'   => $ua,
                        'meta'         => $metaJson,
                        'created_at'   => now(),
                    ]);
                }
            } catch (\Exception $ex) {
                // Do not block flow if legacy fails
                Log::warning('[Legacy Analytics] Write failed: ' . $ex->getMessage());
            }

            return response()->json([
                'success' => true,
                'event_id' => $event->id
            ], 200);

        } catch (\Exception $e) {
            Log::error('[Analytics API] Failed to store event: ' . $e->getMessage(), [
                'event_type' => $validated['event_type'] ?? 'unknown',
                'ip'         => $request->ip(),
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Detect device type from User-Agent.
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

    /**
     * Detect browser name from User-Agent.
     */
    private function detectBrowser(?string $ua): string
    {
        if (!$ua) {
            return 'unknown';
        }
        $ua = strtolower($ua);
        if (str_contains($ua, 'whatsapp')) {
            return 'WhatsApp';
        }
        if (str_contains($ua, 'edg')) {
            return 'Edge';
        }
        if (str_contains($ua, 'chrome') && !str_contains($ua, 'chromium')) {
            return 'Chrome';
        }
        if (str_contains($ua, 'safari') && !str_contains($ua, 'chrome')) {
            return 'Safari';
        }
        if (str_contains($ua, 'firefox')) {
            return 'Firefox';
        }
        if (str_contains($ua, 'opera') || str_contains($ua, 'opr')) {
            return 'Opera';
        }
        return 'Other';
    }
}

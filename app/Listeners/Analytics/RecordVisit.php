<?php

namespace App\Listeners\Analytics;

use App\Events\Analytics\VisitLogged;
use App\Models\VisitLog;
use App\Helpers\AgentHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecordVisit implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(VisitLogged $event): void
    {
        try {
            // 1. GDPR-friendly IP masking
            $maskedIp = $this->maskIpAddress($event->ipAddress);

            // 2. Browser, OS, Device Type detection
            [$deviceType, $browser, $os] = AgentHelper::detect($event->userAgent);

            // 3. Location Lookup (Geographic location from IP)
            [$country, $city] = $this->resolveLocation($event->ipAddress);

            // 4. Save visit log
            VisitLog::create([
                'subscriber_share_link_id' => $event->subscriberShareLinkId,
                'share_track_id'           => $event->shareTrackId,
                'session_id'               => $event->sessionId,
                'visitor_uuid'             => $event->visitorUuid,
                'ip_address'               => $maskedIp,
                'device_type'              => $deviceType,
                'browser'                  => $browser,
                'os'                       => $os,
                'country'                  => $country,
                'city'                     => $city,
                'referrer'                 => $event->referrer,
                'opened_at'                => $event->openedAt,
                'total_time_spent'         => 0,
                'bounce'                   => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics Visit Logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Mask IP address for GDPR compliance (IPv4 and IPv6 support).
     */
    private function maskIpAddress(?string $ip): ?string
    {
        if (empty($ip)) {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $parts = explode('.', $ip);
            if (count($parts) === 4) {
                $parts[3] = '0';
                return implode('.', $parts);
            }
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $parts = explode(':', $ip);
            if (count($parts) > 1) {
                $parts[count($parts) - 1] = '0000';
                return implode(':', $parts);
            }
        }

        return $ip;
    }

    /**
     * Resolve location from IP address.
     */
    private function resolveLocation(?string $ip): array
    {
        if (empty($ip) || $ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return ['Localhost', 'Localhost'];
        }

        try {
            // Free GeoIP lookup with timeout to avoid hanging the queue
            $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success') {
                    return [
                        $data['country'] ?? 'Unknown Country',
                        $data['city'] ?? 'Unknown City'
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fallback to unknown if API timeouts
        }

        return ['Unknown Country', 'Unknown City'];
    }
}

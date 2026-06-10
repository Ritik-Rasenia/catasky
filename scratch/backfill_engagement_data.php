<?php

/**
 * Backfill engagement_logs with missing product and catalogue data.
 * 
 * Run: php scratch/backfill_engagement_data.php
 * Or from project root: php -f scratch/backfill_engagement_data.php
 *
 * This script:
 * 1. Finds engagement_logs with null subscriber_product_id and null subscriber_share_link_id
 * 2. Tries to match them with download_logs from the same user + same day
 * 3. Updates share_link_id from matching download_log
 * 4. If the share_link has a subscriber_product_id, sets it on the engagement log
 * 5. For catalog-type shares, sets the catalogue title in metadata
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\EngagementLog;
use App\Models\DownloadLog;
use App\Models\SubscriberShareLink;

echo "=== Engagement Logs Backfill Script ===\n\n";

$total = EngagementLog::count();
$nullBoth = EngagementLog::whereNull('subscriber_product_id')
    ->whereNull('subscriber_share_link_id')
    ->count();

echo "Total engagement logs: {$total}\n";
echo "Logs missing BOTH product and catalogue: {$nullBoth}\n\n";

if ($nullBoth === 0) {
    echo "Nothing to backfill. All logs have product or catalogue data.\n";
    exit(0);
}

$logs = EngagementLog::whereNull('subscriber_product_id')
    ->whereNull('subscriber_share_link_id')
    ->get();

$updated = 0;
$skipped = 0;

foreach ($logs as $log) {
    $userId = $log->user_id;
    $eventDate = $log->created_at->toDateString();
    
    // Try to find a matching download_log from same user on same day
    $download = DownloadLog::where('user_id', $userId)
        ->whereDate('downloaded_at', $eventDate)
        ->whereNotNull('subscriber_share_link_id')
        ->first();
    
    if ($download && $download->subscriber_share_link_id) {
        $shareLink = SubscriberShareLink::withTrashed()->find($download->subscriber_share_link_id);
        
        if ($shareLink) {
            $log->subscriber_share_link_id = $shareLink->id;
            
            // If share link has a specific product, use it
            if ($shareLink->subscriber_product_id) {
                $log->subscriber_product_id = $shareLink->subscriber_product_id;
            }
            
            // Update metadata with catalogue info
            $metadata = $log->metadata ?? [];
            $metadata['backfilled_catalogue'] = $shareLink->title ?? $shareLink->token;
            $metadata['backfilled_at'] = now()->toIso8601String();
            
            // For catalog-type shares, store the selected product IDs from settings
            $settings = $shareLink->settings ?? [];
            $selectedIds = $settings['selected_product_ids'] ?? [];
            if (!empty($selectedIds)) {
                $metadata['product_ids'] = $selectedIds;
            }
            
            $log->metadata = $metadata;
            $log->save();
            $updated++;
            
            echo "  [UPDATED] Log #{$log->id} ({$log->event_type}) -> Catalogue: " . ($shareLink->title ?? $shareLink->token) . "\n";
            continue;
        }
    }
    
    // If no download_log match, try to find ANY share link for this user
    if ($userId) {
        $anyLink = SubscriberShareLink::withTrashed()
            ->where('user_id', $userId)
            ->where('created_at', '<=', $log->created_at)
            ->latest('created_at')
            ->first();
        
        if ($anyLink) {
            $log->subscriber_share_link_id = $anyLink->id;
            
            $metadata = $log->metadata ?? [];
            $metadata['backfilled_catalogue'] = $anyLink->title ?? $anyLink->token;
            $metadata['backfilled_at'] = now()->toIso8601String();
            $metadata['backfill_source'] = 'nearest_share_link';
            
            $settings = $anyLink->settings ?? [];
            $selectedIds = $settings['selected_product_ids'] ?? [];
            if (!empty($selectedIds)) {
                $metadata['product_ids'] = $selectedIds;
            }
            
            $log->metadata = $metadata;
            $log->save();
            $updated++;
            
            echo "  [UPDATED] Log #{$log->id} ({$log->event_type}) -> Nearest Catalogue: " . ($anyLink->title ?? $anyLink->token) . "\n";
            continue;
        }
    }
    
    $skipped++;
    echo "  [SKIPPED] Log #{$log->id} ({$log->event_type}) - No matching data found\n";
}

echo "\n=== Results ===\n";
echo "Updated: {$updated}\n";
echo "Skipped: {$skipped}\n";
echo "Done!\n";

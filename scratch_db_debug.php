<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EngagementLog;

$logs = EngagementLog::latest()->take(15)->get();

foreach ($logs as $log) {
    echo "ID: {$log->id} | Event: {$log->event_type} | Prod ID: {$log->subscriber_product_id} | Created: {$log->created_at} | Meta: " . json_encode($log->metadata) . "\n";
}

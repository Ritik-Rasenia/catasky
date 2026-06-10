<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$recent = \App\Models\EngagementLog::latest()->take(20)->get();
foreach ($recent as $r) {
    echo "ID: {$r->id} | Type: {$r->event_type} | Prod ID: {$r->subscriber_product_id} | Created: {$r->created_at} | Meta: " . json_encode($r->metadata) . "\n";
}

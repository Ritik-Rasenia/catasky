<?php
// Prevent unauthorized access
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1' && $_SERVER['SERVER_NAME'] !== 'catasky.com' && $_SERVER['SERVER_NAME'] !== 'localhost') {
    die('Unauthorized');
}

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EngagementLog;

$logs = EngagementLog::latest()->take(15)->get();

echo "<h3>Latest 15 Engagement Logs:</h3><pre>";
foreach ($logs as $log) {
    echo "ID: {$log->id} | Event: {$log->event_type} | Prod ID: {$log->subscriber_product_id} | Created: {$log->created_at} | Meta: " . json_encode($log->metadata) . "\n";
}
echo "</pre>";

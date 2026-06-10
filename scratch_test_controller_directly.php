<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\API\AnalyticsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

// Force sync queue driver so listeners execute synchronously
Config::set('queue.default', 'sync');

$controller = new AnalyticsApiController();

// Mock request
$request = Request::create('/api/analytics/engagement', 'POST', [
    'event_type' => 'whatsapp_click',
    'session_id' => 'test_session_' . time(),
    'user_id' => 2,
]);

try {
    echo "Calling logEngagement directly in PHP with sync queue...\n";
    $response = $controller->logEngagement($request);
    echo "Success! Response status: " . $response->getStatusCode() . "\n";
    echo "Response body: " . $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Caught exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

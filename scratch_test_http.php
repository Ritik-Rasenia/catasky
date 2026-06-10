<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$url = 'http://localhost/catasky/public/api/analytics/engagement';

echo "Testing URL: {$url}\n";
try {
    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ])->post($url, [
        'event_type' => 'whatsapp_click',
        'session_id' => 'test_session_' . time(),
        'user_id' => 2,
    ]);

    echo "Status: " . $response->status() . "\n";
    echo "Response: " . $response->body() . "\n\n";
} catch (\Exception $e) {
    echo "Error calling {$url}: " . $e->getMessage() . "\n\n";
}

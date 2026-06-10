<?php

use App\Events\Analytics\EngagementLogged;
use Illuminate\Support\Facades\Log;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Attempting to dispatch EngagementLogged event...\n";
    EngagementLogged::dispatch(
        null, // visitLogId
        null, // subscriberShareLinkId
        2,    // userId (valid subscriber ID)
        'whatsapp_click', // eventType
        null, // productId
        ['source' => 'test_script'] // metadata
    );
    echo "Event dispatched successfully!\n";
} catch (\Exception $e) {
    echo "Error caught: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

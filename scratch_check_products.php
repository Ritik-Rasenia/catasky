<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\SubscriberProduct;

$totalPublic = Product::withoutGlobalScope('tenant')->count();
$nullSub = Product::withoutGlobalScope('tenant')->whereNull('subscriber_id')->count();
$nonNullSub = Product::withoutGlobalScope('tenant')->whereNotNull('subscriber_id')->get();

echo "Total Products (public table withoutGlobalScope): {$totalPublic}\n";
echo "Products with subscriber_id IS NULL: {$nullSub}\n";
echo "Products with subscriber_id IS NOT NULL: " . count($nonNullSub) . "\n";
if (count($nonNullSub) > 0) {
    $counts = [];
    foreach ($nonNullSub as $p) {
        $counts[$p->subscriber_id] = ($counts[$p->subscriber_id] ?? 0) + 1;
    }
    foreach ($counts as $subId => $c) {
        echo "  - Subscriber ID {$subId}: {$c} products\n";
    }
}

$totalSubProducts = SubscriberProduct::count();
echo "Total SubscriberProducts: {$totalSubProducts}\n";
if ($totalSubProducts > 0) {
    $counts = [];
    foreach (SubscriberProduct::all() as $sp) {
        $counts[$sp->user_id] = ($counts[$sp->user_id] ?? 0) + 1;
    }
    foreach ($counts as $userId => $c) {
        echo "  - User ID {$userId}: {$c} products\n";
    }
}

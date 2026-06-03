<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SubscriberProduct;

echo "--- ALL PRODUCTS IN SUBSCRIBER_PRODUCTS TABLE ---\n";
$products = SubscriberProduct::all();
echo "Total products: " . $products->count() . "\n";
foreach ($products as $p) {
    echo "ID: {$p->id}, Name: {$p->name}, User ID: {$p->user_id}, Status: {$p->status}, Approval Status: {$p->approval_status}\n";
}

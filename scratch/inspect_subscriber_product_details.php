<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SubscriberProduct;

$products = SubscriberProduct::where('user_id', 3)->get();
foreach ($products as $p) {
    echo "ID: {$p->id}, Name: {$p->name}\n";
    echo "  user_id: {$p->user_id}\n";
    echo "  status: {$p->status}\n";
    echo "  approval_status: {$p->approval_status}\n";
    echo "  category_id: " . json_encode($p->category_id) . "\n";
    echo "  subcategory_id: " . json_encode($p->subcategory_id) . "\n";
    echo "  deleted_at: {$p->deleted_at}\n";
    echo "---------------------------\n";
}

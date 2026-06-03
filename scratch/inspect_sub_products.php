<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SubscriberProduct;

echo "--- SUBSCRIBER PRODUCTS ---\n";
foreach (SubscriberProduct::all() as $p) {
    echo "ID: {$p->id}, Name: {$p->name}, UserID: {$p->user_id}, Status: {$p->status}, Approval: {$p->approval_status}\n";
}

<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SubscriberProfile;

echo "--- ALL SUBSCRIBER PROFILES ---\n";
foreach (SubscriberProfile::all() as $p) {
    echo "ID: {$p->id}, User ID: {$p->user_id}, Name: {$p->company_name}, Slug: {$p->company_slug}, Status: {$p->status}, Store Status: {$p->store_status}\n";
}

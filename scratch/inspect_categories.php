<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Category;
use App\Models\Subcategory;

echo "--- ALL CATEGORIES ---\n";
foreach (Category::withoutGlobalScope('tenant')->get() as $c) {
    echo "ID: {$c->id}, Name: {$c->name}, Status: {$c->status}, Tenant: " . ($c->subscriber_id ?: 'NULL') . "\n";
}

echo "\n--- ALL SUBCATEGORIES ---\n";
foreach (Subcategory::withoutGlobalScope('tenant')->get() as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, Category ID: {$s->category_id}, Status: {$s->status}, Tenant: " . ($s->subscriber_id ?: 'NULL') . "\n";
}

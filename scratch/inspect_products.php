<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Subcategory;

echo "--- PRODUCTS (Bypassing Scope) ---\n";
foreach (Product::withoutGlobalScope('tenant')->get() as $p) {
    echo "ID: {$p->id}, Name: {$p->name}, SubID: " . ($p->subscriber_id ?: 'NULL') . "\n";
}

echo "\n--- BRANDS (Bypassing Scope) ---\n";
foreach (Brand::withoutGlobalScope('tenant')->get() as $b) {
    echo "ID: {$b->id}, Name: {$b->name}, SubID: " . ($b->subscriber_id ?: 'NULL') . "\n";
}

echo "\n--- SUBCATEGORIES (Bypassing Scope) ---\n";
foreach (Subcategory::withoutGlobalScope('tenant')->get() as $s) {
    echo "ID: {$s->id}, Name: {$s->name}, SubID: " . ($s->subscriber_id ?: 'NULL') . "\n";
}

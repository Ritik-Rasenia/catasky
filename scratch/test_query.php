<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubscriberProduct;

$product = SubscriberProduct::first();
if ($product) {
    echo "Product: " . $product->name . "\n";
    echo "Category ID raw: " . json_encode($product->getRawOriginal('category_id')) . "\n";
    echo "Subcategory ID raw: " . json_encode($product->getRawOriginal('subcategory_id')) . "\n";
    echo "Brand ID raw: " . json_encode($product->getRawOriginal('brand_id')) . "\n";
    echo "Category ID cast: " . json_encode($product->category_id) . "\n";
    echo "Subcategory ID cast: " . json_encode($product->subcategory_id) . "\n";
    echo "Brand ID cast: " . json_encode($product->brand_id) . "\n";
} else {
    echo "No subscriber products found.\n";
}

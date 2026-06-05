<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Exports\ProductsExport;
use App\Models\Product;

$export = new ProductsExport();
echo "HEADINGS:\n";
print_r($export->headings());

$product = Product::withoutGlobalScope('tenant')->first();
if ($product) {
    echo "\nMAPPED ROW FOR PRODUCT '{$product->name}':\n";
    print_r($export->map($product));
} else {
    echo "\nNO PRODUCT FOUND IN DATABASE.\n";
}

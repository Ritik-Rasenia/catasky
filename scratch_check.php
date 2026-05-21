<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$products = Product::all();
$urlCount = 0;
foreach ($products as $p) {
    if (filter_var($p->thumbnail, FILTER_VALIDATE_URL)) {
        echo "Product ID {$p->id} has URL thumbnail: {$p->thumbnail}\n";
        $urlCount++;
    }
    foreach ($p->images as $img) {
        if (filter_var($img->image, FILTER_VALIDATE_URL)) {
            echo "Product ID {$p->id} has URL gallery image: {$img->image}\n";
            $urlCount++;
        }
    }
}

echo "Total products with URL images: $urlCount\n";

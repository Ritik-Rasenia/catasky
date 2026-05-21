<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$products = App\Models\Product::with('images')->take(10)->get();
echo "Total Products Checked: " . $products->count() . "\n";
foreach ($products as $p) {
    echo "ID: {$p->id} | Name: {$p->name}\n";
    echo "  - Thumbnail: {$p->thumbnail}\n";
    $thumbPath = public_path('uploads/products/' . $p->thumbnail);
    if (file_exists($thumbPath) && is_file($thumbPath)) {
        echo "    * File exists, size: " . filesize($thumbPath) . " bytes\n";
    } else {
        echo "    * File does NOT exist at {$thumbPath}\n";
    }
    
    echo "  - Gallery count: " . $p->images->count() . "\n";
    foreach ($p->images as $img) {
        echo "    * Gallery Image: {$img->image}\n";
        $galPath = public_path('uploads/products/gallery/' . $img->image);
        if (file_exists($galPath) && is_file($galPath)) {
            echo "      - File exists, size: " . filesize($galPath) . " bytes\n";
        } else {
            echo "      - File does NOT exist at {$galPath}\n";
        }
    }
}

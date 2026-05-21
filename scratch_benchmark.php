<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Http\Controllers\FrontendController;

$controller = new FrontendController();

echo "--- Benchmarking All Active Products ---\n";
$products = Product::where('status', 1)->get();
echo "Total active products: " . $products->count() . "\n";

foreach ($products as $p) {
    $start = microtime(true);
    try {
        $response = $controller->apiProductDetails($p->id);
        $elapsed = (microtime(true) - $start) * 1000;
        if ($elapsed > 150) {
            echo "Product ID {$p->id} ({$p->name}): " . round($elapsed, 2) . " ms (SLOW!)\n";
        }
    } catch (\Exception $e) {
        echo "Product ID {$p->id} failed: " . $e->getMessage() . "\n";
    }
}
echo "Benchmark completed.\n";

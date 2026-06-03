<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Illuminate\Support\Facades\Schema::getColumnListing('products');
echo "Products Table Columns:\n";
print_r($columns);

$subColumns = Illuminate\Support\Facades\Schema::getColumnListing('subscriber_products');
echo "\nSubscriber Products Table Columns:\n";
print_r($subColumns);

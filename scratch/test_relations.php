<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubscriberProduct;
use App\Models\SubscriberShareLink;

echo "Checking SubscriberProduct relation...\n";
try {
    $product = SubscriberProduct::with(['user', 'user.subscriberProfile'])->first();
    if ($product) {
        echo "Successfully retrieved product! ID: " . $product->id . "\n";
        if ($product->user) {
            echo "Successfully loaded user! ID: " . $product->user->id . ", Name: " . $product->user->name . "\n";
            if ($product->user->subscriberProfile) {
                echo "Successfully loaded user's subscriber profile!\n";
            } else {
                echo "User has no subscriber profile (null), but relationship is loaded successfully!\n";
            }
        } else {
            echo "Product has no associated user (null).\n";
        }
    } else {
        echo "No products found in database.\n";
    }
} catch (\Exception $e) {
    echo "ERROR loading product relation: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\nChecking SubscriberShareLink relation...\n";
try {
    $link = SubscriberShareLink::with(['user', 'user.subscriberProfile'])->first();
    if ($link) {
        echo "Successfully retrieved share link! ID: " . $link->id . "\n";
        if ($link->user) {
            echo "Successfully loaded user! ID: " . $link->user->id . ", Name: " . $link->user->name . "\n";
        } else {
            echo "Share link has no associated user (null).\n";
        }
    } else {
        echo "No share links found in database.\n";
    }
} catch (\Exception $e) {
    echo "ERROR loading share link relation: " . $e->getMessage() . "\n";
}

echo "\nTest execution finished successfully.\n";

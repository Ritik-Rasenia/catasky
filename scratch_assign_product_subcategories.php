<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Subcategory;

$products = Product::all();
echo "Updating product subcategory associations...\n";

foreach ($products as $p) {
    $subId = null;
    $name = strtolower($p->name);
    
    if ($p->category_id == 1) { // Apparel
        if (strpos($name, 'polo') !== false) {
            $sub = Subcategory::where('category_id', 1)->where('name', 'Polos')->first();
        } elseif (strpos($name, 'sweater') !== false || strpos($name, 'hoodie') !== false) {
            $sub = Subcategory::where('category_id', 1)->where('name', 'Sweaters')->first();
        } elseif (strpos($name, 'cap') !== false) {
            $sub = Subcategory::where('category_id', 1)->where('name', 'Caps')->first();
        } else {
            $sub = Subcategory::where('category_id', 1)->where('name', 'T-Shirts')->first();
        }
        if (isset($sub)) $subId = $sub->id;
    } elseif ($p->category_id == 2) { // Tech & Gadgets
        if (strpos($name, 'quietcomfort') !== false || strpos($name, 'head') !== false) {
            $sub = Subcategory::where('category_id', 2)->where('name', 'Headphones')->first();
        } elseif (strpos($name, 'mouse') !== false || strpos($name, 'master') !== false) {
            $sub = Subcategory::where('category_id', 2)->where('name', 'Mice')->first();
        } elseif (strpos($name, 'keyboard') !== false) {
            $sub = Subcategory::where('category_id', 2)->where('name', 'Keyboards')->first();
        } else {
            $sub = Subcategory::where('category_id', 2)->where('name', 'Chargers')->first();
        }
        if (isset($sub)) $subId = $sub->id;
    } elseif ($p->category_id == 3) { // Drinkware
        if (strpos($name, 'tumbler') !== false || strpos($name, 'quencher') !== false) {
            $sub = Subcategory::where('category_id', 3)->where('name', 'Tumblers')->first();
        } elseif (strpos($name, 'mug') !== false) {
            $sub = Subcategory::where('category_id', 3)->where('name', 'Mugs')->first();
        } else {
            $sub = Subcategory::where('category_id', 3)->where('name', 'Bottles')->first();
        }
        if (isset($sub)) $subId = $sub->id;
    }
    
    // Fallback if not assigned yet
    if (!$subId && $p->category_id) {
        $sub = Subcategory::where('category_id', $p->category_id)->first();
        if ($sub) $subId = $sub->id;
    }
    
    if ($subId) {
        $p->subcategory_id = $subId;
        $p->save();
        echo "  - Updated: {$p->name} -> Subcategory ID: {$subId}\n";
    }
}

echo "Subcategory assignment completed successfully!\n";

<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProductVariant;
use Illuminate\Support\Str;

function seedForUser(int $userId) {
    echo "Seeding products for User ID: $userId...\n";
    
    // Clear old products to avoid duplicates
    $oldProductsCount = SubscriberProduct::where('user_id', $userId)->delete();
    echo "Deleted $oldProductsCount old subscriber products.\n";

    // Helper functions to resolve categories, subcategories and brands
    $resolveBrand = function(string $name) use ($userId) {
        $key = trim($name);
        if ($key === '') return null;
        $brand = Brand::withoutGlobalScope('tenant')
            ->where('subscriber_id', $userId)
            ->whereRaw('LOWER(name) = ?', [strtolower($key)])
            ->first();
        if (!$brand) {
            $brand = Brand::create([
                'subscriber_id' => $userId,
                'name' => $key,
                'slug' => Str::slug($key),
                'status' => 1
            ]);
        }
        return $brand->id;
    };

    $resolveCategory = function(string $name) use ($userId) {
        $key = trim($name);
        if ($key === '') return null;
        $cat = Category::withoutGlobalScope('tenant')
            ->where('subscriber_id', $userId)
            ->whereRaw('LOWER(name) = ?', [strtolower($key)])
            ->first();
        if (!$cat) {
            $cat = Category::create([
                'subscriber_id' => $userId,
                'name' => $key,
                'slug' => Str::slug($key),
                'status' => 1
            ]);
        }
        return $cat;
    };

    $resolveSubcategory = function(int $catId, string $name) use ($userId) {
        $key = trim($name);
        if ($key === '') return null;
        $sub = Subcategory::withoutGlobalScope('tenant')
            ->where('subscriber_id', $userId)
            ->where('category_id', $catId)
            ->whereRaw('LOWER(name) = ?', [strtolower($key)])
            ->first();
        if (!$sub) {
            $sub = Subcategory::create([
                'subscriber_id' => $userId,
                'category_id' => $catId,
                'name' => $key,
                'slug' => Str::slug($key),
                'status' => 1
            ]);
        }
        return $sub->id;
    };

    $samples = [
        [
            'name' => 'Elite Leather Watch',
            'sku' => 'ELITE-WATCH-01',
            'slug' => 'elite-leather-watch',
            'brands' => 'Titan',
            'categories' => 'Fashion Accessories',
            'subcategories' => 'Watches',
            'mrp' => 5000.00,
            'offer_price' => 4500.00,
            'moq' => 2,
            'stock' => 150,
            'stock_status' => 'in_stock',
            'short_description' => 'Classic leather strap analogue watch.',
            'full_description' => 'A premium classic watch featuring a genuine leather strap, quartz movement, and water resistance up to 50 meters.',
            'status' => 'active',
            'featured' => true,
            'thumbnail' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80',
            'tags' => ['watch', 'leather', 'premium', 'accessories'],
            'meta_title' => 'Elite Leather Watch - Premium Accessories',
            'meta_description' => 'Shop elite leather watches online at the best prices.'
        ],
        [
            'name' => 'Ergonomic Office Chair',
            'sku' => 'ERG-CHAIR-02',
            'slug' => 'ergonomic-office-chair',
            'brands' => 'Featherlite, Steelcase',
            'categories' => 'Furniture',
            'subcategories' => 'Chairs',
            'mrp' => 12000.00,
            'offer_price' => 9999.00,
            'moq' => 5,
            'stock' => 80,
            'stock_status' => 'in_stock',
            'short_description' => 'Comfortable ergonomic office chair.',
            'full_description' => 'High-back ergonomic office chair with adjustable lumbar support, armrests, and synchro-tilt mechanism.',
            'status' => 'active',
            'featured' => false,
            'thumbnail' => 'https://images.unsplash.com/photo-1505797149-43b0069ec26b?auto=format&fit=crop&w=600&q=80',
            'tags' => ['chair', 'office', 'ergonomic', 'furniture'],
            'meta_title' => 'Ergonomic Office Chair - Dual Brand',
            'meta_description' => 'Premium ergonomic chairs from top brands like Featherlite and Steelcase.'
        ],
        [
            'name' => 'Noise Cancelling Headphones',
            'sku' => 'ANC-HEAD-03',
            'slug' => 'noise-cancelling-headphones',
            'brands' => 'Sony, Bose',
            'categories' => 'Electronics, Audio Devices',
            'subcategories' => 'Headphones',
            'mrp' => 29999.00,
            'offer_price' => 24999.00,
            'moq' => 1,
            'stock' => 120,
            'stock_status' => 'in_stock',
            'short_description' => 'Wireless ANC over-ear headphones.',
            'full_description' => 'Industry-leading noise cancelling wireless headphones with 30-hour battery life and quick charging.',
            'status' => 'active',
            'featured' => true,
            'thumbnail' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
            'tags' => ['headphones', 'noise cancelling', 'electronics', 'audio'],
            'meta_title' => 'Noise Cancelling Headphones - Electronics',
            'meta_description' => 'Discover top noise cancelling headphones from Sony and Bose.'
        ],
        [
            'name' => 'Professional Sports Duffel Bag',
            'sku' => 'SPORT-DUF-04',
            'slug' => 'professional-sports-duffel-bag',
            'brands' => 'Nike, Adidas',
            'categories' => 'Sports Equipment, Travel Gear',
            'subcategories' => 'Gym Bags, Travel Duffle Bags',
            'mrp' => 3500.00,
            'offer_price' => 2900.00,
            'moq' => 10,
            'stock' => 250,
            'stock_status' => 'in_stock',
            'short_description' => 'Durable water-resistant sports duffel.',
            'full_description' => 'Large capacity gym and travel bag with dedicated shoe compartment and wet pocket.',
            'status' => 'active',
            'featured' => true,
            'thumbnail' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80',
            'tags' => ['duffel', 'gym bag', 'travel bag', 'nike', 'adidas'],
            'meta_title' => 'Professional Sports Duffel Bag',
            'meta_description' => 'High-grade sports and travel duffel bags from Nike and Adidas.'
        ],
        [
            'name' => 'Smart Fitness Tracker',
            'sku' => 'FIT-TRACK-05',
            'slug' => 'smart-fitness-tracker',
            'brands' => 'Fitbit',
            'categories' => 'Electronics',
            'subcategories' => 'Wearables',
            'mrp' => null,
            'offer_price' => 4999.00,
            'moq' => 5,
            'stock' => 300,
            'stock_status' => 'in_stock',
            'short_description' => 'Heart rate and sleep tracking smart band.',
            'full_description' => 'Waterproof fitness tracker with continuous heart rate monitoring, sleep analysis, and 7-day battery life.',
            'status' => 'active',
            'featured' => false,
            'thumbnail' => 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?auto=format&fit=crop&w=600&q=80',
            'tags' => ['fitness', 'tracker', 'band', 'wearable'],
            'meta_title' => 'Smart Fitness Tracker',
            'meta_description' => 'Stay active with the latest smart fitness tracker.'
        ],
        [
            'name' => 'Gourmet Coffee Blend',
            'sku' => 'COFFEE-BLEND-06',
            'slug' => 'gourmet-coffee-blend',
            'brands' => 'Blue Tokai',
            'categories' => 'Beverages',
            'subcategories' => 'Coffee',
            'mrp' => 650.00,
            'offer_price' => null,
            'moq' => 20,
            'stock' => 500,
            'stock_status' => 'in_stock',
            'short_description' => 'Medium roast 100% Arabica ground coffee.',
            'full_description' => 'Freshly roasted single-origin Arabica coffee beans with chocolate and caramel tasting notes.',
            'status' => 'active',
            'featured' => true,
            'thumbnail' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=600&q=80',
            'tags' => ['coffee', 'arabica', 'beverage', 'fresh roast'],
            'meta_title' => 'Gourmet Coffee Blend - Blue Tokai',
            'meta_description' => 'Experience the finest medium roast Arabica coffee beans.'
        ],
        [
            'name' => 'Stainless Steel Water Bottle',
            'sku' => 'STEEL-BOTTLE-07',
            'slug' => 'stainless-steel-water-bottle',
            'brands' => 'Milton',
            'categories' => 'Kitchenware',
            'subcategories' => 'Bottles',
            'mrp' => 999.00,
            'offer_price' => 850.00,
            'moq' => 50,
            'stock' => 1000,
            'stock_status' => 'in_stock',
            'short_description' => 'Double-walled vacuum insulated bottle.',
            'full_description' => '', // Description blank example
            'status' => 'active',
            'featured' => false,
            'thumbnail' => 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=600&q=80',
            'tags' => ['bottle', 'stainless steel', 'kitchenware'],
            'meta_title' => 'Stainless Steel Water Bottle',
            'meta_description' => 'Keep your drinks hot or cold for 24 hours.'
        ],
        [
            'name' => 'Minimalist Wireless Mouse',
            'sku' => 'WIRELESS-MOUSE-08',
            'slug' => 'minimalist-wireless-mouse',
            'brands' => 'Logitech',
            'categories' => 'Electronics',
            'subcategories' => 'Computer Accessories',
            'mrp' => 1299.00,
            'offer_price' => 999.00,
            'moq' => 10,
            'stock' => 450,
            'stock_status' => 'in_stock',
            'short_description' => 'Ultra-quiet slim wireless optical mouse.',
            'full_description' => 'Sleek and compact wireless mouse with silent clicking, high precision tracking, and Bluetooth/USB receiver connectivity.',
            'status' => 'active',
            'featured' => false,
            'thumbnail' => null, // Image field blank example
            'tags' => ['mouse', 'wireless', 'computer accessories', 'logitech'],
            'meta_title' => 'Minimalist Wireless Mouse',
            'meta_description' => 'Silent wireless mouse with comfortable design.'
        ],
        [
            'name' => 'Organic Cotton T-Shirt',
            'sku' => 'COTTON-TEE-09',
            'slug' => 'organic-cotton-t-shirt',
            'brands' => 'Zara',
            'categories' => 'Apparel',
            'subcategories' => 'T-Shirts',
            'mrp' => 1499.00,
            'offer_price' => 1199.00,
            'moq' => 15,
            'stock' => 200,
            'stock_status' => 'in_stock',
            'short_description' => 'Eco-friendly organic cotton tee.',
            'full_description' => 'Crafted from 100% certified organic cotton. Features a relaxed fit, crew neck, and breathable fabric ideal for daily wear.',
            'status' => 'active',
            'featured' => false,
            'thumbnail' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=600&q=80',
            'tags' => null, // Tags/Notes blank example
            'meta_title' => 'Organic Cotton T-Shirt - Zara',
            'meta_description' => 'Eco-friendly premium organic cotton tees.'
        ],
        [
            'name' => 'Portable Power Bank',
            'sku' => 'PORT-POWER-10',
            'slug' => '', // Slug blank (will auto generate)
            'brands' => 'Xiaomi',
            'categories' => 'Electronics',
            'subcategories' => '', // Subcategory blank
            'mrp' => 1999.00,
            'offer_price' => null, // Offer price blank
            'moq' => 5,
            'stock' => 0,
            'stock_status' => 'out_of_stock',
            'short_description' => '', // Short description blank
            'full_description' => '10000mAh high capacity fast charging power bank with dual USB outputs.',
            'status' => 'active',
            'featured' => true,
            'thumbnail' => null, // Image blank
            'tags' => null, // Tags blank
            'meta_title' => null, // Meta title blank
            'meta_description' => null // Meta desc blank
        ]
    ];

    foreach ($samples as $sample) {
        // Resolve Brand
        $bNames = array_filter(array_map('trim', explode(',', $sample['brands'])));
        $bIds = [];
        foreach ($bNames as $bN) {
            $bId = $resolveBrand($bN);
            if ($bId) $bIds[] = (string)$bId;
        }

        // Resolve Categories
        $cNames = array_filter(array_map('trim', explode(',', $sample['categories'])));
        $cIds = [];
        $firstCatId = null;
        foreach ($cNames as $cN) {
            $cat = $resolveCategory($cN);
            if ($cat) {
                $cIds[] = (string)$cat->id;
                if ($firstCatId === null) $firstCatId = $cat->id;
            }
        }

        // Resolve Subcategories
        $sIds = [];
        if ($firstCatId && $sample['subcategories'] !== '') {
            $sNames = array_filter(array_map('trim', explode(',', $sample['subcategories'])));
            foreach ($sNames as $sN) {
                $sId = $resolveSubcategory($firstCatId, $sN);
                if ($sId) $sIds[] = (string)$sId;
            }
        }

        $price = $sample['offer_price'] ?: ($sample['mrp'] ?: 0.00);

        $prod = SubscriberProduct::create([
            'user_id' => $userId,
            'brand_id' => $bIds,
            'category_id' => $cIds,
            'subcategory_id' => $sIds,
            'name' => $sample['name'],
            'sku' => $sample['sku'],
            'slug' => $sample['slug'] ?: (Str::slug($sample['name']) . '-' . Str::lower(Str::random(6))),
            'mrp' => $sample['mrp'],
            'offer_price' => $sample['offer_price'],
            'price' => $price,
            'moq' => $sample['moq'],
            'stock' => $sample['stock'],
            'stock_status' => $sample['stock_status'],
            'thumbnail' => $sample['thumbnail'],
            'short_description' => $sample['short_description'],
            'full_description' => $sample['full_description'],
            'tags' => $sample['tags'],
            'featured' => $sample['featured'],
            'status' => $sample['status'],
            'meta_title' => $sample['meta_title'],
            'meta_description' => $sample['meta_description'],
            'approval_status' => 'approved',
        ]);

        // Default variant
        SubscriberProductVariant::create([
            'subscriber_product_id' => $prod->id,
            'variant_sku' => $prod->sku,
            'price' => $prod->price,
            'stock' => $prod->stock,
            'status' => true,
        ]);
        
        echo "  Created product: {$prod->name} (ID: {$prod->id})\n";
    }
}

seedForUser(3); // Seeding for User ID 3 (Aakansha pro / Demo user)
seedForUser(2); // Seeding for User ID 2 (Ritik)

echo "\nDone seeding dummy products!\n";

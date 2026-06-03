<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\FrontendController;
use Illuminate\Http\Request;

$request = Request::create('/demo', 'GET');
$controller = new FrontendController();

// Resolve demo user id dynamically
$demoUser = \App\Models\User::where('id', 3)->first();
$demoUserId = $demoUser ? $demoUser->id : 3;
$profile = \App\Models\SubscriberProfile::where('user_id', $demoUserId)->first();
$companySlug = $profile ? $profile->company_slug : 'aakansha-pro';

echo "Profile Info:\n";
echo "  company_name: {$profile->company_name}\n";
echo "  company_slug: {$profile->company_slug}\n";
echo "  user_id: {$profile->user_id}\n";

try {
    $res = $controller->demoCatalogue($request);
    
    // It's a View object, so we can render it
    $html = $res->render();
    echo "Rendered HTML successfully! Length: " . strlen($html) . "\n";
    
    // Check if the products are present in the HTML
    $products = [
        'Elite Leather Watch',
        'Ergonomic Office Chair',
        'Noise Cancelling Headphones',
        'Professional Sports Duffel Bag',
        'Smart Fitness Tracker',
        'Gourmet Coffee Blend',
        'Stainless Steel Water Bottle',
        'Minimalist Wireless Mouse',
        'Organic Cotton T-Shirt',
        'Portable Power Bank'
    ];
    
    echo "Checking product names in HTML:\n";
    foreach ($products as $name) {
        $found = stripos($html, $name) !== false ? "FOUND" : "NOT FOUND";
        echo "  - $name: $found\n";
    }
    
    if (stripos($html, 'No products match selection') !== false) {
        echo "WARNING: HTML contains 'No products match selection'\n";
    } else {
        echo "SUCCESS: 'No products match selection' is NOT in HTML\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

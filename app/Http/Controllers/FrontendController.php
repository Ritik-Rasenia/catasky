<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    /**
     * Display the landing catalogue page.
     */
    public function index(Request $request)
    {
        if ($subscriberId = $request->attributes->get('custom_domain_subscriber_id')) {
            $slug = $request->attributes->get('custom_domain_slug');
            return $this->storeCatalog($slug, $request);
        }

        $categories = Category::where('status', 1)->withCount('products')->get();
        $featuredProducts = Product::where('status', 1)
            ->where('featured', 1)
            ->with([])
            ->latest()
            ->take(8)
            ->get();
            
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
            
        return view('welcome', compact('categories', 'featuredProducts', 'plans'));
    }

    /**
     * Display the public demo catalogue page without login.
     */
    public function demoCatalogue(Request $request)
    {
        return $this->catalogue($request);
    }

    /**
     * Display the full catalogue.
     */
    public function catalogue(Request $request)
    {
        $category = (object)['name' => 'All Products', 'id' => 0];

        $query = Product::where('status', 1)->with([]);

        // Filter by selected product IDs (for catalogue sharing)
        $productIds = $request->input('products');
        if ($productIds) {
            $ids = array_filter(explode(',', $productIds));
            $query->whereIn('id', $ids);
            $category->name = 'Selected Catalogue Products';
        }

        // Filter by subcategory
        if ($request->filled('subcategory')) {
            $sub = Subcategory::where('slug', $request->input('subcategory'))->first();
            if ($sub) {
                $query->where(function($q) use ($sub) {
                    $q->whereJsonContains('subcategory_id', $sub->id)
                      ->orWhereJsonContains('subcategory_id', (string)$sub->id);
                });
            }
        }

        // Filter by price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Sorting
        $sort = $request->input('sort', 'default');
        match ($sort) {
            'name_asc'   => $query->orderBy('name', 'asc'),
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default      => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('category-products', compact('category', 'products'));
    }

    /**
     * Display brands.
     */
    public function brands()
    {
        $brands = Brand::where('status', 1)->get();
        return view('brands', compact('brands'));
    }

    /**
     * Display categories.
     */
    public function categories()
    {
        $categories = Category::where('status', 1)->withCount('products')->get();
        return view('categories', compact('categories'));
    }

    /**
     * Display subcategories.
     */
    public function subcategories()
    {
        $subcategories = Subcategory::with(['category', 'products'])->get();
        return view('subcategories', compact('subcategories'));
    }



    /**
     * Display products for a specific category.
     */
    public function categoryProducts($slug, Request $request)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $query = Product::where(function($q) use ($category) {
                $q->whereJsonContains('category_id', $category->id)
                  ->orWhereJsonContains('category_id', (string)$category->id);
            })
            ->where('status', 1)
            ->with([]);

        // Filter by subcategory
        if ($request->filled('subcategory')) {
            $sub = Subcategory::where('slug', $request->input('subcategory'))->first();
            if ($sub) {
                $query->where(function($q) use ($sub) {
                    $q->whereJsonContains('subcategory_id', $sub->id)
                      ->orWhereJsonContains('subcategory_id', (string)$sub->id);
                });
            }
        }

        // Filter by price
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Sorting
        $sort = $request->input('sort', 'default');
        match ($sort) {
            'name_asc'   => $query->orderBy('name', 'asc'),
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default      => $query->latest(),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('category-products', compact('category', 'products'));
    }

    public function productDetails($slug)
    {
        $request = request();
        $isSubscriberStore = false;
        $profile = null;
        $settings = \App\Models\Setting::first();

        if ($subscriberId = $request->attributes->get('custom_domain_subscriber_id')) {
            $isSubscriberStore = true;
            $profile = \App\Models\SubscriberProfile::where('user_id', $subscriberId)->first();
            if (!$profile) {
                abort(404, 'Subscriber profile not found.');
            }
            if ($profile->status !== 'approved' && $profile->status !== 'active') {
                abort(403, 'This storefront account is not active.');
            }
            if ($profile->store_status !== 'live') {
                abort(403, 'This storefront is pending review.');
            }

            $product = \App\Models\SubscriberProduct::where('slug', $slug)
                ->where('user_id', $subscriberId)
                ->with(['images'])
                ->first();

            if (!$product) {
                abort(404, 'Product not found.');
            }
        } else {
            // 1. Resolve product from either Product (admin) or SubscriberProduct table
            $product = Product::where('slug', $slug)
                ->with(['images'])
                ->first();

            if (!$product) {
                // Check if it exists in SubscriberProduct
                $product = \App\Models\SubscriberProduct::where('slug', $slug)
                    ->with(['images'])
                    ->first();

                if (!$product) {
                    abort(404, 'Product not found.');
                }

                // It's a subscriber product!
                $isSubscriberStore = true;
                $profile = \App\Models\SubscriberProfile::where('user_id', $product->user_id)->first();
                if ($profile) {
                    // Ensure double-approval status is checked
                    if ($profile->status !== 'approved' && $profile->status !== 'active') {
                        abort(403, 'This storefront account is not active.');
                    }
                    if ($profile->store_status !== 'live') {
                        abort(403, 'This storefront is pending review.');
                    }
                    // Inject active subscriber ID so layout query scoping works correctly
                    $request->attributes->set('custom_domain_subscriber_id', $product->user_id);
                } else {
                    abort(404, 'Subscriber profile not found.');
                }
            } else {
                // It's an admin product. Check if the request explicitly asks for subscriber context
                if ($request->has('company_slug')) {
                    $profile = \App\Models\SubscriberProfile::where('company_slug', $request->input('company_slug'))->first();
                    if ($profile) {
                        $isSubscriberStore = true;
                        $request->attributes->set('custom_domain_subscriber_id', $profile->user_id);
                    }
                }
            }
        }

        // 2. Fetch related products from the same table (Product or SubscriberProduct)
        if ($isSubscriberStore) {
            $relatedProducts = \App\Models\SubscriberProduct::where('user_id', $product->user_id)
                ->where('status', 'active')
                ->where('approval_status', 'approved')
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->take(4)
                ->get();
        } else {
            $relatedProducts = Product::where('status', 1)
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->take(4)
                ->get();
        }

        // 3. For subscriber store view compatibility, prepare logoBase64
        $logoBase64 = '';
        if ($profile && $profile->logo) {
            $logoPath = public_path('uploads/subscriber-logos/' . $profile->logo);
            if (file_exists($logoPath) && is_file($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = @file_get_contents($logoPath);
                if ($data) {
                    $logoBase64 = 'data:image/' . ($type === 'svg' ? 'svg+xml' : $type) . ';base64,' . base64_encode($data);
                }
            }
        }

        $companyName = $profile ? $profile->company_name : ($settings->company_name ?? 'Catasky');

        return view('product-details', compact(
            'product', 'relatedProducts', 'isSubscriberStore', 'profile', 'settings', 'logoBase64', 'companyName'
        ));
    }

    /**
     * Search products.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $subscriberId = $request->attributes->get('custom_domain_subscriber_id');
        
        if (!$subscriberId && $request->filled('company_slug')) {
            $profile = \App\Models\SubscriberProfile::where('company_slug', $request->input('company_slug'))->first();
            if ($profile) {
                $subscriberId = $profile->user_id;
                $request->attributes->set('custom_domain_subscriber_id', $subscriberId);
            }
        }

        if ($subscriberId) {
            $products = \App\Models\SubscriberProduct::where('user_id', $subscriberId)
                ->where('status', 'active')
                ->where('approval_status', 'approved')
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%$query%")
                      ->orWhere('sku', 'like', "%$query%")
                      ->orWhere('short_description', 'like', "%$query%")
                      ->orWhere('full_description', 'like', "%$query%");
                })
                ->paginate(12);
        } else {
            $products = Product::where('name', 'like', "%$query%")
                ->orWhere('short_description', 'like', "%$query%")
                ->where('status', 1)
                ->paginate(12);
        }

        return view('search-results', compact('products', 'query'));
    }

    /**
     * Placeholder for Part List.
     */
    public function partList()
    {
        return view('part-list');
    }

    /**
     * Contact Us page.
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Handle Enquiry Submission.
     */
    public function enquirySubmit(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|integer',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string'
        ]);

        $productId = $request->product_id;
        $subscriberProductId = null;
        $brandId = $request->brand_id;

        if ($productId) {
            $isSubscriberProduct = false;
            if ($request->input('is_subscriber_product') == '1') {
                $isSubscriberProduct = true;
            } else {
                $existsInProducts = \App\Models\Product::where('id', $productId)->exists();
                $existsInSubProducts = \App\Models\SubscriberProduct::where('id', $productId)->exists();
                if ($existsInSubProducts && !$existsInProducts) {
                    $isSubscriberProduct = true;
                }
            }

            if ($isSubscriberProduct) {
                $subscriberProductId = $productId;
                $productId = null;
                if (!$brandId) {
                    $subProd = \App\Models\SubscriberProduct::find($subscriberProductId);
                    $brandId = $subProd ? (is_array($subProd->brand_id) ? (collect($subProd->brand_id)->first() ?? null) : $subProd->brand_id) : null;
                }
            } else {
                if (!$brandId) {
                    $prod = \App\Models\Product::find($productId);
                    $brandId = $prod ? (is_array($prod->brand_id) ? (collect($prod->brand_id)->first() ?? null) : $prod->brand_id) : null;
                }
            }
        }

        \App\Models\Enquiry::create([
            'product_id' => $productId,
            'subscriber_product_id' => $subscriberProductId,
            'brand_id' => $brandId,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'subject' => $request->subject ?: 'Corporate Product Inquiry',
            'message' => $request->message
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your B2B inquiry has been logged. Our corporate dispatch office will contact you shortly.'
            ]);
        }

        return back()->with('success', 'Thank you! Your B2B inquiry has been logged. Our corporate dispatch office will contact you shortly.');
    }

    /**
     * Handle Newsletter Submission.
     */
    public function newsletterSubmit(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255'
        ]);

        $exists = \App\Models\Newsletter::where('email', $request->email)->first();
        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already subscribed to our newsletter updates.'
                ]);
            }
            return back()->with('info', 'You are already subscribed to our newsletter updates.');
        }

        \App\Models\Newsletter::create([
            'email' => $request->email,
            'status' => 1
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subscribed to our newsletter updates successfully!'
            ]);
        }

        return back()->with('success', 'Subscribed to our newsletter updates successfully!');
    }

    /**
     * Thank You page.
     */
    public function thankYou()
    {
        return view('thank-you');
    }

    /**
     * Future Products page.
     */
    public function futureProducts()
    {
        $products = Product::where('is_future', 1)->paginate(12);
        return view('future-products', compact('products'));
    }



    /**
     * API for subcategories.
     */
    public function getApiSubcategories($category_id)
    {
        return Subcategory::where('category_id', $category_id)->get();
    }



    /**
     * API for featured products.
     */
    public function getApiFeaturedProducts()
    {
        return Product::where('featured', 1)->where('status', 1)->get();
    }

    /**
     * API for multiple products details (bulk request).
     */
    public function apiProductsDetails(Request $request)
    {
        $idsStr = $request->input('ids', '');
        $ids = array_filter(explode(',', $idsStr));
        
        if (empty($ids)) {
            return response()->json([
                'success' => true,
                'products' => []
            ]);
        }

        $results = [];
        $subscriberId = $request->attributes->get('custom_domain_subscriber_id');

        if ($subscriberId) {
            $subProducts = \App\Models\SubscriberProduct::with(['images'])
                ->where('user_id', $subscriberId)
                ->whereIn('id', $ids)
                ->get();

            foreach ($subProducts as $product) {
                $thumbnail_url = $product->thumbnail_url;
                $gallery_urls = $product->images->map(function($img) {
                    $image_url = $img->image;
                    if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
                        return asset('uploads/subscriber-products/gallery/' . $img->image);
                    }
                    return $image_url;
                });

                $results[$product->id] = [
                    'success' => true,
                    'product' => $product,
                    'thumbnail_url' => $thumbnail_url,
                    'gallery_urls' => $gallery_urls
                ];
            }
        } else {
            $isSubscriber = $request->input('is_subscriber') == 1;
            $companySlug = $request->input('company_slug');

            if ($isSubscriber && $companySlug) {
                $profile = \App\Models\SubscriberProfile::where('company_slug', $companySlug)->first();
                if ($profile) {
                    $subProducts = \App\Models\SubscriberProduct::with(['images'])
                        ->where('user_id', $profile->user_id)
                        ->whereIn('id', $ids)
                        ->get();

                    foreach ($subProducts as $product) {
                        $thumbnail_url = $product->thumbnail_url;
                        $gallery_urls = $product->images->map(function($img) {
                            $image_url = $img->image;
                            if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
                                return asset('uploads/subscriber-products/gallery/' . $img->image);
                            }
                            return $image_url;
                        });

                        $results[$product->id] = [
                            'success' => true,
                            'product' => $product,
                            'thumbnail_url' => $thumbnail_url,
                            'gallery_urls' => $gallery_urls
                        ];
                    }
                }
            } else {
                // Strictly query standard admin Product table (never return subscriber products here)
                $products = Product::with(['images'])
                    ->whereIn('id', $ids)
                    ->get();

                foreach ($products as $product) {
                    $thumbnail_url = $product->thumbnail_url;
                    $gallery_urls = $product->images->map(function($img) {
                        $image_url = $img->image;
                        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
                            return asset('uploads/products/gallery/' . $img->image);
                        }
                        return $image_url;
                    });

                    $results[$product->id] = [
                        'success' => true,
                        'product' => $product,
                        'thumbnail_url' => $thumbnail_url,
                        'gallery_urls' => $gallery_urls
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'products' => $results
        ]);
    }

    /**
     * API for product details (for the drawer).
     */
    public function apiProductDetails(Request $request, $id)
    {
        $subscriberId = $request->attributes->get('custom_domain_subscriber_id');
        $product = null;

        if ($subscriberId) {
            $product = \App\Models\SubscriberProduct::with(['images'])->where('user_id', $subscriberId)->find($id);
        } else {
            $isSubscriber = $request->input('is_subscriber') == 1;
            $companySlug = $request->input('company_slug');

            if ($isSubscriber && $companySlug) {
                $profile = \App\Models\SubscriberProfile::where('company_slug', $companySlug)->first();
                if ($profile) {
                    $product = \App\Models\SubscriberProduct::with(['images'])->where('user_id', $profile->user_id)->find($id);
                }
            } else {
                $product = Product::with(['images'])->find($id);
            }
        }

        if (!$product) {
            abort(404);
        }
        
        $thumbnail_url = $product->thumbnail_url;
        $isSubProduct = $product instanceof \App\Models\SubscriberProduct;

        $gallery_urls = $product->images->map(function($img) use ($isSubProduct) {
            $image_url = $img->image;
            if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
                $folder = $isSubProduct ? 'subscriber-products/gallery/' : 'products/gallery/';
                return asset('uploads/' . $folder . $img->image);
            }
            return $image_url;
        });
        
        return response()->json([
            'success' => true,
            'product' => $product,
            'thumbnail_url' => $thumbnail_url,
            'gallery_urls' => $gallery_urls
        ]);
    }

    /**
     * Return partial view for product drawer.
     */
    public function productQuickView(Request $request, $id)
    {
        $subscriberId = $request->attributes->get('custom_domain_subscriber_id');
        $product = null;

        if ($subscriberId) {
            $product = \App\Models\SubscriberProduct::with(['images', 'attributeValues.attribute'])
                ->where('user_id', $subscriberId)
                ->find($id);
            if ($product) {
                return view('partials.subscriber-product-drawer-content', compact('product'));
            }
        } else {
            $isSubscriber = $request->input('is_subscriber') == 1;
            $companySlug = $request->input('company_slug');

            if ($isSubscriber && $companySlug) {
                $profile = \App\Models\SubscriberProfile::where('company_slug', $companySlug)->first();
                if ($profile) {
                    $product = \App\Models\SubscriberProduct::with(['images', 'attributeValues.attribute'])
                        ->where('user_id', $profile->user_id)
                        ->find($id);
                    if ($product) {
                        return view('partials.subscriber-product-drawer-content', compact('product'));
                    }
                }
            } else {
                $product = Product::with(['images'])->find($id);
                if ($product) {
                    return view('partials.product-drawer-content', compact('product'));
                }
            }
        }

        abort(404);
    }

    /**
     * Display the subscription pricing plans page.
     */
    public function pricing()
    {
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
        return view('pricing', compact('plans'));
    }

    /**
     * Display the public subscriber catalog store page.
     */
    public function storeCatalog($slug, Request $request)
    {
        $profile = \App\Models\SubscriberProfile::where('company_slug', $slug)->firstOrFail();

        // Strictly block other stores when loaded on a custom domain!
        $customDomainSubscriberId = $request->attributes->get('custom_domain_subscriber_id');
        if ($customDomainSubscriberId && $profile->user_id !== $customDomainSubscriberId) {
            return redirect()->to('/');
        }

        // Redirect Enterprise stores with verified custom domains to their own domain disabled to keep default URL hamesha active
        /*
        if (!$isEnterprise && $profile->custom_domain && $profile->domain_verified) {
            $requestHost = strtolower(trim($request->getHost()));
            $customDomain = strtolower(trim($profile->custom_domain));
            $customDomainClean = preg_replace('/^www\./i', '', $customDomain);

            if ($requestHost !== $customDomain && $requestHost !== $customDomainClean && $requestHost !== 'www.' . $customDomainClean) {
                return redirect()->away('https://' . $customDomain, 301);
            }
        }
        */
        
        // Double-approval check: Account status must be approved/active AND Store status must be live
        if ($profile->status !== 'approved' && $profile->status !== 'active') {
            abort(403, 'This storefront account is not active.');
        }

        if ($profile->store_status !== 'live') {
            return response()->view('subscriber-panel.share.pending', ['link' => (object)[
                'title' => $profile->company_name . ' - Storefront Catalog',
                'approval_status' => $profile->store_status ?: 'pending',
                'is_expired' => false,
            ]], 403);
        }

        // Check active subscription
        $subscriber = $profile->user;
        if (!$subscriber || !$subscriber->hasActiveSubscription()) {
            abort(403, 'This storefront has no active subscription.');
        }

        $category = (object)['name' => 'All Products', 'id' => 0];

        // Get approved active subscriber products query
        $query = \App\Models\SubscriberProduct::where('user_id', $profile->user_id)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->with(['images', 'attributeValues.attribute']);

        // Filter by selected product IDs (for catalogue sharing)
        $productIds = $request->input('products');
        if ($productIds) {
            $ids = array_filter(explode(',', $productIds));
            $query->whereIn('id', $ids);
            $category->name = 'Selected Catalogue Products';
        }

        // Search filter
        if ($request->filled('search')) {
            $q = $request->input('search');
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                   ->orWhere('sku', 'like', "%{$q}%")
                   ->orWhere('short_description', 'like', "%{$q}%")
                   ->orWhere('full_description', 'like', "%{$q}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $catSlug = $request->input('category');
            $cat = Category::withoutGlobalScope('tenant')->where('slug', $catSlug)->first();
            if ($cat) {
                $query->where(function($q) use ($cat) {
                    $q->whereJsonContains('category_id', $cat->id)
                      ->orWhereJsonContains('category_id', (string)$cat->id);
                });
            }
        }

        // Subcategory filter
        if ($request->filled('subcategory')) {
            $subSlug = $request->input('subcategory');
            $sub = \App\Models\Subcategory::withoutGlobalScope('tenant')->where('slug', $subSlug)->first();
            if ($sub) {
                $query->where(function($q) use ($sub) {
                    $q->whereJsonContains('subcategory_id', $sub->id)
                      ->orWhereJsonContains('subcategory_id', (string)$sub->id);
                });
            }
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        // Sorting
        $sort = $request->input('sort', 'default');
        match ($sort) {
            'name_asc'   => $query->orderBy('name', 'asc'),
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default      => $query->latest(),
        };

        // Paginate products exactly like standard catalogue
        $products = $query->paginate(12)->withQueryString();

        // Get all categories represented in this subscriber's active approved products
        $categoryIds = \App\Models\SubscriberProduct::where('user_id', $profile->user_id)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->whereNotNull('category_id')
            ->pluck('category_id')
            ->flatten()
            ->filter()
            ->unique();
        $allCategories = \App\Models\Category::withoutGlobalScope('tenant')->whereIn('id', $categoryIds)->get();

        // Get all subcategories represented in this subscriber's active approved products (optionally filtered by category)
        $subQuery = \App\Models\SubscriberProduct::where('user_id', $profile->user_id)
            ->where('status', 'active')
            ->where('approval_status', 'approved');

        if ($request->filled('category')) {
            $catSlug = $request->input('category');
            $cat = \App\Models\Category::withoutGlobalScope('tenant')->where('slug', $catSlug)->first();
            if ($cat) {
                $subQuery->where(function($q) use ($cat) {
                    $q->whereJsonContains('category_id', $cat->id)
                      ->orWhereJsonContains('category_id', (string)$cat->id);
                });
            }
        }

        $subcategoryIds = $subQuery->whereNotNull('subcategory_id')
            ->pluck('subcategory_id')
            ->flatten()
            ->filter()
            ->unique();
        $subcategories = \App\Models\Subcategory::withoutGlobalScope('tenant')->whereIn('id', $subcategoryIds)->get();

        // Category object for header/title context
        if ($request->filled('category')) {
            $cat = \App\Models\Category::withoutGlobalScope('tenant')->where('slug', $request->input('category'))->first();
            if ($cat) {
                $category = $cat;
            }
        }

        // Base64 Logo generation for high-fidelity captures (watermarking/PDF generation)
        $logoBase64 = '';
        if ($profile->logo) {
            $logoPath = public_path('uploads/subscriber-logos/' . $profile->logo);
            if (file_exists($logoPath) && is_file($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = @file_get_contents($logoPath);
                if ($data) {
                    $logoBase64 = 'data:image/' . ($type === 'svg' ? 'svg+xml' : $type) . ';base64,' . base64_encode($data);
                }
            }
        }

        $companyName = $profile->company_name;
        $isSubscriberStore = true;
        
        // Mock default settings for layouts that expect $settings
        $settings = (object)[
            'site_title' => $profile->company_name,
            'logo' => $profile->logo ? 'subscriber-logos/' . $profile->logo : null,
            'footer_logo' => $profile->logo ? 'subscriber-logos/' . $profile->logo : null,
        ];

        return view('category-products', compact(
            'profile', 'category', 'products', 'allCategories', 'subcategories', 
            'companyName', 'isSubscriberStore', 'logoBase64', 'subscriber', 'settings'
        ));
    }

    /**
     * Submit B2B Product Review.
     */
    public function submitReview(Request $request, $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $request->validate([
            'reviewer_name'  => 'required|string|max:255',
            'reviewer_email' => 'required|email|max:255',
            'rating'         => 'required|integer|min:1|max:5',
            'review_content' => 'required|string|max:2000',
            'images.*'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Verified buyer check
        $isVerified = \App\Models\Enquiry::where('email', $request->reviewer_email)
            ->where('product_id', $product->id)
            ->exists();

        $reviewImages = [];
        if ($request->hasFile('images')) {
            $destDir = storage_path('app/public/reviews');
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            foreach ($request->file('images') as $file) {
                $imageName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destDir, $imageName);
                $reviewImages[] = $imageName;
            }
        }

        $review = \App\Models\Review::create([
            'product_id'        => $product->id,
            'user_id'           => auth()->id(),
            'rating'            => $request->rating,
            'reviewer_name'     => $request->reviewer_name,
            'reviewer_email'    => $request->reviewer_email,
            'review_content'    => $request->review_content,
            'images'            => $reviewImages,
            'is_verified_buyer' => $isVerified,
            'status'            => true,
        ]);

        return response()->json([
            'success'        => true,
            'message'        => 'Thank you! Your product review has been submitted successfully.',
            'review'         => [
                'reviewer_name'     => $review->reviewer_name,
                'rating'            => $review->rating,
                'review_content'    => $review->review_content,
                'created_at'        => $review->created_at->diffForHumans(),
                'is_verified_buyer' => $review->is_verified_buyer,
                'images'            => array_map(function($img) {
                    return asset('storage/reviews/' . $img);
                }, $review->images ?? []),
            ],
            'average_rating' => $product->average_rating,
            'reviews_count'  => $product->reviews_count,
        ]);
    }

    /**
     * Download Product PDF Catalogue with QR Code.
     */
    public function downloadProductPdf($slug)
    {
        $product = Product::where('slug', $slug)->with([])->firstOrFail();

        $qrUrl = route('product.details', $product->slug);
        
        $qrCodeBase64 = '';
        try {
            $qrCodeBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(140)->margin(0)->generate($qrUrl));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('QR Code Generation Error: ' . $e->getMessage());
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.product', compact('product', 'qrCodeBase64'));
        return $pdf->download($product->slug . '-specification.pdf');
    }

    /**
     * Generate dynamic XML Sitemap for products.
     */
    public function sitemap()
    {
        $products = Product::where('status', 1)->latest()->get();
        $categories = Category::where('status', 1)->get();
        $subcategories = Subcategory::where('status', 1)->get();

        $xml = view('seo.sitemap', compact('products', 'categories', 'subcategories'))->render();

        return response($xml, 200, [
            'Content-Type' => 'text/xml'
        ]);
    }

    /**
     * AJAX Product Catalogue Filtering.
     */
    public function apiFilterProducts(Request $request)
    {
        $query = Product::where('status', 1)->with(['reviews']);

        if ($request->filled('query')) {
            $q = $request->input('query');
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                   ->orWhere('sku', 'like', "%{$q}%")
                   ->orWhere('short_description', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category')) {
            $catSlug = $request->input('category');
            if ($catSlug !== 'all') {
                $cat = Category::where('slug', $catSlug)->first();
                if ($cat) {
                    $query->where(function($q) use ($cat) {
                        $q->whereJsonContains('category_id', $cat->id)
                          ->orWhereJsonContains('category_id', (string)$cat->id);
                    });
                }
            }
        }

        if ($request->filled('subcategory')) {
            $subSlug = $request->input('subcategory');
            $sub = Subcategory::where('slug', $subSlug)->first();
            if ($sub) {
                $query->where(function($q) use ($sub) {
                    $q->whereJsonContains('subcategory_id', $sub->id)
                      ->orWhereJsonContains('subcategory_id', (string)$sub->id);
                });
            }
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        $sort = $request->input('sort', 'default');
        match ($sort) {
            'name_asc'   => $query->orderBy('name', 'asc'),
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default      => $query->latest(),
        };

        $products = $query->paginate(12);

        $html = view('partials.product-grid-items', compact('products'))->render();
        $paginationHtml = $products->links('pagination::bootstrap-5')->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'pagination_html' => $paginationHtml,
            'count' => $products->total(),
        ]);
    }
}


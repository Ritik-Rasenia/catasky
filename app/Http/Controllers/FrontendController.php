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
            ->with(['category', 'brand'])
            ->latest()
            ->take(8)
            ->get();
            
        return view('welcome', compact('categories', 'featuredProducts'));
    }

    /**
     * Display the full catalogue.
     */
    public function catalogue(Request $request)
    {
        $category = (object)['name' => 'All Products', 'id' => 0];

        $query = Product::where('status', 1)->with(['category', 'brand']);

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
                $query->where('subcategory_id', $sub->id);
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

        $query = Product::where('category_id', $category->id)
            ->where('status', 1)
            ->with(['category', 'brand']);

        // Filter by subcategory
        if ($request->filled('subcategory')) {
            $sub = Subcategory::where('slug', $request->input('subcategory'))->first();
            if ($sub) {
                $query->where('subcategory_id', $sub->id);
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
     * Display product details.
     */
    public function productDetails($slug)
    {
        $product = Product::where('slug', $slug)
            ->with(['category', 'brand', 'images'])
            ->firstOrFail();

        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)
            ->get();

        return view('product-details', compact('product', 'relatedProducts'));
    }

    /**
     * Search products.
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        $products = Product::where('name', 'like', "%$query%")
            ->orWhere('short_description', 'like', "%$query%")
            ->where('status', 1)
            ->paginate(12);

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
            'product_id' => 'nullable|exists:products,id',
            'brand_id' => 'nullable|exists:brands,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string'
        ]);

        \App\Models\Enquiry::create([
            'product_id' => $request->product_id,
            'brand_id' => $request->brand_id ?: ($request->product_id ? \App\Models\Product::find($request->product_id)->brand_id : null),
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

        $products = Product::with(['category', 'brand', 'images'])
            ->whereIn('id', $ids)
            ->get();

        $results = [];
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

        return response()->json([
            'success' => true,
            'products' => $results
        ]);
    }

    /**
     * API for product details (for the drawer).
     */
    public function apiProductDetails($id)
    {
        $product = Product::with(['category', 'brand', 'images'])->findOrFail($id);
        
        $thumbnail_url = $product->thumbnail_url;

        $gallery_urls = $product->images->map(function($img) {
            $image_url = $img->image;
            if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
                return asset('uploads/products/gallery/' . $img->image);
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
    public function productQuickView($id)
    {
        $product = Product::with(['category', 'brand', 'images'])->findOrFail($id);
        return view('partials.product-drawer-content', compact('product'));
    }

    /**
     * Display the subscription pricing plans page.
     */
    public function pricing()
    {
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)
            ->where('is_trial', false)
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
        
        // Double-approval check: Store status must be active
        if ($profile->status !== 'active') {
            return response()->view('subscriber-panel.share.pending', ['link' => (object)[
                'title' => $profile->company_name . ' - Storefront Catalog',
                'approval_status' => 'pending',
                'is_expired' => false,
            ]], 403);
        }

        // Check active subscription
        $subscriber = $profile->user;
        if (!$subscriber || !$subscriber->hasActiveSubscription()) {
            abort(403, 'This storefront has no active subscription.');
        }

        // Get approved active subscriber products
        $query = \App\Models\SubscriberProduct::where('user_id', $profile->user_id)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->with(['images', 'attributeValues.attribute', 'category']);

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        // Category filter
        if ($request->filled('category')) {
            $catSlug = $request->input('category');
            $query->whereHas('category', function($q) use ($catSlug) {
                $q->where('slug', $catSlug);
            });
        }

        $catalogProducts = $query->orderBy('name')->get();

        // Get categories represented in subscriber products
        $subscriberCategories = \App\Models\Category::whereHas('products', function($q) use ($profile) {
            // Note: does Category have products or subscriberProducts relation? Let's check Category.php or use basic query.
        })->get();
        
        // Wait, let's write a robust query for categories.
        $categoryIds = \App\Models\SubscriberProduct::where('user_id', $profile->user_id)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->whereNotNull('category_id')
            ->pluck('category_id')
            ->unique();
        
        $subscriberCategories = \App\Models\Category::whereIn('id', $categoryIds)->get();

        $companyName = $profile->company_name;
        $settings = [
            'show_mrp'         => true,
            'show_offer_price' => true,
            'show_description' => true,
            'show_attributes'  => true,
            'show_images'      => true,
            'show_contact'     => true,
            'allow_download'   => false,
        ];

        return view('subscriber-panel.share.store', compact('profile', 'catalogProducts', 'subscriberCategories', 'companyName', 'settings', 'subscriber'));
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
        $product = Product::where('slug', $slug)->with(['category', 'brand'])->firstOrFail();

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
        $query = Product::where('status', 1)->with(['category', 'brand', 'reviews']);

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
                $query->whereHas('category', function($sub) use ($catSlug) {
                    $sub->where('slug', $catSlug);
                });
            }
        }

        if ($request->filled('subcategory')) {
            $subSlug = $request->input('subcategory');
            $query->whereHas('subcategory', function($sub) use ($subSlug) {
                $sub->where('slug', $subSlug);
            });
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


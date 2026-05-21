<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\Solution;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    /**
     * Display the landing catalogue page.
     */
    public function index()
    {
        $categories = Category::where('status', 1)->withCount('products')->get();
        $featuredProducts = Product::where('status', 1)
            ->where('featured', 1)
            ->with(['category', 'brand'])
            ->latest()
            ->take(8)
            ->get();
            
        $solutions = Solution::all();

        return view('welcome', compact('categories', 'featuredProducts', 'solutions'));
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
     * Display child categories.
     */
    public function childCategories()
    {
        $childCategories = ChildCategory::with(['subcategory', 'products'])->get();
        return view('childcategories', compact('childCategories'));
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
            ->with(['category', 'brand', 'images', 'solutions'])
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
     * Solutions list.
     */
    public function solutions()
    {
        $solutions = Solution::all();
        return view('solutions.index', compact('solutions'));
    }

    /**
     * Solution details.
     */
    public function solutionDetails($slug)
    {
        $solution = Solution::where('slug', $slug)->with('products')->firstOrFail();
        return view('solutions.show', compact('solution'));
    }

    /**
     * API for subcategories.
     */
    public function getApiSubcategories($category_id)
    {
        return Subcategory::where('category_id', $category_id)->get();
    }

    /**
     * API for child categories.
     */
    public function getApiChildcategories($subcategory_id)
    {
        return ChildCategory::where('subcategory_id', $subcategory_id)->get();
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

        $products = Product::with(['category', 'brand', 'images', 'solutions'])
            ->whereIn('id', $ids)
            ->get();

        $results = [];
        foreach ($products as $product) {
            $thumbnail_url = $product->thumbnail;
            if (!filter_var($thumbnail_url, FILTER_VALIDATE_URL)) {
                $thumbnail_url = asset('uploads/products/' . $product->thumbnail);
            }

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
        $product = Product::with(['category', 'brand', 'images', 'solutions'])->findOrFail($id);
        
        $thumbnail_url = $product->thumbnail;
        if (!filter_var($thumbnail_url, FILTER_VALIDATE_URL)) {
            $thumbnail_url = asset('uploads/products/' . $product->thumbnail);
        }

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
}

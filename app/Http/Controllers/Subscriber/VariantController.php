<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProductVariant;
use App\Models\SubscriberProductVariantAttribute;
use App\Models\CategoryAttribute;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    /**
     * Display all subscriber product variants.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SubscriberProductVariant::whereHas('product', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->with(['product', 'variantAttributes.attribute']);

        if ($request->search) {
            $query->where('variant_sku', 'like', '%' . $request->search . '%')
                  ->orWhereHas('product', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
        }

        $variants = $query->latest()->paginate(15);

        return view('subscriber-panel.variants.index', compact('variants'));
    }

    /**
     * Show page to create variants for a specific product.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $products = SubscriberProduct::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $selectedProduct = null;
        $variantAttributes = collect();

        if ($request->product_id) {
            $selectedProduct = SubscriberProduct::where('user_id', $user->id)->findOrFail($request->product_id);
            
            // Get attributes that are marked as variant-enabled in the category template mapping
            $categoryAttributes = CategoryAttribute::where('category_id', $selectedProduct->category_id)
                ->whereHas('attribute', function($q) {
                    $q->where('is_variant_enabled', true);
                })
                ->with('attribute')
                ->get();
                
            $variantAttributes = $categoryAttributes->pluck('attribute');
        }

        return view('subscriber-panel.variants.create', compact('products', 'selectedProduct', 'variantAttributes'));
    }

    /**
     * Store new product variant.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subscriber_product_id' => 'required|exists:subscriber_products,id',
            'variant_sku'           => 'required|string|unique:subscriber_product_variants,variant_sku',
            'price'                 => 'nullable|numeric|min:0',
            'stock'                 => 'required|integer|min:0',
            'attributes'            => 'required|array', // attribute_id => value (e.g. XL, Red)
        ]);

        $product = SubscriberProduct::where('user_id', auth()->id())->findOrFail($request->subscriber_product_id);

        $variant = SubscriberProductVariant::create([
            'subscriber_product_id' => $product->id,
            'variant_sku'           => $request->variant_sku,
            'price'                 => $request->price,
            'stock'                 => $request->stock,
            'status'                => true,
        ]);

        foreach ($request->input('attributes') as $attributeId => $value) {
            if ($value !== null && $value !== '') {
                SubscriberProductVariantAttribute::create([
                    'variant_id'      => $variant->id,
                    'attribute_id'    => $attributeId,
                    'attribute_value' => $value,
                ]);
            }
        }

        return redirect()->route('subscriber.variants.index')
            ->with('success', 'Product Variant created successfully!');
    }

    /**
     * Update variant price/stock quickly.
     */
    public function update(Request $request, SubscriberProductVariant $variant)
    {
        if ($variant->product->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $variant->update([
            'price' => $request->price,
            'stock' => $request->stock,
            'status' => $request->boolean('status', true),
        ]);

        return back()->with('success', 'Variant updated successfully.');
    }

    /**
     * Delete product variant.
     */
    public function destroy(SubscriberProductVariant $variant)
    {
        if ($variant->product->user_id !== auth()->id()) {
            abort(403);
        }

        $variant->variantAttributes()->delete();
        $variant->delete();

        return back()->with('success', 'Variant deleted.');
    }
}

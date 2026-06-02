<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProductVariant;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Display subscriber inventory dashboard.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Load active subscriber products with their variants
        $query = SubscriberProduct::where('user_id', $user->id)
            ->with(['variants']);

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        if ($request->stock_status === 'low') {
            // Low stock is less than 5 units
            $query->where(function($q) {
                // If product has no variants, check MRP/product level (or a simple fallback).
                // If it has variants, check variant stock.
                $q->whereHas('variants', function($varQ) {
                    $varQ->where('stock', '<', 5);
                });
            });
        }

        $products = $query->latest()->paginate(15);

        return view('subscriber-panel.inventory.index', compact('products'));
    }

    /**
     * Update stock level directly.
     */
    public function updateStock(Request $request)
    {
        $request->validate([
            'type' => 'required|in:product,variant',
            'id'   => 'required|integer',
            'stock' => 'required|integer|min:0',
        ]);

        $user = auth()->user();

        if ($request->type === 'variant') {
            $variant = SubscriberProductVariant::whereHas('product', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->findOrFail($request->id);

            $variant->update(['stock' => $request->stock]);
        } else {
            // For simple products, we can either save the stock globally or at simple variant level.
            // If they don't have a variant, stock might just be mapped to a dummy/default variant or a direct column.
            // Wait, does subscriber_products table have a 'stock' column? Let's check!
            // Wait, subscriber_products doesn't have a stock column in the create migration we saw! 
            // All stock changes are managed by subscriber_product_variants (the Variant Engine).
            // Let's create or update a default variant if none exists for a product!
            $product = SubscriberProduct::where('user_id', $user->id)->findOrFail($request->id);
            
            // Get or create default variant
            $variant = SubscriberProductVariant::firstOrCreate(
                ['subscriber_product_id' => $product->id, 'variant_sku' => $product->sku ?: 'DEFAULT-' . $product->id],
                ['price' => $product->offer_price ?: $product->mrp, 'stock' => 0]
            );

            $variant->update(['stock' => $request->stock]);
        }

        return response()->json(['success' => true, 'message' => 'Stock updated successfully.']);
    }
}

<?php

namespace App\Http\Controllers\Subscriber;

use App\Http\Controllers\Controller;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProductImage;
use App\Models\SubscriberProductAttributeValue;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\SubscriberActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SubscriberProduct::where('user_id', $user->id)->with(['images', 'category']);

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(12);
        $categories = Category::orderBy('name')->get();

        return view('subscriber-panel.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $user = auth()->user();
        $categories = Category::orderBy('name')->get();
        $attributes = Attribute::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['group', 'options'])
            ->orderBy('sort_order')
            ->get();

        return view('subscriber-panel.products.create', compact('categories', 'attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:255',
            'status'            => 'required|in:active,inactive,draft',
            'mrp'               => 'nullable|numeric|min:0',
            'offer_price'       => 'nullable|numeric|min:0',
            'short_description' => 'nullable|string|max:1000',
            'full_description'  => 'nullable|string',
            'thumbnail'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $user = auth()->user();
        $data = $request->except(['_token', 'thumbnail', 'images', 'attributes']);
        $data['user_id'] = $user->id;
        $data['slug'] = Str::slug($request->name) . '-' . Str::random(6);
        $data['tags'] = $request->tags ? array_map('trim', explode(',', $request->tags)) : null;

        // Visibility toggles
        $data['pdf_show_mrp'] = $request->boolean('pdf_show_mrp');
        $data['pdf_show_offer_price'] = $request->boolean('pdf_show_offer_price');
        $data['pdf_show_description'] = $request->boolean('pdf_show_description');
        $data['pdf_show_attributes'] = $request->boolean('pdf_show_attributes');
        $data['pdf_show_images'] = $request->boolean('pdf_show_images');
        $data['pdf_show_short_desc'] = $request->boolean('pdf_show_short_desc');
        $data['share_show_mrp'] = $request->boolean('share_show_mrp');
        $data['share_show_offer_price'] = $request->boolean('share_show_offer_price');
        $data['share_show_description'] = $request->boolean('share_show_description');
        $data['share_show_attributes'] = $request->boolean('share_show_attributes');

        // Upload thumbnail
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->uploadImage($request->file('thumbnail'), 'subscriber-products');
        }

        $data['approval_status'] = 'pending';
        $product = SubscriberProduct::create($data);

        // Upload additional images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $imageFile) {
                $path = $this->uploadImage($imageFile, 'subscriber-products');
                SubscriberProductImage::create([
                    'subscriber_product_id' => $product->id,
                    'image_path'        => $path,
                    'is_primary'        => $index === 0,
                    'sort_order'        => $index,
                ]);
            }
        }

        // Save attribute values
        $this->saveAttributeValues($product, $request);

        SubscriberActivityLog::log('created', 'Created product: ' . $product->name, $product);

        return redirect()->route('subscriber.products.index')
            ->with('success', 'Product created successfully!');
    }

    public function show(SubscriberProduct $product)
    {
        $this->authorizeSubscriberProduct($product);
        $product->load(['images', 'category', 'attributeValues.attribute']);
        return view('subscriber-panel.products.show', compact('product'));
    }

    public function edit(SubscriberProduct $product)
    {
        $this->authorizeSubscriberProduct($product);
        $user = auth()->user();
        $product->load(['images', 'attributeValues.attribute']);
        $categories = Category::orderBy('name')->get();
        $subcategories = $product->category_id ? Subcategory::where('category_id', $product->category_id)->get() : collect();
        $attributes = Attribute::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['group', 'options'])
            ->orderBy('sort_order')
            ->get();

        $existingValues = $product->attributeValues->keyBy('attribute_id');

        return view('subscriber-panel.products.edit', compact('product', 'categories', 'subcategories', 'attributes', 'existingValues'));
    }

    public function update(Request $request, SubscriberProduct $product)
    {
        $this->authorizeSubscriberProduct($product);

        $request->validate([
            'name'              => 'required|string|max:255',
            'status'            => 'required|in:active,inactive,draft',
            'mrp'               => 'nullable|numeric|min:0',
            'offer_price'       => 'nullable|numeric|min:0',
            'thumbnail'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $data = $request->except(['_token', '_method', 'thumbnail', 'images', 'attributes']);
        $data['tags'] = $request->tags ? array_map('trim', explode(',', $request->tags)) : null;
        $data['pdf_show_mrp'] = $request->boolean('pdf_show_mrp');
        $data['pdf_show_offer_price'] = $request->boolean('pdf_show_offer_price');
        $data['pdf_show_description'] = $request->boolean('pdf_show_description');
        $data['pdf_show_attributes'] = $request->boolean('pdf_show_attributes');
        $data['pdf_show_images'] = $request->boolean('pdf_show_images');
        $data['pdf_show_short_desc'] = $request->boolean('pdf_show_short_desc');
        $data['share_show_mrp'] = $request->boolean('share_show_mrp');
        $data['share_show_offer_price'] = $request->boolean('share_show_offer_price');
        $data['share_show_description'] = $request->boolean('share_show_description');
        $data['share_show_attributes'] = $request->boolean('share_show_attributes');

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->uploadImage($request->file('thumbnail'), 'subscriber-products');
        }

        $data['approval_status'] = 'pending';
        $product->update($data);

        // Upload new images
        if ($request->hasFile('images')) {
            $lastOrder = $product->images()->max('sort_order') ?? 0;
            foreach ($request->file('images') as $index => $imageFile) {
                $path = $this->uploadImage($imageFile, 'subscriber-products');
                SubscriberProductImage::create([
                    'subscriber_product_id' => $product->id,
                    'image_path'        => $path,
                    'is_primary'        => false,
                    'sort_order'        => $lastOrder + $index + 1,
                ]);
            }
        }

        // Update attribute values
        $this->saveAttributeValues($product, $request);

        SubscriberActivityLog::log('updated', 'Updated product: ' . $product->name, $product);

        return redirect()->route('subscriber.products.index')
            ->with('success', 'Product updated successfully!');
    }

    public function destroy(SubscriberProduct $product)
    {
        $this->authorizeSubscriberProduct($product);
        SubscriberActivityLog::log('deleted', 'Deleted product: ' . $product->name, $product);
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    public function deleteImage(Request $request, SubscriberProductImage $image)
    {
        // Ensure image belongs to subscriber's product
        if ($image->product->user_id !== auth()->id()) {
            abort(403);
        }
        $image->delete();
        return response()->json(['success' => true]);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function authorizeSubscriberProduct(SubscriberProduct $product): void
    {
        if ($product->user_id !== auth()->id()) {
            abort(403, 'You do not have permission to access this product.');
        }
    }

    private function uploadImage($file, string $folder): string
    {
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/' . $folder), $filename);
        return $filename;
    }

    private function saveAttributeValues(SubscriberProduct $product, Request $request): void
    {
        $attributeData = $request->input('attributes', []);
        foreach ($attributeData as $attributeId => $value) {
            if ($value === null || $value === '') {
                SubscriberProductAttributeValue::where('subscriber_product_id', $product->id)
                    ->where('attribute_id', $attributeId)
                    ->delete();
                continue;
            }

            $storedValue = is_array($value) ? json_encode($value) : $value;

            SubscriberProductAttributeValue::updateOrCreate(
                ['subscriber_product_id' => $product->id, 'attribute_id' => $attributeId],
                ['value' => $storedValue]
            );
        }
    }

    // AJAX: get category-specific dynamic attributes template
    public function getCategoryAttributes(Request $request, $categoryId)
    {
        // Prefer category-level attributes as a fallback when no subcategory specified
        $categoryAttributes = \App\Models\CategoryAttribute::where('category_id', $categoryId)
            ->with(['attribute' => function($q) {
                $q->where('is_active', true)->with(['group', 'options']);
            }])
            ->orderBy('sort_order')
            ->get();

        $attributes = $categoryAttributes->pluck('attribute')->filter();

        $grouped = $attributes->groupBy(function($attr) {
            return $attr->group?->name ?? 'General / Basic Specifications';
        });

        $result = [];
        foreach ($grouped as $groupName => $attrs) {
            $mappedAttrs = [];
            foreach ($attrs as $attr) {
                $catAttr = $categoryAttributes->where('attribute_id', $attr->id)->first();
                $mappedAttrs[] = [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'type' => $attr->type,
                    'unit' => $attr->unit,
                    'placeholder' => $attr->placeholder,
                    'default_value' => $attr->default_value,
                    'is_required' => $catAttr ? (bool)$catAttr->is_required : (bool)$attr->is_required,
                    'options' => $attr->options->map(function($opt) {
                        return [
                            'value' => $opt->value,
                            'label' => $opt->label,
                            'is_default' => (bool)$opt->is_default
                        ];
                    })
                ];
            }
            $result[] = [
                'group_name' => $groupName,
                'attributes' => $mappedAttrs
            ];
        }

        return response()->json($result);
    }

    // AJAX: get subcategory-specific dynamic attributes template (preferred)
    public function getSubcategoryAttributes(Request $request, $subcategoryId)
    {
        $subcatAttrs = \App\Models\SubcategoryAttribute::where('subcategory_id', $subcategoryId)
            ->with(['attribute' => function($q) {
                $q->where('is_active', true)->with(['group', 'options']);
            }])
            ->orderBy('sort_order')
            ->get();

        if ($subcatAttrs->isEmpty()) {
            // Fallback to category-level attributes if none assigned to subcategory
            $subcat = \App\Models\Subcategory::find($subcategoryId);
            if (!$subcat) return response()->json([]);
            return $this->getCategoryAttributes($request, $subcat->category_id);
        }

        $attributes = $subcatAttrs->pluck('attribute')->filter();

        $grouped = $attributes->groupBy(function($attr) {
            return $attr->group?->name ?? 'General / Basic Specifications';
        });

        $result = [];
        foreach ($grouped as $groupName => $attrs) {
            $mappedAttrs = [];
            foreach ($attrs as $attr) {
                $subAttr = $subcatAttrs->where('attribute_id', $attr->id)->first();
                $mappedAttrs[] = [
                    'id' => $attr->id,
                    'name' => $attr->name,
                    'type' => $attr->type,
                    'unit' => $attr->unit,
                    'placeholder' => $attr->placeholder,
                    'default_value' => $attr->default_value,
                    'is_required' => $subAttr ? (bool)$subAttr->is_required : (bool)$attr->is_required,
                    'options' => $attr->options->map(function($opt) {
                        return [
                            'value' => $opt->value,
                            'label' => $opt->label,
                            'is_default' => (bool)$opt->is_default
                        ];
                    })
                ];
            }
            $result[] = [
                'group_name' => $groupName,
                'attributes' => $mappedAttrs
            ];
        }

        return response()->json($result);
    }

    // AJAX: get subcategories
    public function getSubcategories(Request $request)
    {
        $subcategories = Subcategory::where('category_id', $request->category_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($subcategories);
    }
}

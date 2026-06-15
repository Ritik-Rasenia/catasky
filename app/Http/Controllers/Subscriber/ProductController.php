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
use App\Models\ProductImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SubscriberProduct::where('user_id', $user->id)->with(['images']);

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
        $categories = Category::where('subscriber_id', $user->id)->orderBy('name')->get();

        return view('subscriber-panel.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $user = auth()->user();
        $categories = Category::where('subscriber_id', $user->id)->orderBy('name')->get();
        $brands = \App\Models\Brand::where('subscriber_id', $user->id)->orderBy('name')->get();
        $attributes = Attribute::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['group', 'options'])
            ->orderBy('sort_order')
            ->get();

        return view('subscriber-panel.products.create', compact('categories', 'brands', 'attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'              => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('subscriber_products', 'name')
                    ->where(fn ($query) => $query->where('user_id', auth()->id())->whereNull('deleted_at'))
            ],
            'sku'               => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('subscriber_products', 'sku')
                    ->where(fn ($query) => $query->where('user_id', auth()->id())->whereNull('deleted_at'))
            ],
            'category_id'       => 'nullable|array',
            'category_id.*'     => [
                \Illuminate\Validation\Rule::exists('categories', 'id')->where(fn ($q) => $q->where('subscriber_id', auth()->id())->orWhereNull('subscriber_id')),
            ],
            'subcategory_id'    => 'nullable|array',
            'subcategory_id.*'  => [
                \Illuminate\Validation\Rule::exists('subcategories', 'id')->where(fn ($q) => $q->where('subscriber_id', auth()->id())->orWhereNull('subscriber_id')),
            ],
            'child_category_id' => 'nullable|exists:child_categories,id',
            'brand_id'          => 'nullable|array',
            'brand_id.*'        => [
                \Illuminate\Validation\Rule::exists('brands', 'id')->where(fn ($q) => $q->where('subscriber_id', auth()->id())->orWhereNull('subscriber_id')),
            ],
            'status'            => 'nullable|in:active,inactive,draft',
            'mrp'               => 'nullable|numeric|min:0',
            'offer_price'       => 'nullable|numeric|min:0',
            'price'             => 'nullable|numeric|min:0',
            'stock'             => 'nullable|integer|min:0',
            'moq'               => 'nullable|integer|min:1',
            'stock_status'      => 'nullable|string|in:in_stock,out_of_stock',
            'short_description' => 'nullable|string|max:1000',
            'full_description'  => 'nullable|string',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string',
            'thumbnail'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $user = auth()->user();

        if ($duplicate = $this->findDuplicateForSubscriber($user->id, $request->input('name'), $request->input('sku'))) {
            $field = strcasecmp(trim($duplicate->name), trim($request->input('name'))) === 0 ? 'name' : 'sku';

            return back()
                ->withErrors([$field => "Product '{$duplicate->name}' already exists in your catalogue."])
                ->withInput();
        }

        $data = $request->except(['_token', 'thumbnail', 'images', 'attributes']);
        $data['user_id'] = $user->id;
        $data['slug'] = Str::slug($request->name) . '-' . Str::random(6);
        $data['tags'] = $request->tags ? array_map('trim', explode(',', $request->tags)) : null;

        // Handle mrp and offer_price - support both direct fields and legacy 'price' field
        if ($request->filled('mrp')) {
            $data['mrp'] = $request->mrp;
        }
        if ($request->filled('offer_price')) {
            $data['offer_price'] = $request->offer_price;
        }
        // Legacy price field fallback
        if ($request->filled('price') && !$request->filled('mrp') && !$request->filled('offer_price')) {
            $data['offer_price'] = $request->price;
            $data['mrp'] = $request->price;
        }

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

        $data['approval_status'] = 'approved';
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
        $product->load(['images', 'attributeValues.attribute']);
        return view('subscriber-panel.products.show', compact('product'));
    }

    public function edit(SubscriberProduct $product)
    {
        $this->authorizeSubscriberProduct($product);
        $user = auth()->user();
        $product->load(['images', 'attributeValues.attribute']);
        $categories = Category::where('subscriber_id', $user->id)->orderBy('name')->get();
        $brands = \App\Models\Brand::where('subscriber_id', $user->id)->orderBy('name')->get();
        $catIds = is_array($product->category_id) ? $product->category_id : ($product->category_id ? [$product->category_id] : []);
        $subcategories = !empty($catIds) ? Subcategory::where('subscriber_id', $user->id)->whereIn('category_id', $catIds)->get() : collect();
        $subIds = is_array($product->subcategory_id) ? $product->subcategory_id : ($product->subcategory_id ? [$product->subcategory_id] : []);
        $productTypes = !empty($subIds) ? \App\Models\ChildCategory::whereIn('subcategory_id', $subIds)->get() : collect();
        $attributes = Attribute::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['group', 'options'])
            ->orderBy('sort_order')
            ->get();

        $existingValues = $product->attributeValues->keyBy('attribute_id');

        return view('subscriber-panel.products.edit', compact('product', 'categories', 'brands', 'subcategories', 'productTypes', 'attributes', 'existingValues'));
    }

    public function update(Request $request, SubscriberProduct $product)
    {
        $this->authorizeSubscriberProduct($product);
 
        $request->validate([
            'name'              => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('subscriber_products', 'name')
                    ->ignore($product->id)
                    ->where(fn ($query) => $query->where('user_id', auth()->id())->whereNull('deleted_at'))
            ],
            'sku'               => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('subscriber_products', 'sku')
                    ->ignore($product->id)
                    ->where(fn ($query) => $query->where('user_id', auth()->id())->whereNull('deleted_at'))
            ],
            'category_id'       => 'nullable|array',
            'category_id.*'     => [
                \Illuminate\Validation\Rule::exists('categories', 'id')->where(fn ($q) => $q->where('subscriber_id', auth()->id())->orWhereNull('subscriber_id')),
            ],
            'subcategory_id'    => 'nullable|array',
            'subcategory_id.*'  => [
                \Illuminate\Validation\Rule::exists('subcategories', 'id')->where(fn ($q) => $q->where('subscriber_id', auth()->id())->orWhereNull('subscriber_id')),
            ],
            'child_category_id' => 'nullable|exists:child_categories,id',
            'brand_id'          => 'nullable|array',
            'brand_id.*'        => [
                \Illuminate\Validation\Rule::exists('brands', 'id')->where(fn ($q) => $q->where('subscriber_id', auth()->id())->orWhereNull('subscriber_id')),
            ],
            'status'            => 'nullable|in:active,inactive,draft',
            'mrp'               => 'nullable|numeric|min:0',
            'offer_price'       => 'nullable|numeric|min:0',
            'price'             => 'nullable|numeric|min:0',
            'stock'             => 'nullable|integer|min:0',
            'moq'               => 'nullable|integer|min:1',
            'stock_status'      => 'nullable|string|in:in_stock,out_of_stock',
            'short_description' => 'nullable|string|max:1000',
            'full_description'  => 'nullable|string',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string',
            'thumbnail'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($duplicate = $this->findDuplicateForSubscriber(auth()->id(), $request->input('name'), $request->input('sku'), $product->id)) {
            $field = strcasecmp(trim($duplicate->name), trim($request->input('name'))) === 0 ? 'name' : 'sku';

            return back()
                ->withErrors([$field => "Product '{$duplicate->name}' already exists in your catalogue."])
                ->withInput();
        }

        $data = $request->except(['_token', '_method', 'thumbnail', 'images', 'attributes']);
        $data['tags'] = $request->tags ? array_map('trim', explode(',', $request->tags)) : null;

        // Handle mrp and offer_price - support both direct fields and legacy 'price' field
        if ($request->filled('mrp')) {
            $data['mrp'] = $request->mrp;
        }
        if ($request->filled('offer_price')) {
            $data['offer_price'] = $request->offer_price;
        }
        // Legacy price field fallback
        if ($request->filled('price') && !$request->filled('mrp') && !$request->filled('offer_price')) {
            $data['offer_price'] = $request->price;
            $data['mrp'] = $request->price;
        }
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

        if ($request->boolean('remove_thumbnail')) {
            $this->deleteSubscriberProductFile($product->thumbnail);
            $data['thumbnail'] = null;
        }

        if ($request->hasFile('thumbnail')) {
            $this->deleteSubscriberProductFile($product->thumbnail);
            $data['thumbnail'] = $this->uploadImage($request->file('thumbnail'), 'subscriber-products');
        }

        $data['approval_status'] = 'approved';
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
        $this->deleteSubscriberProductFile($product->thumbnail);
        foreach ($product->images as $image) {
            $this->deleteSubscriberProductFile($image->image_path);
            $image->delete();
        }
        $product->delete();
        return back()->with('success', 'Product deleted.');
    }

    public function deleteImage(Request $request, SubscriberProductImage $image)
    {
        // Ensure image belongs to subscriber's product
        if ($image->product->user_id !== auth()->id()) {
            abort(403);
        }
        $this->deleteSubscriberProductFile($image->image_path);
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

    private function findDuplicateForSubscriber(int $userId, ?string $name, ?string $sku, ?int $ignoreId = null): ?SubscriberProduct
    {
        $name = trim((string) $name);
        $sku = trim((string) $sku);

        return SubscriberProduct::whereNull('deleted_at')
            ->where('user_id', $userId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($name, $sku) {
                $query->whereRaw('LOWER(name) = ?', [strtolower($name)]);

                if ($sku !== '') {
                    $query->orWhereRaw('LOWER(sku) = ?', [strtolower($sku)]);
                }
            })
            ->first();
    }

    private function uploadImage($file, string $folder): string
    {
        $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/' . $folder), $filename);
        return $filename;
    }

    private function deleteSubscriberProductFile(?string $filename): void
    {
        if (! $filename || filter_var($filename, FILTER_VALIDATE_URL)) {
            return;
        }

        $relative = str_starts_with($filename, 'uploads/')
            ? $filename
            : 'uploads/subscriber-products/' . $filename;
        $path = public_path($relative);

        if (is_file($path)) {
            @unlink($path);
        }
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
                $q->withoutGlobalScope('tenant')
                  ->where('is_active', true)
                  ->where(function($query) {
                      $query->where('approval_status', 'approved')
                            ->orWhere(function($subq) {
                                $subq->where('user_id', auth()->id())
                                     ->where('approval_status', 'pending');
                            });
                  })
                  ->with(['group', 'options']);
            }])
            ->orderBy('sort_order')
            ->get();

        $attributes = $categoryAttributes->pluck('attribute')->filter();

        // Group attributes using mapGroupSection
        $grouped = $attributes->groupBy(function($attr) {
            return $this->mapGroupSection($attr->group?->name);
        });

        // Ensure we order them strictly: Basic, Technical, Packaging, Compliance, Commercial
        $orderedGroups = [
            'Basic Details',
            'Technical Specifications',
            'Packaging Details',
            'Compliance & Safety',
            'Commercial Details'
        ];

        $result = [];
        foreach ($orderedGroups as $groupName) {
            if (!$grouped->has($groupName)) {
                continue;
            }
            $attrs = $grouped[$groupName];
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
                    'approval_status' => $attr->approval_status,
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
    public function getSubcategoryAttributes(Request $request, $subcategoryId = null)
    {
        if (!$subcategoryId) {
            return response()->json([]);
        }
        $subcatAttrs = \App\Models\SubcategoryAttribute::where('subcategory_id', $subcategoryId)
            ->with(['attribute' => function($q) {
                $q->withoutGlobalScope('tenant')
                  ->where('is_active', true)
                  ->where(function($query) {
                      $query->where('approval_status', 'approved')
                            ->orWhere(function($subq) {
                                $subq->where('user_id', auth()->id())
                                     ->where('approval_status', 'pending');
                            });
                  })
                  ->with(['group', 'options']);
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

        // Group attributes using mapGroupSection
        $grouped = $attributes->groupBy(function($attr) {
            return $this->mapGroupSection($attr->group?->name);
        });

        // Ensure we order them strictly: Basic, Technical, Packaging, Compliance, Commercial
        $orderedGroups = [
            'Basic Details',
            'Technical Specifications',
            'Packaging Details',
            'Compliance & Safety',
            'Commercial Details'
        ];

        $result = [];
        foreach ($orderedGroups as $groupName) {
            if (!$grouped->has($groupName)) {
                continue;
            }
            $attrs = $grouped[$groupName];
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
                    'approval_status' => $attr->approval_status,
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
        $catIds = $request->category_id;
        $query = Subcategory::where('subscriber_id', auth()->id());
        
        if (is_array($catIds)) {
            $query->whereIn('category_id', $catIds);
        } else {
            $query->where('category_id', $catIds);
        }
        
        $subcategories = $query->orderBy('name')->get(['id', 'name']);
        return response()->json($subcategories);
    }

    // AJAX: get product types (child categories)
    public function getProductTypes(Request $request)
    {
        $types = \App\Models\ChildCategory::where('subcategory_id', $request->subcategory_id)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($types);
    }

    /**
     * Map any group name into one of the 5 standard sections strictly.
     */
    private function mapGroupSection(?string $groupName): string
    {
        if (!$groupName) {
            return 'Basic Details';
        }
        
        $name = strtolower(trim($groupName));
        
        if (str_contains($name, 'basic') || str_contains($name, 'general') || str_contains($name, 'overview') || str_contains($name, 'primary')) {
            return 'Basic Details';
        }
        if (str_contains($name, 'tech') || str_contains($name, 'spec') || str_contains($name, 'feature') || str_contains($name, 'detail') || str_contains($name, 'performance')) {
            return 'Technical Specifications';
        }
        if (str_contains($name, 'pack') || str_contains($name, 'box') || str_contains($name, 'shipping') || str_contains($name, 'dimension')) {
            return 'Packaging Details';
        }
        if (str_contains($name, 'compliance') || str_contains($name, 'safety') || str_contains($name, 'cert') || str_contains($name, 'standard') || str_contains($name, 'legal')) {
            return 'Compliance & Safety';
        }
        if (str_contains($name, 'commercial') || str_contains($name, 'price') || str_contains($name, 'cost') || str_contains($name, 'sale') || str_contains($name, 'sell') || str_contains($name, 'trade') || str_contains($name, 'vendor')) {
            return 'Commercial Details';
        }
        
        return 'Technical Specifications';
    }
 
    public function importPage()
    {
        return view('subscriber-panel.products.import');
    }
 
    public function import(Request $request)
    {
        if ($request->input('confirm') == 1) {
            return $this->confirmImport($request);
        }
 
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,csv,txt|max:51200',
        ]);
 
        $file = $request->file('excel');
        $extension = strtolower($file->getClientOriginalExtension());
        
        $tempId = \Illuminate\Support\Str::random(12);
        $tempDirName = 'uploads/temp/products/' . $tempId;
        $tempPath = public_path($tempDirName);
        \Illuminate\Support\Facades\File::ensureDirectoryExists($tempPath);
 
        // Store file temporarily
        $fileName = 'import_' . \Illuminate\Support\Str::random(10) . '.' . $extension;
        $file->storeAs('imports/temp', $fileName, 'local');
        $storedFilePath = 'imports/temp/' . $fileName;
        $absoluteFilePath = \Illuminate\Support\Facades\Storage::disk('local')->path($storedFilePath);
 
        $extractedImages = [];
        if ($extension === 'xlsx') {
            $extractedImages = \App\Services\ExcelImageExtractor::extract($absoluteFilePath, $tempPath);
        }
 
        // Open spreadsheet to read rows for preview
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($absoluteFilePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($storedFilePath);
            \Illuminate\Support\Facades\File::deleteDirectory($tempPath);
            return response()->json([
                'success' => false,
                'message' => 'Failed to parse Excel file: ' . $e->getMessage()
            ], 422);
        }
 
        $previewRows = [];
        $summary = [
            'total' => 0,
            'valid' => 0,
            'error' => 0,
        ];
 
        // Limits Check
        $user = auth()->user();
        $sub = $user?->activeSubscription();
        $limit = $sub?->plan?->product_limit ?? 1000;
        $currCount = SubscriberProduct::whereNull('deleted_at')
            ->where('user_id', $user->id)
            ->count();
 
        $firstRow = true;
        foreach ($rows as $rowIndex => $row) {
            // Skip header row
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
 
            // Skip empty rows
            $rowValues = array_filter($row);
            if (empty($rowValues)) {
                continue;
            }
 
            $name             = trim($row['A'] ?? '');
            $sku              = trim($row['B'] ?? '');
            $slug             = trim($row['C'] ?? '');
            $partCode         = trim($row['D'] ?? '');  // Part Code (col D)
            $partNumber       = trim($row['E'] ?? '');  // Part Number (col E)
            $brand            = trim($row['F'] ?? '');
            $category         = trim($row['G'] ?? '');
            $subcategory      = trim($row['H'] ?? '');
            $mrpVal           = trim($row['I'] ?? '');
            $offerPriceVal    = trim($row['J'] ?? '');
            $moqVal           = trim($row['K'] ?? '');
            $stockVal         = trim($row['L'] ?? '');
            $stockStatusVal   = trim($row['M'] ?? '');
            $shortDesc        = trim($row['N'] ?? '');
            $fullDesc         = trim($row['O'] ?? '');
            $statusVal        = trim($row['P'] ?? '');
            $featuredVal      = trim($row['Q'] ?? '');
            $featuredImageVal = trim($row['R'] ?? '');
            $gallery1Val      = trim($row['S'] ?? '');
            $gallery2Val      = trim($row['T'] ?? '');
            $gallery3Val      = trim($row['U'] ?? '');
            $tags             = trim($row['V'] ?? '');
            $weightVal        = trim($row['W'] ?? '');  // Weight (col W)
            $colorsVal        = trim($row['X'] ?? '');  // Colors (col X)
            $sizesVal         = trim($row['Y'] ?? '');  // Sizes (col Y)
            $metaTitle        = trim($row['Z'] ?? '');
            $metaDescription  = trim($row['AA'] ?? '');
            $metaKeywords     = trim($row['AB'] ?? ''); // Meta Keywords (col AB)
 
            // Row drawing checks (Embedded cell images) — Featured Image now col R
            $featuredImageSrc = '';
            if (isset($extractedImages["R_{$rowIndex}"])) {
                $featuredImageSrc = asset($tempDirName . '/' . $extractedImages["R_{$rowIndex}"]);
            } elseif ($featuredImageVal !== '') {
                $featuredImageSrc = $featuredImageVal;
            }
 
            // Gallery images preview sources — Gallery cols S, T, U
            $gallerySrcs = [];
            foreach (['S', 'T', 'U'] as $col) {
                if (isset($extractedImages["{$col}_{$rowIndex}"])) {
                    $gallerySrcs[] = asset($tempDirName . '/' . $extractedImages["{$col}_{$rowIndex}"]);
                }
            }
            foreach ([$gallery1Val, $gallery2Val, $gallery3Val] as $gVal) {
                if ($gVal !== '' && filter_var($gVal, FILTER_VALIDATE_URL)) {
                    $gallerySrcs[] = $gVal;
                }
            }
 
            $errors = [];
 
            // Required validations
            if ($name === '') {
                $errors[] = 'Product Name is required.';
            }
            if ($sku === '') {
                $errors[] = 'SKU is required.';
            }
 
            // Check if product exists (Upsert mode)
            $action = 'Insert';
            if ($sku !== '') {
                $exists = SubscriberProduct::whereNull('deleted_at')
                    ->where('user_id', $user->id)
                    ->where(function ($query) use ($sku, $name) {
                        $query->where('sku', $sku)
                            ->orWhere('name', $name);
                    })
                    ->first();
                if ($exists) {
                    $action = 'Update';
                }
            } elseif ($name !== '') {
                $exists = SubscriberProduct::whereNull('deleted_at')
                    ->where('user_id', $user->id)
                    ->where('name', $name)
                    ->first();
                if ($exists) {
                    $action = 'Update';
                }
            }

            // Category existence check
            $firstCatId = null;
            $firstCatName = null;
            if ($category !== '') {
                $categoryNames = array_filter(array_map('trim', explode(',', $category)));
                foreach ($categoryNames as $cName) {
                    $cat = \App\Models\Category::withoutGlobalScope('tenant')
                        ->whereNull('deleted_at')
                        ->where('subscriber_id', $user->id)
                        ->whereRaw('LOWER(name) = ?', [strtolower($cName)])
                        ->first();
                    if ($cat) {
                        if ($firstCatId === null) {
                            $firstCatId = $cat->id;
                            $firstCatName = $cName;
                        }
                    }
                }
            }

            // Subcategory existence check
            if ($subcategory !== '') {
                $subcatNames = array_filter(array_map('trim', explode(',', $subcategory)));
                foreach ($subcatNames as $sName) {
                    $subQuery = \App\Models\Subcategory::withoutGlobalScope('tenant')
                        ->whereNull('deleted_at')
                        ->where('subscriber_id', $user->id);
                    if ($firstCatId !== null) {
                        $subQuery->where('category_id', $firstCatId);
                    }
                    $sub = $subQuery->whereRaw('LOWER(name) = ?', [strtolower($sName)])
                        ->first();
                    if ($sub) {
                        if ($firstCatId === null) {
                            $firstCatId = $sub->category_id;
                            $firstCatName = $sub->category?->name;
                        }
                    }
                }
            }

            $mrp = null;
            if ($mrpVal !== '') {
                $mrpClean = preg_replace('/[^0-9.]/', '', $mrpVal);
                $mrp = is_numeric($mrpClean) ? (float)$mrpClean : null;
            }

            $offerPrice = null;
            if ($offerPriceVal !== '') {
                $opClean = preg_replace('/[^0-9.]/', '', $offerPriceVal);
                $offerPrice = is_numeric($opClean) ? (float)$opClean : null;
            }

            $moq = 1;
            if ($moqVal !== '') {
                $moqClean = preg_replace('/[^0-9]/', '', $moqVal);
                $moq = is_numeric($moqClean) ? (int)$moqClean : 1;
            }

            $stock = 0;
            if ($stockVal !== '') {
                $stockClean = preg_replace('/[^0-9]/', '', $stockVal);
                $stock = is_numeric($stockClean) ? (int)$stockClean : 0;
            }
 
            if ($action === 'Insert' && $currCount + $summary['valid'] >= $limit) {
                $errors[] = "Subscription limit of max {$limit} products reached.";
            }
 
            $hasError = count($errors) > 0;
            $summary['total']++;
            if ($hasError) {
                $summary['error']++;
            } else {
                $summary['valid']++;
            }
 
            $previewRows[] = [
                'row' => $rowIndex,
                'name' => $name,
                'sku' => $sku,
                'slug' => $slug ?: \Illuminate\Support\Str::slug($name),
                'brand' => $brand,
                'category' => $category,
                'subcategory' => $subcategory,
                'mrp' => $mrp,
                'offer_price' => $offerPrice,
                'price' => $offerPrice ?: $mrp,
                'discount_price' => $offerPrice,
                'moq' => $moq,
                'stock' => $stock,
                'stock_status' => $stockStatusVal,
                'tax_type' => '',
                'tax_percentage' => null,
                'weight' => '',
                'short_description' => $shortDesc,
                'full_description' => $fullDesc,
                'status' => $statusVal,
                'featured' => $featuredVal,
                'featured_image' => $featuredImageSrc,
                'gallery_images' => $gallerySrcs,
                'colors' => '',
                'sizes' => '',
                'tags' => $tags,
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'errors' => $errors,
                'is_valid' => !$hasError,
                'action' => $action,
            ];
        }
 
        return response()->json([
            'success' => true,
            'temp_file_path' => $storedFilePath,
            'temp_id' => $tempId,
            'rows' => $previewRows,
            'summary' => $summary,
        ]);
    }
 
    public function confirmImport(Request $request)
    {
        $request->validate([
            'temp_file_path' => 'required|string',
            'temp_id' => 'required|string',
        ]);
 
        $storedFilePath = $request->input('temp_file_path');
        $tempId = $request->input('temp_id');
 
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($storedFilePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Uploaded Excel file session has expired. Please upload the file again.'
            ], 422);
        }
 
        // Create import log
        $log = ProductImportLog::create([
            'user_id' => auth()->id(),
            'scope' => 'subscriber',
            'filename' => basename($storedFilePath),
            'status' => 'pending',
            'errors' => [],
        ]);
 
        // Copy temporary excel to job imports folder
        $base = 'imports/' . $log->id;
        \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory($base);
        \Illuminate\Support\Facades\Storage::disk('local')->copy($storedFilePath, $base . '/products.xlsx');
 
        // Copy drawings to job images folder
        $tempPath = public_path('uploads/temp/products/' . $tempId);
        $jobImagesPath = \Illuminate\Support\Facades\Storage::disk('local')->path($base . '/images');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($jobImagesPath);
 
        if (is_dir($tempPath)) {
            $files = \Illuminate\Support\Facades\File::files($tempPath);
            foreach ($files as $file) {
                \Illuminate\Support\Facades\File::copy($file->getRealPath(), $jobImagesPath . '/' . $file->getFilename());
            }
            \Illuminate\Support\Facades\File::deleteDirectory($tempPath);
        }
 
        // Delete temp uploaded excel
        \Illuminate\Support\Facades\Storage::disk('local')->delete($storedFilePath);
 
        // Dispatch background queue job
        \App\Jobs\SubscriberProductImportJob::dispatch($log->id, auth()->id());
 
        // Automatically start a background worker to process the job
        $artisanPath = base_path('artisan');
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                if (function_exists('popen')) {
                    @pclose(@popen("start /B php \"$artisanPath\" queue:work --stop-when-empty", "r"));
                } else {
                    \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]);
                }
            } else {
                if (function_exists('exec')) {
                    @exec("php \"$artisanPath\" queue:work --stop-when-empty > /dev/null 2>&1 &");
                } else {
                    \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Artisan queue worker failed to start: ' . $e->getMessage());
        }
 
        return response()->json([
            'success' => true,
            'import_log_id' => $log->id,
        ]);
    }
 
    public function importStatus(string $id)
    {
        $log = $this->importLogQuery()->findOrFail($id);
        $processed = $log->imported_rows + ($log->updated_rows ?? 0) + $log->skipped_rows + ($log->failed_rows ?? 0);
        $percent = $log->total_rows > 0
            ? (int) min(100, round(($processed / $log->total_rows) * 100))
            : null;
 
        return response()->json([
            'id' => $log->id,
            'status' => $log->status,
            'total_rows' => $log->total_rows,
            'imported_rows' => $log->imported_rows,
            'updated_rows' => $log->updated_rows ?? 0,
            'skipped_rows' => $log->skipped_rows,
            'failed_rows' => $log->failed_rows ?? 0,
            'warning_rows' => $log->warning_rows ?? 0,
            'errors' => $log->errors ?? [],
            'detailed_logs' => $log->detailed_logs ?? [],
            'percent' => $percent,
            'started_at' => $log->started_at,
            'completed_at' => $log->completed_at,
        ]);
    }
 
    public function importLogs()
    {
        $logs = $this->importLogQuery()->latest()->paginate(15);
        return view('subscriber-panel.products.import_logs', compact('logs'));
    }
 
    public function importLogShow($id)
    {
        $log = $this->importLogQuery()->findOrFail($id);
        return view('subscriber-panel.products.import_log_show', compact('log'));
    }

    public function downloadImportErrors($id)
    {
        $log = $this->importLogQuery()->findOrFail($id);
        $detailedLogs = is_array($log->detailed_logs) ? $log->detailed_logs : json_decode($log->detailed_logs, true) ?? [];
        
        $failedRows = array_filter($detailedLogs, function($item) {
            return ($item['status'] ?? '') === 'failed';
        });

        if (empty($failedRows)) {
            return back()->with('error', 'No failed records found in this import log.');
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="import_errors_' . $log->id . '_' . date('Ymd_His') . '.csv"',
        ];

        $callback = function() use ($failedRows) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Row Number', 'SKU', 'Product Name', 'Category', 'Subcategory', 'Failure Reason']);
            
            foreach ($failedRows as $row) {
                fputcsv($file, [
                    $row['row'] ?? '',
                    $row['part_code'] ?? '',
                    $row['product_name'] ?? '',
                    $row['category'] ?? '',
                    $row['subcategory'] ?? '',
                    $row['message'] ?? '',
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
 
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
 
        $headers = [
            'Product Name',
            'SKU',
            'Slug',
            'Part Code',
            'Part Number',
            'Brand',
            'Category',
            'Subcategory',
            'MRP',
            'Offer Price',
            'MOQ',
            'Stock Quantity',
            'Stock Status',
            'Short Description',
            'Full Description',
            'Status',
            'Featured',
            'Featured Image',
            'Gallery Image 1',
            'Gallery Image 2',
            'Gallery Image 3',
            'Tags',
            'Weight',
            'Colors',
            'Sizes',
            'Meta Title',
            'Meta Description',
            'Meta Keywords',
        ];
 
        $samples = [
            [
                'Elite Leather Watch',
                'ELITE-WATCH-01',
                'elite-leather-watch',
                '',
                '',
                'Titan',
                'Fashion Accessories',
                'Watches',
                5000.00,
                4500.00,
                2,
                150,
                'in_stock',
                'Classic leather strap analogue watch.',
                'A premium classic watch featuring a genuine leather strap, quartz movement, and water resistance up to 50 meters.',
                'active',
                'yes',
                'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=600&q=80',
                '',
                '',
                '',
                'watch, leather, premium, accessories',
                '',
                '',
                '',
                'Elite Leather Watch - Premium Accessories',
                'Shop elite leather watches online at the best prices.',
                '',
            ],
            [
                'Ergonomic Office Chair',
                'ERG-CHAIR-02',
                'ergonomic-office-chair',
                '',
                '',
                'Featherlite, Steelcase',
                'Furniture',
                'Chairs',
                12000.00,
                9999.00,
                5,
                80,
                'in_stock',
                'Comfortable ergonomic office chair.',
                'High-back ergonomic office chair with adjustable lumbar support, armrests, and synchro-tilt mechanism.',
                'active',
                'no',
                'https://images.unsplash.com/photo-1505797149-43b0069ec26b?auto=format&fit=crop&w=600&q=80',
                'https://images.unsplash.com/photo-1580481072645-022f9a6dbf27?auto=format&fit=crop&w=600&q=80',
                '',
                '',
                'chair, office, ergonomic, furniture',
                '',
                '',
                '',
                'Ergonomic Office Chair - Dual Brand',
                'Premium ergonomic chairs from top brands like Featherlite and Steelcase.',
                '',
            ],
            [
                'Noise Cancelling Headphones',
                'ANC-HEAD-03',
                'noise-cancelling-headphones',
                '',
                '',
                'Sony, Bose',
                'Electronics, Audio Devices',
                'Headphones',
                29999.00,
                24999.00,
                1,
                120,
                'in_stock',
                'Wireless ANC over-ear headphones.',
                'Industry-leading noise cancelling wireless headphones with 30-hour battery life and quick charging.',
                'active',
                'yes',
                'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
                '',
                '',
                '',
                'headphones, noise cancelling, electronics, audio',
                '',
                '',
                '',
                'Noise Cancelling Headphones - Electronics',
                'Discover top noise cancelling headphones from Sony and Bose.',
                '',
            ],
            [
                'Professional Sports Duffel Bag',
                'SPORT-DUF-04',
                'professional-sports-duffel-bag',
                '',
                '',
                'Nike, Adidas',
                'Sports Equipment, Travel Gear',
                'Gym Bags, Travel Duffle Bags',
                3500.00,
                2900.00,
                10,
                250,
                'in_stock',
                'Durable water-resistant sports duffel.',
                'Large capacity gym and travel bag with dedicated shoe compartment and wet pocket.',
                'active',
                'yes',
                'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=600&q=80',
                '',
                '',
                '',
                'duffel, gym bag, travel bag, nike, adidas',
                '',
                '',
                '',
                'Professional Sports Duffel Bag',
                'High-grade sports and travel duffel bags from Nike and Adidas.',
                '',
            ],
            [
                'Smart Fitness Tracker',
                'FIT-TRACK-05',
                'smart-fitness-tracker',
                '',
                '',
                'Fitbit',
                'Electronics',
                'Wearables',
                '',
                4999.00,
                5,
                300,
                'in_stock',
                'Heart rate and sleep tracking smart band.',
                'Waterproof fitness tracker with continuous heart rate monitoring, sleep analysis, and 7-day battery life.',
                'active',
                'no',
                'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?auto=format&fit=crop&w=600&q=80',
                '',
                '',
                '',
                'fitness, tracker, band, wearable',
                '',
                '',
                '',
                'Smart Fitness Tracker',
                'Stay active with the latest smart fitness tracker.',
                '',
            ],
            [
                'Gourmet Coffee Blend',
                'COFFEE-BLEND-06',
                'gourmet-coffee-blend',
                '',
                '',
                'Blue Tokai',
                'Beverages',
                'Coffee',
                650.00,
                '',
                20,
                500,
                'in_stock',
                'Medium roast 100% Arabica ground coffee.',
                'Freshly roasted single-origin Arabica coffee beans with chocolate and caramel tasting notes.',
                'active',
                'yes',
                'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=600&q=80',
                '',
                '',
                '',
                'coffee, arabica, beverage, fresh roast',
                '',
                '',
                '',
                'Gourmet Coffee Blend - Blue Tokai',
                'Experience the finest medium roast Arabica coffee beans.',
                '',
            ],
            [
                'Stainless Steel Water Bottle',
                'STEEL-BOTTLE-07',
                'stainless-steel-water-bottle',
                '',
                '',
                'Milton',
                'Kitchenware',
                'Bottles',
                999.00,
                850.00,
                50,
                1000,
                'in_stock',
                'Double-walled vacuum insulated bottle.',
                '',
                'active',
                'no',
                'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=600&q=80',
                '',
                '',
                '',
                'bottle, stainless steel, kitchenware',
                '',
                '',
                '',
                'Stainless Steel Water Bottle',
                'Keep your drinks hot or cold for 24 hours.',
                '',
            ],
            [
                'Minimalist Wireless Mouse',
                'WIRELESS-MOUSE-08',
                'minimalist-wireless-mouse',
                '',
                '',
                'Logitech',
                'Electronics',
                'Computer Accessories',
                1299.00,
                999.00,
                10,
                450,
                'in_stock',
                'Ultra-quiet slim wireless optical mouse.',
                'Sleek and compact wireless mouse with silent clicking, high precision tracking, and Bluetooth/USB receiver connectivity.',
                'active',
                'no',
                '',
                '',
                '',
                '',
                'mouse, wireless, computer accessories, logitech',
                '',
                '',
                '',
                'Minimalist Wireless Mouse',
                'Silent wireless mouse with comfortable design.',
                '',
            ],
            [
                'Organic Cotton T-Shirt',
                'COTTON-TEE-09',
                'organic-cotton-t-shirt',
                '',
                '',
                'Zara',
                'Apparel',
                'T-Shirts',
                1499.00,
                1199.00,
                15,
                200,
                'in_stock',
                'Eco-friendly organic cotton tee.',
                'Crafted from 100% certified organic cotton. Features a relaxed fit, crew neck, and breathable fabric ideal for daily wear.',
                'active',
                'no',
                'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=600&q=80',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Organic Cotton T-Shirt - Zara',
                'Eco-friendly premium organic cotton tees.',
                '',
            ],
            [
                'Portable Power Bank',
                'PORT-POWER-10',
                '',
                '',
                '',
                'Xiaomi',
                'Electronics',
                '',
                1999.00,
                '',
                5,
                0,
                'out_of_stock',
                '',
                '10000mAh high capacity fast charging power bank with dual USB outputs.',
                'active',
                'yes',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ]
        ];
 
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray($samples, null, 'A2');
 
        $lastCol = 'AB';
        $sheet->getStyle('A1:'.$lastCol.'1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);
 
        // Highlight image columns (Featured Image = R, Gallery = S, T, U)
        $sheet->getStyle('R1:U1')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '06B6D4'],
            ],
        ]);
 
        for ($col = 1; $col <= 28; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
 
        $path = tempnam(sys_get_temp_dir(), 'product-import-template');
        if ($path === false) {
            abort(500, 'Could not create temporary file.');
        }
 
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
 
        return response()->download($path, 'product-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
 
    public function export()
    {
        $filename = 'products-export-'.date('Y-m-d-His').'.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SubscriberProductsExport, $filename);
    }

    private function importLogQuery()
    {
        return ProductImportLog::where('scope', 'subscriber')
            ->where('user_id', auth()->id());
    }
}

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
            'category_id'       => 'required|exists:categories,id',
            'subcategory_id'    => 'required|exists:subcategories,id',
            'child_category_id' => 'nullable|exists:child_categories,id',
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
        $productTypes = $product->subcategory_id ? \App\Models\ChildCategory::where('subcategory_id', $product->subcategory_id)->get() : collect();
        $attributes = Attribute::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['group', 'options'])
            ->orderBy('sort_order')
            ->get();

        $existingValues = $product->attributeValues->keyBy('attribute_id');

        return view('subscriber-panel.products.edit', compact('product', 'categories', 'subcategories', 'productTypes', 'attributes', 'existingValues'));
    }

    public function update(Request $request, SubscriberProduct $product)
    {
        $this->authorizeSubscriberProduct($product);

        $request->validate([
            'name'              => 'required|string|max:255',
            'category_id'       => 'required|exists:categories,id',
            'subcategory_id'    => 'required|exists:subcategories,id',
            'child_category_id' => 'nullable|exists:child_categories,id',
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
                $q->where('is_active', true)
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
    public function getSubcategoryAttributes(Request $request, $subcategoryId)
    {
        $subcatAttrs = \App\Models\SubcategoryAttribute::where('subcategory_id', $subcategoryId)
            ->with(['attribute' => function($q) {
                $q->where('is_active', true)
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
        $subcategories = Subcategory::where('category_id', $request->category_id)
            ->orderBy('name')
            ->get(['id', 'name']);
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
        $tempDirName = 'products/temp/' . $tempId;
        $tempPath = storage_path('app/public/' . $tempDirName);
        \Illuminate\Support\Facades\File::ensureDirectoryExists($tempPath);
 
        // Store file temporarily
        $fileName = 'import_' . \Illuminate\Support\Str::random(10) . '.' . $extension;
        $file->storeAs('imports/temp', $fileName, 'local');
        $storedFilePath = 'imports/temp/' . $fileName;
        $absoluteFilePath = storage_path('app/' . $storedFilePath);
 
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
        $currCount = SubscriberProduct::where('user_id', $user->id)->count();
 
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
 
            $name = trim($row['A'] ?? '');
            $sku = trim($row['B'] ?? '');
            $slug = trim($row['C'] ?? '');
            $category = trim($row['D'] ?? '');
            $subcategory = trim($row['E'] ?? '');
            $mrpVal = trim($row['F'] ?? '');
            $offerPriceVal = trim($row['G'] ?? '');
            $shortDesc = trim($row['H'] ?? '');
            $fullDesc = trim($row['I'] ?? '');
            $statusVal = trim($row['J'] ?? '');
            $featuredVal = trim($row['K'] ?? '');
            $featuredImageVal = trim($row['L'] ?? '');
            $gallery1Val = trim($row['M'] ?? '');
            $gallery2Val = trim($row['N'] ?? '');
            $gallery3Val = trim($row['O'] ?? '');
            $tags = trim($row['P'] ?? '');
 
            // Row drawing checks (Embedded cell images)
            $featuredImageSrc = '';
            if (isset($extractedImages["L_{$rowIndex}"])) {
                $featuredImageSrc = asset('storage/' . $tempDirName . '/' . $extractedImages["L_{$rowIndex}"]);
            } elseif ($featuredImageVal !== '') {
                $featuredImageSrc = $featuredImageVal;
            }
 
            // Gallery images preview sources
            $gallerySrcs = [];
            foreach (['M', 'N', 'O'] as $col) {
                if (isset($extractedImages["{$col}_{$rowIndex}"])) {
                    $gallerySrcs[] = asset('storage/' . $tempDirName . '/' . $extractedImages["{$col}_{$rowIndex}"]);
                }
            }
            foreach ([$gallery1Val, $gallery2Val, $gallery3Val] as $gVal) {
                if ($gVal !== '' && filter_var($gVal, FILTER_VALIDATE_URL)) {
                    $gallerySrcs[] = $gVal;
                }
            }
 
            $errors = [];
 
            // Validation Rules
            if ($name === '') {
                $errors[] = 'Product Name is required.';
            }
 
            if ($currCount + $summary['valid'] >= $limit) {
                $errors[] = "Subscription limit of max {$limit} products reached.";
            }
 
            if ($sku !== '') {
                $exists = SubscriberProduct::where('user_id', $user->id)->where('sku', $sku)->first();
                if ($exists && strcasecmp($exists->name, $name) !== 0) {
                    $errors[] = "SKU '{$sku}' already exists for product: '{$exists->name}'.";
                }
            }
 
            $mrp = null;
            if ($mrpVal !== '') {
                $mrpClean = preg_replace('/[^0-9.]/', '', $mrpVal);
                if (!is_numeric($mrpClean)) {
                    $errors[] = 'MRP must be numeric.';
                } else {
                    $mrp = (float)$mrpClean;
                }
            }
 
            $offerPrice = null;
            if ($offerPriceVal !== '') {
                $opClean = preg_replace('/[^0-9.]/', '', $offerPriceVal);
                if (!is_numeric($opClean)) {
                    $errors[] = 'Offer Price must be numeric.';
                } else {
                    $offerPrice = (float)$opClean;
                }
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
                'brand' => '',
                'category' => $category,
                'subcategory' => $subcategory,
                'price' => $offerPrice ?: $mrp,
                'discount_price' => $offerPrice,
                'tax_type' => '',
                'tax_percentage' => null,
                'stock' => 0,
                'weight' => '',
                'short_description' => $shortDesc,
                'full_description' => $fullDesc,
                'status' => $statusVal,
                'featured_image' => $featuredImageSrc,
                'gallery_images' => $gallerySrcs,
                'colors' => '',
                'sizes' => '',
                'tags' => $tags,
                'errors' => $errors,
                'is_valid' => !$hasError,
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
            'filename' => basename($storedFilePath),
            'status' => 'pending',
            'errors' => [],
        ]);
 
        // Copy temporary excel to job imports folder
        $base = 'imports/' . $log->id;
        \Illuminate\Support\Facades\Storage::disk('local')->makeDirectory($base);
        \Illuminate\Support\Facades\Storage::disk('local')->copy($storedFilePath, $base . '/products.xlsx');
 
        // Copy drawings to job images folder
        $tempPath = storage_path('app/public/products/temp/' . $tempId);
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
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B php \"$artisanPath\" queue:work --stop-when-empty", "r"));
        } else {
            exec("php \"$artisanPath\" queue:work --stop-when-empty > /dev/null 2>&1 &");
        }
 
        return response()->json([
            'success' => true,
            'import_log_id' => $log->id,
        ]);
    }
 
    public function importStatus(string $id)
    {
        $log = ProductImportLog::findOrFail($id);
        $processed = $log->imported_rows + $log->skipped_rows + ($log->failed_rows ?? 0);
        $percent = $log->total_rows > 0
            ? (int) min(100, round(($processed / $log->total_rows) * 100))
            : null;
 
        return response()->json([
            'id' => $log->id,
            'status' => $log->status,
            'total_rows' => $log->total_rows,
            'imported_rows' => $log->imported_rows,
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
        $logs = ProductImportLog::latest()->paginate(15);
        return view('subscriber-panel.products.import_logs', compact('logs'));
    }
 
    public function importLogShow($id)
    {
        $log = ProductImportLog::findOrFail($id);
        return view('subscriber-panel.products.import_log_show', compact('log'));
    }
 
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
 
        $headers = [
            'Product Name',
            'SKU',
            'Slug',
            'Category',
            'Subcategory',
            'MRP',
            'Offer Price',
            'Short Description',
            'Full Description',
            'Status',
            'Featured',
            'Featured Image',
            'Gallery Image 1',
            'Gallery Image 2',
            'Gallery Image 3',
            'Tags',
        ];
 
        $sample = [
            'Premium Smart Switch',
            'SMART-SW-001',
            'premium-smart-switch',
            'Home Automation',
            'Smart Switches',
            2499.00,
            1999.00,
            'Sleek, touch-sensitive smart switch with Wi-Fi control.',
            'Upgrade your home lighting with the Premium Smart Switch. Features glass panel, LED backlit touch buttons, remote control via mobile app, and integration with Alexa & Google Assistant.',
            'active',
            'yes',
            'paste_image_or_url_here',
            '',
            '',
            '',
            'smart home, switches, electrical, iot',
        ];
 
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray([$sample], null, 'A2');
 
        $lastCol = 'P';
        $sheet->getStyle('A1:'.$lastCol.'1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);
 
        $sheet->getStyle('L1:O1')->applyFromArray([
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '06B6D4'],
            ],
        ]);
 
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
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
}

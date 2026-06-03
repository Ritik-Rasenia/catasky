<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProductImportJob;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\ProductImage;
use App\Models\ProductImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = $this->productTenantQuery()->with('images')->latest()->get();
        
        $user = auth()->user();
        $isDemo = $user && $user->isDemo();

        $subscriberProducts = ($isDemo || ! $user?->hasRole('Super Admin'))
            ? collect()
            : \App\Models\SubscriberProduct::with(['user.subscriberProfile'])->latest()->get();

        return view('admin.products.index', compact('products', 'subscriberProducts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $brands = Brand::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        
        $subcategories = collect();
        if (old('category_id')) {
            $subcategories = Subcategory::where('category_id', old('category_id'))->where('status', 1)->get();
        }

        return view('admin.products.create', compact('brands', 'categories', 'subcategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    

    public function store(Request $request)
    {
        $request->validate([
            'name'              => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('products', 'name')
                    ->where(fn ($query) => $this->applyProductTenant($query)->whereNull('deleted_at'))
            ],
            'sku'               => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('products', 'sku')
                    ->where(fn ($query) => $this->applyProductTenant($query)->whereNull('deleted_at'))
            ],
            'part_code'         => 'nullable|string|max:255',
            'part_number'       => 'nullable|string|max:255',
            'brand_id'          => 'nullable|array',
            'brand_id.*'        => 'exists:brands,id',
            'category_id'       => 'nullable|array',
            'category_id.*'     => 'exists:categories,id',
            'subcategory_id'    => 'nullable|array',
            'subcategory_id.*'  => 'exists:subcategories,id',
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'short_description' => 'nullable|string',
            'additional_info'   => 'nullable|string',
            'mrp'               => 'nullable|numeric|min:0',
            'offer_price'       => 'nullable|numeric|min:0',
            'price'             => 'nullable|numeric|min:0',
            'moq'               => 'nullable|integer|min:1',
            'tags'              => 'nullable|string',
            'stock'             => 'nullable|integer|min:0',
            'status'            => 'nullable|in:0,1',
            'featured'          => 'nullable|in:0,1',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string',
            'meta_keywords'     => 'nullable|string',
        ]);

        // IMAGE UPLOAD
        $imageName = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time().'_'.$image->getClientOriginalName();
            $image->move(public_path('uploads/products'), $imageName);
        }

        // Handle mrp/offer_price
        $mrp        = $request->filled('mrp') ? $request->mrp : ($request->filled('price') ? $request->price : null);
        $offerPrice = $request->filled('offer_price') ? $request->offer_price : ($request->filled('price') ? $request->price : null);

        // CREATE PRODUCT
        $product = Product::create([
            'brand_id'          => $request->brand_id,
            'subscriber_id'     => $this->productTenantId(),
            'category_id'       => $request->category_id,
            'subcategory_id'    => $request->subcategory_id,
            'child_category_id' => null,
            'name'              => $request->name,
            'slug'              => Str::slug($request->name).'-'.Str::random(4),
            'sku'               => $request->sku,
            'part_code'         => $request->part_code,
            'part_number'       => $request->part_number,
            'thumbnail'         => $imageName,
            'short_description' => $request->short_description,
            'additional_info'   => $request->additional_info,
            'mrp'               => $mrp,
            'offer_price'       => $offerPrice,
            'price'             => $offerPrice ?? $mrp,
            'moq'               => $request->moq,
            'stock'             => $request->input('stock', 0),
            'tags'              => $request->tags,
            'featured'          => $request->featured ?? 0,
            'status'            => $request->status ?? 1,
            'meta_title'        => $request->meta_title,
            'meta_description'  => $request->meta_description,
            'meta_keywords'     => $request->meta_keywords,
        ]);

        // MULTIPLE IMAGES UPLOAD
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $multiImageName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/products/gallery'), $multiImageName);
                ProductImage::create(['product_id' => $product->id, 'image' => $multiImageName]);
            }
        }

        // Dynamic Attributes
        if ($request->has('attributes')) {
            $specs = [];
            foreach ($request->input('attributes', []) as $attrId => $val) {
                if ($val !== null && $val !== '') {
                    $attr = \App\Models\Attribute::find($attrId);
                    if ($attr) {
                        $specs[$attr->name] = is_array($val) ? json_encode($val) : $val;
                    }
                }
            }
            $product->update(['specifications' => json_encode($specs)]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product Created Successfully');
    }

        /**
         * Display the specified resource.
         */
        public function show(string $id)
        {
            $product = $this->productTenantQuery()->findOrFail($id);
            return view('admin.products.show', compact('product'));
        }

        /**
         * Show the form for editing the specified resource.
         */
        public function edit(string $id)
        {
            $product = $this->productTenantQuery()->findOrFail($id);
            $brands = Brand::where('status', 1)->get();
            $categories = Category::where('status', 1)->get();

            // Load subcategories based on current product's category_id (array)
            $categoryIds = is_array($product->category_id) ? $product->category_id : [$product->category_id];
            $firstCatId  = old('category_id', $categoryIds[0] ?? null);
            $subcategories = $firstCatId
                ? Subcategory::where('category_id', $firstCatId)->where('status', 1)->get()
                : collect();

            // Decode specifications for dynamic attributes mapping
            $specifications = json_decode($product->specifications, true) ?: [];
            $existingValues = [];
            foreach ($specifications as $name => $value) {
                $attr = \App\Models\Attribute::where('name', $name)->first();
                if ($attr) {
                    $existingValues[$attr->id] = (object)['attribute_id' => $attr->id, 'value' => $value];
                }
            }
            $existingValues = collect($existingValues);

            return view('admin.products.edit', compact('product', 'brands', 'categories', 'subcategories', 'existingValues'));
        }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = $this->productTenantQuery()->findOrFail($id);

        $request->validate([
            'name'              => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('products', 'name')
                    ->ignore($product->id)
                    ->where(fn ($query) => $this->applyProductTenant($query))
                    ->whereNull('deleted_at')
            ],
            'sku'               => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('products', 'sku')
                    ->ignore($product->id)
                    ->where(fn ($query) => $this->applyProductTenant($query))
                    ->whereNull('deleted_at')
            ],
            'part_code'         => 'nullable|string|max:255',
            'part_number'       => 'nullable|string|max:255',
            'brand_id'          => 'nullable|array',
            'brand_id.*'        => 'exists:brands,id',
            'category_id'       => 'nullable|array',
            'category_id.*'     => 'exists:categories,id',
            'subcategory_id'    => 'nullable|array',
            'subcategory_id.*'  => 'exists:subcategories,id',
            'image'             => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'images.*'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'short_description' => 'nullable|string',
            'additional_info'   => 'nullable|string',
            'mrp'               => 'nullable|numeric|min:0',
            'offer_price'       => 'nullable|numeric|min:0',
            'price'             => 'nullable|numeric|min:0',
            'moq'               => 'nullable|integer|min:1',
            'tags'              => 'nullable|string',
            'stock'             => 'nullable|integer|min:0',
            'status'            => 'nullable|in:0,1',
            'featured'          => 'nullable|in:0,1',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string',
            'meta_keywords'     => 'nullable|string',
        ]);

        $imageName = $product->thumbnail;
        if ($request->boolean('remove_image')) {
            $this->deletePublicFile('uploads/products/' . $product->thumbnail);
            $imageName = null;
        }
        if ($request->hasFile('image')) {
            $this->deletePublicFile('uploads/products/' . $product->thumbnail);
            $image = $request->file('image');
            $imageName = time().'_'.$image->getClientOriginalName();
            $image->move(public_path('uploads/products'), $imageName);
        }

        $mrp        = $request->filled('mrp') ? $request->mrp : ($request->filled('price') ? $request->price : $product->mrp);
        $offerPrice = $request->filled('offer_price') ? $request->offer_price : ($request->filled('price') ? $request->price : $product->offer_price);

        // UPDATE PRODUCT
        $product->update([
            'brand_id'          => $request->brand_id,
            'category_id'       => $request->category_id,
            'subcategory_id'    => $request->subcategory_id,
            'child_category_id' => null,
            'name'              => $request->name,
            'slug'              => Str::slug($request->name),
            'sku'               => $request->sku,
            'part_code'         => $request->part_code,
            'part_number'       => $request->part_number,
            'thumbnail'         => $imageName,
            'short_description' => $request->short_description,
            'additional_info'   => $request->additional_info,
            'mrp'               => $mrp,
            'offer_price'       => $offerPrice,
            'price'             => $offerPrice ?? $mrp,
            'moq'               => $request->moq,
            'stock'             => $request->input('stock', 0),
            'tags'              => $request->tags,
            'featured'          => $request->featured ?? 0,
            'status'            => $request->status ?? $product->status,
            'meta_title'        => $request->meta_title,
            'meta_description'  => $request->meta_description,
            'meta_keywords'     => $request->meta_keywords,
        ]);

        // MULTIPLE IMAGES UPLOAD
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $multiImageName = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/products/gallery'), $multiImageName);
                ProductImage::create(['product_id' => $product->id, 'image' => $multiImageName]);
            }
        }

        // Dynamic Attributes
        if ($request->has('attributes')) {
            $specs = [];
            foreach ($request->input('attributes', []) as $attrId => $val) {
                if ($val !== null && $val !== '') {
                    $attr = \App\Models\Attribute::find($attrId);
                    if ($attr) {
                        $specs[$attr->name] = is_array($val) ? json_encode($val) : $val;
                    }
                }
            }
            $product->update(['specifications' => json_encode($specs)]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Product Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = $this->productTenantQuery()->with('images')->findOrFail($id);

        if($product->thumbnail && file_exists(public_path('uploads/products/'.$product->thumbnail))){
            unlink(public_path('uploads/products/'.$product->thumbnail));
        }

        foreach ($product->images as $image) {
            if (file_exists(public_path('uploads/products/gallery/' . $image->image))) {
                unlink(public_path('uploads/products/gallery/' . $image->image));
            }
            $image->delete();
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product Deleted Successfully');
    }

    public function deleteImage($id)
    {
        $image = ProductImage::whereHas('product', function ($query) {
            $this->applyProductTenant($query->withoutGlobalScope('tenant'));
        })->findOrFail($id);
        
        if (file_exists(public_path('uploads/products/gallery/' . $image->image))) {
            unlink(public_path('uploads/products/gallery/' . $image->image));
        }

        $image->delete();

        return response()->json(['success' => 'Image Deleted Successfully']);
    }

    public function importPage()
    {
        return view('admin.products.import');
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
        
        $tempId = Str::random(12);
        $tempDirName = 'products/temp/' . $tempId;
        $tempPath = storage_path('app/public/' . $tempDirName);
        File::ensureDirectoryExists($tempPath);

        // Store file temporarily
        $fileName = 'import_' . Str::random(10) . '.' . $extension;
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
            Storage::disk('local')->delete($storedFilePath);
            File::deleteDirectory($tempPath);
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
            $brand = trim($row['D'] ?? '');
            $category = trim($row['E'] ?? '');
            $subcategory = trim($row['F'] ?? '');
            $mrpVal = trim($row['G'] ?? '');
            $offerPriceVal = trim($row['H'] ?? '');
            $moqVal = trim($row['I'] ?? '');
            $stockVal = trim($row['J'] ?? '');
            $stockStatusVal = trim($row['K'] ?? '');
            $shortDesc = trim($row['L'] ?? '');
            $fullDesc = trim($row['M'] ?? '');
            $statusVal = trim($row['N'] ?? '');
            $featuredVal = trim($row['O'] ?? '');
            $featuredImageVal = trim($row['P'] ?? '');
            $gallery1Val = trim($row['Q'] ?? '');
            $gallery2Val = trim($row['R'] ?? '');
            $gallery3Val = trim($row['S'] ?? '');
            $tags = trim($row['T'] ?? '');
            $metaTitle = trim($row['U'] ?? '');
            $metaDescription = trim($row['V'] ?? '');

            // Row drawing checks (Embedded cell images)
            $featuredImageSrc = '';
            if (isset($extractedImages["P_{$rowIndex}"])) {
                $featuredImageSrc = asset('storage/' . $tempDirName . '/' . $extractedImages["P_{$rowIndex}"]);
            } elseif ($featuredImageVal !== '') {
                $featuredImageSrc = $featuredImageVal;
            }

            // Gallery images preview sources
            $gallerySrcs = [];
            foreach (['Q', 'R', 'S'] as $col) {
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

            if ($sku !== '') {
                $exists = $this->productTenantQuery()
                    ->where(function ($query) use ($sku, $name) {
                        $query->where('sku', $sku)
                            ->orWhere('name', $name);
                    })
                    ->first();
                if ($exists) {
                    $errors[] = "Product name or SKU already exists for product: '{$exists->name}'.";
                }
            } elseif ($name !== '' && $this->productTenantQuery()->where('name', $name)->exists()) {
                $errors[] = "Product '{$name}' already exists.";
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

            $moq = 1;
            if ($moqVal !== '') {
                $moqClean = preg_replace('/[^0-9]/', '', $moqVal);
                if (!is_numeric($moqClean)) {
                    $errors[] = 'MOQ must be an integer.';
                } else {
                    $moq = (int)$moqClean;
                }
            }

            $stock = 0;
            if ($stockVal !== '') {
                $stockClean = preg_replace('/[^0-9]/', '', $stockVal);
                if (!is_numeric($stockClean)) {
                    $errors[] = 'Stock Quantity must be an integer.';
                } else {
                    $stock = (int)$stockClean;
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
                'slug' => $slug ?: Str::slug($name),
                'part_code' => $sku,
                'part_number' => null,
                'brand' => $brand,
                'category' => $category,
                'subcategory' => $subcategory,
                'mrp' => $mrp,
                'offer_price' => $offerPrice,
                'price' => $offerPrice ?? $mrp,
                'moq' => $moq,
                'stock' => $stock,
                'stock_status' => $stockStatusVal,
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
                'meta_keywords' => '',
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

        if (!Storage::disk('local')->exists($storedFilePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Uploaded Excel file session has expired. Please upload the file again.'
            ], 422);
        }

        // Create import log
        $log = ProductImportLog::create([
            'user_id' => auth()->id(),
            'scope' => 'admin',
            'filename' => basename($storedFilePath),
            'status' => 'pending',
            'errors' => [],
        ]);

        // Copy temporary excel to job imports folder
        $base = 'imports/' . $log->id;
        Storage::disk('local')->makeDirectory($base);
        Storage::disk('local')->copy($storedFilePath, $base . '/products.xlsx');

        // Copy drawings to job images folder
        $tempPath = storage_path('app/public/products/temp/' . $tempId);
        $jobImagesPath = Storage::disk('local')->path($base . '/images');
        File::ensureDirectoryExists($jobImagesPath);

        if (is_dir($tempPath)) {
            $files = File::files($tempPath);
            foreach ($files as $file) {
                File::copy($file->getRealPath(), $jobImagesPath . '/' . $file->getFilename());
            }
            File::deleteDirectory($tempPath);
        }

        // Delete temp uploaded excel
        Storage::disk('local')->delete($storedFilePath);

        // Dispatch background queue job
        ProductImportJob::dispatch($log->id, $this->productTenantId());

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
        $logs = $this->importLogQuery()->latest()->paginate(15);
        return view('admin.products.import_logs', compact('logs'));
    }

    public function importLogShow($id)
    {
        $log = $this->importLogQuery()->findOrFail($id);
        return view('admin.products.import_log_show', compact('log'));
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Product Name',
            'SKU',
            'Slug',
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
            'Meta Title',
            'Meta Description',
        ];

        $samples = [
            [
                'Elite Leather Watch',
                'ELITE-WATCH-01',
                'elite-leather-watch',
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
                'Elite Leather Watch - Premium Accessories',
                'Shop elite leather watches online at the best prices.'
            ],
            [
                'Ergonomic Office Chair',
                'ERG-CHAIR-02',
                'ergonomic-office-chair',
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
                'Ergonomic Office Chair - Dual Brand',
                'Premium ergonomic chairs from top brands like Featherlite and Steelcase.'
            ],
            [
                'Noise Cancelling Headphones',
                'ANC-HEAD-03',
                'noise-cancelling-headphones',
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
                'Noise Cancelling Headphones - Electronics',
                'Discover top noise cancelling headphones from Sony and Bose.'
            ],
            [
                'Professional Sports Duffel Bag',
                'SPORT-DUF-04',
                'professional-sports-duffel-bag',
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
                'Professional Sports Duffel Bag',
                'High-grade sports and travel duffel bags from Nike and Adidas.'
            ],
            [
                'Smart Fitness Tracker',
                'FIT-TRACK-05',
                'smart-fitness-tracker',
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
                'Smart Fitness Tracker',
                'Stay active with the latest smart fitness tracker.'
            ],
            [
                'Gourmet Coffee Blend',
                'COFFEE-BLEND-06',
                'gourmet-coffee-blend',
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
                'Gourmet Coffee Blend - Blue Tokai',
                'Experience the finest medium roast Arabica coffee beans.'
            ],
            [
                'Stainless Steel Water Bottle',
                'STEEL-BOTTLE-07',
                'stainless-steel-water-bottle',
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
                'Stainless Steel Water Bottle',
                'Keep your drinks hot or cold for 24 hours.'
            ],
            [
                'Minimalist Wireless Mouse',
                'WIRELESS-MOUSE-08',
                'minimalist-wireless-mouse',
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
                'Minimalist Wireless Mouse',
                'Silent wireless mouse with comfortable design.'
            ],
            [
                'Organic Cotton T-Shirt',
                'COTTON-TEE-09',
                'organic-cotton-t-shirt',
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
                'Organic Cotton T-Shirt - Zara',
                'Eco-friendly premium organic cotton tees.'
            ],
            [
                'Portable Power Bank',
                'PORT-POWER-10',
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
                ''
            ]
        ];

        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray($samples, null, 'A2');

        $lastCol = 'V';
        $sheet->getStyle('A1:'.$lastCol.'1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);

        // Style specific helper columns
        $sheet->getStyle('P1:S1')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '06B6D4'],
            ],
        ]);

        for ($col = 1; $col <= 22; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $path = tempnam(sys_get_temp_dir(), 'product-import-template');
        if ($path === false) {
            abort(500, 'Could not create temporary file.');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();

        return response()->download($path, 'product-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function export()
    {
        $filename = 'products-export-'.date('Y-m-d-His').'.xlsx';

        return Excel::download(new ProductsExport($this->productTenantId()), $filename);
    }

    public function destroySubscriberProduct(string $id)
    {
        abort_if(!auth()->user()->can('delete-products') || auth()->user()->isDemo(), 403, 'Unauthorized.');
        $product = \App\Models\SubscriberProduct::findOrFail($id);

        if ($product->thumbnail && file_exists(public_path('uploads/subscriber-products/' . $product->thumbnail))) {
            unlink(public_path('uploads/subscriber-products/' . $product->thumbnail));
        }

        foreach ($product->images as $image) {
            if (file_exists(public_path('uploads/subscriber-products/' . $image->image_path))) {
                unlink(public_path('uploads/subscriber-products/' . $image->image_path));
            }
            $image->delete();
        }

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Subscriber Product Deleted Successfully');
    }

    private function productTenantId(): ?int
    {
        $user = auth()->user();

        return $user && $user->hasRole('Admin') ? $user->id : null;
    }

    private function applyProductTenant($query)
    {
        $tenantId = $this->productTenantId();

        return $tenantId === null
            ? $query->whereNull('subscriber_id')
            : $query->where('subscriber_id', $tenantId);
    }

    private function productTenantQuery()
    {
        return $this->applyProductTenant(Product::withoutGlobalScope('tenant'))->whereNull('deleted_at');
    }

    private function importLogQuery()
    {
        $query = ProductImportLog::where('scope', 'admin');

        return auth()->user()?->hasRole('Super Admin')
            ? $query
            : $query->where('user_id', auth()->id());
    }

    private function deletePublicFile(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $path = public_path($relativePath);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

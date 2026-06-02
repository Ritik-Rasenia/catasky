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
        $products = Product::latest()->get();
        $subscriberProducts = \App\Models\SubscriberProduct::with(['user.subscriberProfile'])->latest()->get();

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
                \Illuminate\Validation\Rule::unique('products', 'name')->whereNull('deleted_at')
            ],
            'sku'               => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('products', 'sku')->whereNull('deleted_at')
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
            $product = Product::findOrFail($id);
            return view('admin.products.show', compact('product'));
        }

        /**
         * Show the form for editing the specified resource.
         */
        public function edit(string $id)
        {
            $product = Product::findOrFail($id);
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
        $product = Product::findOrFail($id);

        $request->validate([
            'name'              => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('products', 'name')
                    ->ignore($product->id)
                    ->whereNull('deleted_at')
            ],
            'sku'               => [
                'required', 'string', 'max:255',
                \Illuminate\Validation\Rule::unique('products', 'sku')
                    ->ignore($product->id)
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
            'status'            => 'nullable|in:0,1',
            'featured'          => 'nullable|in:0,1',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string',
            'meta_keywords'     => 'nullable|string',
        ]);

        $imageName = $product->thumbnail;
        if ($request->hasFile('image')) {
            if ($product->thumbnail && file_exists(public_path('uploads/products/'.$product->thumbnail))) {
                unlink(public_path('uploads/products/'.$product->thumbnail));
            }
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
        $product = Product::findOrFail($id);

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
        $image = ProductImage::findOrFail($id);
        
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
            $priceVal = trim($row['G'] ?? '');
            $discountPriceVal = trim($row['H'] ?? '');
            $taxType = trim($row['I'] ?? '');
            $taxPercentageVal = trim($row['J'] ?? '');
            $stockVal = trim($row['K'] ?? '');
            $weightVal = trim($row['L'] ?? '');
            $shortDesc = trim($row['M'] ?? '');
            $fullDesc = trim($row['N'] ?? '');
            $statusVal = trim($row['O'] ?? '');
            $featuredImageVal = trim($row['P'] ?? '');
            $gallery1Val = trim($row['Q'] ?? '');
            $gallery2Val = trim($row['R'] ?? '');
            $gallery3Val = trim($row['S'] ?? '');
            $colors = trim($row['T'] ?? '');
            $sizes = trim($row['U'] ?? '');
            $tags = trim($row['V'] ?? '');

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
                $exists = Product::where('sku', $sku)->first();
                if ($exists && strcasecmp($exists->name, $name) !== 0) {
                    $errors[] = "SKU '{$sku}' already exists for product: '{$exists->name}'.";
                }
            }

            $price = null;
            if ($priceVal !== '') {
                $priceClean = preg_replace('/[^0-9.]/', '', $priceVal);
                if (!is_numeric($priceClean)) {
                    $errors[] = 'Price must be numeric.';
                } else {
                    $price = (float)$priceClean;
                }
            }

            $discountPrice = null;
            if ($discountPriceVal !== '') {
                $dpClean = preg_replace('/[^0-9.]/', '', $discountPriceVal);
                if (!is_numeric($dpClean)) {
                    $errors[] = 'Discount Price must be numeric.';
                } else {
                    $discountPrice = (float)$dpClean;
                }
            }

            $taxPercentage = null;
            if ($taxPercentageVal !== '') {
                $taxClean = preg_replace('/[^0-9.]/', '', $taxPercentageVal);
                if (!is_numeric($taxClean)) {
                    $errors[] = 'Tax Percentage must be numeric.';
                } else {
                    $taxPercentage = (float)$taxClean;
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
                'brand' => $brand,
                'category' => $category,
                'subcategory' => $subcategory,
                'price' => $price,
                'discount_price' => $discountPrice,
                'tax_type' => $taxType,
                'tax_percentage' => $taxPercentage,
                'stock' => $stock,
                'weight' => $weightVal,
                'short_description' => $shortDesc,
                'full_description' => $fullDesc,
                'status' => $statusVal,
                'featured_image' => $featuredImageSrc,
                'gallery_images' => $gallerySrcs,
                'colors' => $colors,
                'sizes' => $sizes,
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

        if (!Storage::disk('local')->exists($storedFilePath)) {
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
        ProductImportJob::dispatch($log->id);

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
        return view('admin.products.import_logs', compact('logs'));
    }

    public function importLogShow($id)
    {
        $log = ProductImportLog::findOrFail($id);
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
            'Price',
            'Discount Price',
            'Tax Type',
            'Tax Percentage',
            'Stock Quantity',
            'Weight',
            'Short Description',
            'Full Description',
            'Status',
            'Featured Image',
            'Gallery Image 1',
            'Gallery Image 2',
            'Gallery Image 3',
            'Colors',
            'Sizes',
            'Tags',
        ];

        $sample = [
            'Corporate Elite Notebook',
            'ELITE-NOTE-001',
            'corporate-elite-notebook',
            'Acme Stationery',
            'Office Supplies',
            'Notebooks',
            150.00,
            120.00,
            'GST',
            18.00,
            500,
            '350g',
            'Premium faux leather binding business notebook.',
            'Fitted with 120 sheets of 80gsm fountain-pen friendly lined paper. Includes silk bookmark, document pocket, and secure elastic band.',
            '1',
            'paste_image_or_url_here',
            '',
            '',
            '',
            'Navy Blue, Slate Grey, Black',
            'A5, A6',
            'office, notebook, premium, leather',
        ];

        $sheet->fromArray([$headers], null, 'A1');
        $sheet->fromArray([$sample], null, 'A2');

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

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
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

        return Excel::download(new ProductsExport, $filename);
    }

    public function destroySubscriberProduct(string $id)
    {
        abort_if(!auth()->user()->can('delete-products'), 403, 'Unauthorized.');
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
}

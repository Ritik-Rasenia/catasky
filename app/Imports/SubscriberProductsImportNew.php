<?php
 
namespace App\Imports;
 
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProductImage;
use App\Models\SubscriberProductVariant;
use App\Models\SubscriberProductAttributeValue;
use App\Models\ProductImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
 
class SubscriberProductsImportNew implements OnEachRow, WithChunkReading, SkipsEmptyRows, WithHeadingRow
{
    private const ERROR_CAP = 400;
 
    private array $categoryCache = [];
    private array $subcategoryCache = [];
    private array $brandCache = [];
 
    public function __construct(
        private int $importLogId,
        private int $subscriberId,
        private string $imagesExtractPath,
        private ?int $subcategoryId = null,
    ) {}
 
    public function chunkSize(): int
    {
        return 100;
    }
 
    public function headingRow(): int
    {
        return 1;
    }
 
    public function onRow(Row $row): void
    {
        $rowIndex = $row->getIndex();
        $data = $row->toArray();
 
        $name = trim($this->getCell($data, 'product_name', 'name'));
        $sku = trim($this->getCell($data, 'sku', 'part_code'));
        $slugInput = trim($this->getCell($data, 'slug'));
 
        if ($name === '') {
            $this->logSkippedRow($rowIndex, $sku, $name, 'Product name is required.', $data);
            return;
        }
 
        // Limits Check
        $user = \App\Models\User::find($this->subscriberId);
        $sub = $user?->activeSubscription();
        $limit = $sub?->plan?->product_limit ?? 1000;
        $currCount = SubscriberProduct::where('user_id', $this->subscriberId)->count();
        if ($currCount >= $limit) {
            $this->logFailedRow($rowIndex, $sku, $name, "Subscription limit reached. Your plan allows max {$limit} products.", $data);
            return;
        }
 
        // Duplicate SKU checking in subscriber workspace
        if ($sku !== '') {
            $existingProduct = SubscriberProduct::where('user_id', $this->subscriberId)
                ->where('sku', $sku)
                ->first();
            if ($existingProduct && strcasecmp($existingProduct->name, $name) !== 0) {
                $this->logSkippedRow($rowIndex, $sku, $name, "Duplicate SKU: '{$sku}' already exists in your catalogue.", $data);
                return;
            }
        } else {
            $sku = 'SKU-' . strtoupper(Str::random(10));
        }
 
        // Resolve categories
        $categoryCell = trim($this->getCell($data, 'category'));
        if ($categoryCell === '') {
            $categoryCell = 'General';
        }
        $categoryNames = array_filter(array_map('trim', explode(',', $categoryCell)));
        $categoryIds = [];
        $firstCategoryId = null;
        foreach ($categoryNames as $cName) {
            $cat = $this->resolveCategory($cName);
            if ($cat) {
                $categoryIds[] = $cat->id;
                if ($firstCategoryId === null) {
                    $firstCategoryId = $cat->id;
                }
            }
        }
        if (empty($categoryIds)) {
            $cat = $this->resolveCategory('General');
            $categoryIds[] = $cat->id;
            $firstCategoryId = $cat->id;
        }

        // Resolve subcategories (scoped to the first category found, or general)
        $subcategoryCell = trim($this->getCell($data, 'subcategory', 'sub_category'));
        if ($subcategoryCell === '') {
            $subcategoryCell = 'General';
        }
        $subcategoryNames = array_filter(array_map('trim', explode(',', $subcategoryCell)));
        $subcategoryIds = [];
        foreach ($subcategoryNames as $sName) {
            $sub = $this->resolveSubcategory($firstCategoryId, $sName);
            if ($sub) {
                $subcategoryIds[] = $sub->id;
            }
        }
        if (empty($subcategoryIds)) {
            $sub = $this->resolveSubcategory($firstCategoryId, 'General');
            $subcategoryIds[] = $sub->id;
        }

        // Resolve Brands
        $brandCell = trim($this->getCell($data, 'brand'));
        $brandIds = [];
        if ($brandCell !== '') {
            $brandNames = array_filter(array_map('trim', explode(',', $brandCell)));
            foreach ($brandNames as $bName) {
                $bId = $this->resolveBrandId($bName);
                if ($bId) {
                    $brandIds[] = $bId;
                }
            }
        }

        // Generate Slug
        $slug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($name);
        if ($slug === '') {
            $slug = Str::slug($name) . '-' . Str::lower(Str::random(6));
        }
        $slug = $this->uniqueSlug($slug, $sku);

        // Handle Thumbnail: Column L (Featured Image)
        $thumbnail = null;
        $thumbWarning = null;

        // Try pre-extracted cell drawing first
        $extractedDrawingPath = $this->getExtractedDrawing($rowIndex, 'L');
        if ($extractedDrawingPath) {
            $thumbnail = $this->copyDrawingToFinal($extractedDrawingPath, $slug, 'thumbnail');
        } else {
            // Check string URL or filename
            $thumbVal = trim($this->getCell($data, 'featured_image', 'thumbnail'));
            if ($thumbVal !== '') {
                if (filter_var($thumbVal, FILTER_VALIDATE_URL)) {
                    $downloaded = $this->downloadImageFromUrl(
                        $thumbVal,
                        public_path('uploads/subscriber-products'),
                        $slug . '_' . time() . '_thumb'
                    );
                    if ($downloaded) {
                        $thumbnail = $downloaded;
                    } else {
                        $thumbWarning = "Failed to download main image from URL: '{$thumbVal}'.";
                    }
                } else {
                    $thumbnail = $thumbVal;
                }
            }
        }

        // Parse fields
        $mrp = $this->parseNumeric($this->getCell($data, 'mrp'));
        $offerPrice = $this->parseNumeric($this->getCell($data, 'offer_price', 'price'));
        $stock = (int)$this->parseNumeric($this->getCell($data, 'stock_quantity', 'stock'), 0);
        $status = $this->parseStatus($this->getCell($data, 'status'));
        $featured = $this->parseBool($this->getCell($data, 'featured'));

        $shortDesc = trim($this->getCell($data, 'short_description'));
        $fullDesc = trim($this->getCell($data, 'full_description', 'description'));
        $tagsStr = trim($this->getCell($data, 'tags'));
        $tagsArray = $tagsStr !== '' ? array_map('trim', explode(',', $tagsStr)) : null;

        try {
            DB::transaction(function () use ($name, $slug, $sku, $brandIds, $categoryIds, $subcategoryIds, $mrp, $offerPrice, $stock, $shortDesc, $fullDesc, $thumbnail, $status, $featured, $tagsArray, $rowIndex, $data) {
                // Create Subscriber Product
                $product = SubscriberProduct::create([
                    'user_id'           => $this->subscriberId,
                    'category_id'       => $categoryIds,
                    'subcategory_id'    => $subcategoryIds,
                    'brand_id'          => $brandIds,
                    'name'              => $name,
                    'slug'              => $slug,
                    'sku'               => $sku,
                    'mrp'               => $mrp > 0 ? $mrp : null,
                    'offer_price'       => $offerPrice > 0 ? $offerPrice : null,
                    'thumbnail'         => $thumbnail,
                    'short_description' => $shortDesc,
                    'full_description'  => $fullDesc,
                    'tags'              => $tagsArray,
                    'featured'          => $featured,
                    'status'            => $status,
                    'approval_status'   => 'approved', // Auto-approved for B2B subscriber
                ]);

                // Initialize default Variant (for stock/inventory)
                SubscriberProductVariant::create([
                    'subscriber_product_id' => $product->id,
                    'variant_sku'           => $product->sku,
                    'price'                 => $product->offer_price ?: ($product->mrp ?: 0.00),
                    'stock'                 => $stock,
                    'status'                => true,
                ]);
 
                // Save dynamic attributes (PIM attributes mapped to subcategory first, falling back to category)
                $subcatIdToUse = $this->subcategoryId ?: ($subcategory?->id);
                $attributes = collect();
                if ($subcatIdToUse) {
                    $subcatAttrs = \App\Models\SubcategoryAttribute::where('subcategory_id', $subcatIdToUse)
                        ->with('attribute')
                        ->get();
                    if ($subcatAttrs->isNotEmpty()) {
                        $attributes = $subcatAttrs->pluck('attribute')->filter();
                    }
                }
                
                if ($attributes->isEmpty()) {
                    $categoryAttributes = \App\Models\CategoryAttribute::where('category_id', $category->id)
                        ->with('attribute')
                        ->get();
                    $attributes = $categoryAttributes->pluck('attribute')->filter();
                }

                foreach ($attributes as $attr) {
                    $slugKey = Str::slug($attr->name);
                    $headingKey = str_replace('-', '_', $slugKey);

                    $val = null;
                    if (isset($data[$headingKey]) && $data[$headingKey] !== '') {
                        $val = $data[$headingKey];
                    } elseif (isset($data[$slugKey]) && $data[$slugKey] !== '') {
                        $val = $data[$slugKey];
                    }

                    if ($val !== null && $val !== '') {
                        SubscriberProductAttributeValue::create([
                            'subscriber_product_id' => $product->id,
                            'attribute_id'          => $attr->id,
                            'value'                 => is_array($val) ? json_encode($val) : (string)$val,
                        ]);
                    }
                }
 
                // Handle Gallery Images (Columns M, N, O)
                $galleryImages = [];
                foreach (['M', 'N', 'O'] as $colIndex => $colLetter) {
                    $drawingPath = $this->getExtractedDrawing($rowIndex, $colLetter);
                    if ($drawingPath) {
                        $stored = $this->copyDrawingToFinal($drawingPath, $slug, 'gallery_' . ($colIndex + 1));
                        if ($stored) {
                            $galleryImages[] = $stored;
                        }
                    }
                }
 
                // Fallback to gallery URLs
                foreach (['gallery_image_1', 'gallery_image_2', 'gallery_image_3'] as $colIndex => $key) {
                    $url = trim($this->getCell($data, $key));
                    if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                        $downloaded = $this->downloadImageFromUrl(
                            $url,
                            public_path('uploads/subscriber-products'),
                            $slug . '_' . time() . '_gallery_' . ($colIndex + 1)
                        );
                        if ($downloaded) {
                            $galleryImages[] = $downloaded;
                        }
                    }
                }
 
                if (!empty($galleryImages)) {
                    foreach ($galleryImages as $index => $gImg) {
                        SubscriberProductImage::create([
                            'subscriber_product_id' => $product->id,
                            'image_path'            => $gImg,
                            'is_primary'            => false,
                            'sort_order'            => $index,
                        ]);
                    }
                }
            });
 
            // Log row level results
            $this->logDetailedRow($rowIndex, $sku, $name, 'imported', 'Product imported successfully.', $data);
 
            if ($thumbWarning) {
                $this->logWarningRow($rowIndex, $sku, $name, $thumbWarning, $data);
            }
 
            $this->applyLogDelta(1, 0, 0, $thumbWarning ? 1 : 0);
        } catch (\Throwable $e) {
            $this->logFailedRow($rowIndex, $sku, $name, 'Database error: ' . $e->getMessage(), $data);
        }
    }
 
    private function getExtractedDrawing(int $rowIndex, string $colLetter): ?string
    {
        $dir = $this->imagesExtractPath;
        if (!is_dir($dir)) {
            return null;
        }
 
        $pattern = $dir . DIRECTORY_SEPARATOR . "row_{$rowIndex}_col_{$colLetter}.*";
        $matches = glob($pattern);
 
        if ($matches && count($matches) > 0) {
            return $matches[0];
        }
 
        return null;
    }
 
    private function copyDrawingToFinal(string $absoluteSourcePath, string $slug, string $type): ?string
    {
        if (!file_exists($absoluteSourcePath)) {
            return null;
        }
 
        $destDir = public_path('uploads/subscriber-products');
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
 
        $extension = pathinfo($absoluteSourcePath, PATHINFO_EXTENSION);
        $newName = $slug . '_' . time() . '_' . $type . '.' . $extension;
        $destPath = $destDir . DIRECTORY_SEPARATOR . $newName;
 
        if (copy($absoluteSourcePath, $destPath)) {
            return $newName;
        }
 
        return null;
    }
 
    private function logSkippedRow(int $rowIndex, string $sku, string $name, string $message, array $row = []): void
    {
        $this->logDetailedRow($rowIndex, $sku, $name, 'skipped', $message, $row);
        $this->applyLogDelta(0, 1, 0, 0);
    }
 
    private function logFailedRow(int $rowIndex, string $sku, string $name, string $message, array $row = []): void
    {
        $this->logDetailedRow($rowIndex, $sku, $name, 'failed', $message, $row);
        $this->applyLogDelta(0, 0, 1, 0);
    }
 
    private function logWarningRow(int $rowIndex, string $sku, string $name, string $message, array $row = []): void
    {
        $this->logDetailedRow($rowIndex, $sku, $name, 'warning', $message, $row);
        $this->applyLogDelta(0, 0, 0, 1);
    }
 
    private function logDetailedRow(int $rowIndex, string $sku, string $productName, string $status, string $message, array $row = []): void
    {
        $log = ProductImportLog::find($this->importLogId);
        if (!$log) return;
 
        $detailedLog = [
            'row'          => $rowIndex,
            'part_code'    => $sku,
            'product_name' => $productName,
            'category'     => trim($this->getCell($row, 'category')),
            'subcategory'  => trim($this->getCell($row, 'subcategory', 'sub_category')),
            'status'       => $status,
            'message'      => $message,
            'timestamp'    => now()->toISOString(),
        ];
 
        $existingLogs = $log->detailed_logs ?? [];
        $existingLogs[] = $detailedLog;
 
        $log->update(['detailed_logs' => $existingLogs]);
    }
 
    private function applyLogDelta(int $imported, int $skipped, int $failed, int $warning): void
    {
        $log = ProductImportLog::find($this->importLogId);
        if (!$log) return;
 
        $log->update([
            'imported_rows' => $log->imported_rows + $imported,
            'skipped_rows'  => $log->skipped_rows + $skipped,
            'failed_rows'   => ($log->failed_rows ?? 0) + $failed,
            'warning_rows'  => ($log->warning_rows ?? 0) + $warning,
        ]);
    }
 
    private function resolveCategory(string $name): ?Category
    {
        $key = Str::lower($name);
        if (array_key_exists($key, $this->categoryCache)) {
            return $this->categoryCache[$key];
        }
        $cat = Category::whereRaw('LOWER(name) = ?', [$key])->first();
        if (!$cat) {
            $cat = Category::create([
                'name'   => $name,
                'slug'   => Str::slug($name),
                'status' => 1
            ]);
        }
        $this->categoryCache[$key] = $cat;
        return $cat;
    }
 
    private function resolveSubcategory(int $categoryId, string $name): ?Subcategory
    {
        $key = $categoryId . '|' . Str::lower($name);
        if (array_key_exists($key, $this->subcategoryCache)) {
            return $this->subcategoryCache[$key];
        }
        $sub = Subcategory::where('category_id', $categoryId)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)])
            ->first();
        if (!$sub) {
            $sub = Subcategory::create([
                'category_id' => $categoryId,
                'name'        => $name,
                'slug'        => Str::slug($name),
                'status'      => 1
            ]);
        }
        $this->subcategoryCache[$key] = $sub;
        return $sub;
    }
 
    private function downloadImageFromUrl(string $url, string $destDir, string $customName): ?string
    {
        $url = trim($url);
        if ($url === '' || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
 
        try {
            $ctx = stream_context_create([
                'http' => [
                    'timeout'         => 15,
                    'follow_location' => true,
                    'user_agent'      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ]
            ]);
            $contents = @file_get_contents($url, false, $ctx);
            if ($contents === false || strlen($contents) < 100) {
                return null;
            }
 
            $extension = 'jpg';
            $pathExt = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (in_array(strtolower($pathExt), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $extension = strtolower($pathExt);
            }
 
            $newName = $customName . '.' . $extension;
 
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }
 
            file_put_contents($destDir . DIRECTORY_SEPARATOR . $newName, $contents);
            return $newName;
        } catch (\Throwable $e) {
            return null;
        }
    }
 
    private function uniqueSlug(string $base, string $sku): string
    {
        $slug = $base;
        $i = 2;
        while (SubscriberProduct::where('slug', $slug)->where('sku', '!=', $sku)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }
        return $slug;
    }
 
    private function parseNumeric(mixed $val, float $default = 0): float
    {
        if ($val === null || $val === '') {
            return $default;
        }
        $clean = preg_replace('/[^0-9.]/', '', str_replace(',', '', (string)$val));
        return is_numeric($clean) ? (float)$clean : $default;
    }
 
    private function parseStatus(mixed $value, string $default = 'draft'): string
    {
        if ($value === null || $value === '') {
            return $default;
        }
        $v = Str::lower(trim((string)$value));
        if (in_array($v, ['active', '1', 'yes', 'y'], true)) {
            return 'active';
        }
        if (in_array($v, ['inactive', '0', 'no', 'n'], true)) {
            return 'inactive';
        }
        return 'draft';
    }
 
    private function parseBool(mixed $value, bool $default = false): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }
        $v = Str::lower(trim((string)$value));
        return in_array($v, ['1', 'true', 'yes', 'y', 'featured', 'active'], true);
    }
 
    private function getCell(array $row, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }
        return null;
    }

    private function resolveBrandId(string $name): ?int
    {
        $key = Str::lower($name);
        if (array_key_exists($key, $this->brandCache)) {
            return $this->brandCache[$key];
        }
        $brand = \App\Models\Brand::whereRaw('LOWER(name) = ?', [$key])->first();
        if (!$brand) {
            $brand = \App\Models\Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => 1
            ]);
        }
        $id = $brand?->id;
        $this->brandCache[$key] = $id;
 
        return $id;
    }
}

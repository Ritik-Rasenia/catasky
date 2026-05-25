<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductImportLog;
use App\Models\Subcategory;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class ProductsImport implements OnEachRow, WithChunkReading, SkipsEmptyRows, WithHeadingRow
{
    private const ERROR_CAP = 400;

    private array $brandCache = [];
    private array $categoryCache = [];
    private array $subcategoryCache = [];

    public function __construct(
        private int $importLogId,
        private string $imagesExtractPath,
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

        // Uniqueness validation
        if ($sku !== '') {
            $existingProduct = Product::where('sku', $sku)->first();
            if ($existingProduct && strcasecmp($existingProduct->name, $name) !== 0) {
                $this->logSkippedRow($rowIndex, $sku, $name, "Duplicate SKU: '{$sku}' already exists for another product.", $data);
                return;
            }
        } else {
            $sku = 'SKU-' . strtoupper(Str::random(10));
        }

        // Resolve category
        $categoryName = trim($this->getCell($data, 'category'));
        if ($categoryName === '') {
            $categoryName = 'General';
        }
        $category = $this->resolveCategory($categoryName);

        // Resolve subcategory
        $subcategoryName = trim($this->getCell($data, 'subcategory', 'sub_category'));
        if ($subcategoryName === '') {
            $subcategoryName = 'General';
        }
        $subcategory = $this->resolveSubcategory($category->id, $subcategoryName);

        // Resolve Brand
        $brandId = null;
        $brandCell = trim($this->getCell($data, 'brand'));
        if ($brandCell !== '') {
            $brandId = $this->resolveBrandId($brandCell);
        }

        // Generate Slug
        $slug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($name);
        if ($slug === '') {
            $slug = Str::slug($name) . '-' . Str::lower(Str::random(6));
        }
        $slug = $this->uniqueSlug($slug, $sku);

        // Handle Image Fields: Featured Image (P)
        $featuredImage = null;
        $featuredWarning = null;

        // Try pre-extracted cell drawing first
        $extractedDrawingPath = $this->getExtractedDrawing($rowIndex, 'P');
        if ($extractedDrawingPath) {
            $featuredImage = $this->copyDrawingToFinal($extractedDrawingPath, $slug, 'featured');
        } else {
            // Check string URL or filename
            $featuredImageVal = trim($this->getCell($data, 'featured_image', 'thumbnail'));
            if ($featuredImageVal !== '') {
                if (filter_var($featuredImageVal, FILTER_VALIDATE_URL)) {
                    $downloaded = $this->downloadImageFromUrl(
                        $featuredImageVal,
                        storage_path('app/public/products'),
                        $slug . '_' . time() . '_featured'
                    );
                    if ($downloaded) {
                        $featuredImage = $downloaded;
                    } else {
                        $featuredWarning = "Failed to download featured image from URL: '{$featuredImageVal}'.";
                    }
                } else {
                    $featuredImage = $featuredImageVal;
                }
            }
        }

        // Resolve fields
        $price = $this->parseNumeric($this->getCell($data, 'price'));
        $salePrice = $this->parseNumeric($this->getCell($data, 'discount_price', 'sale_price'));
        $tax = $this->parseNumeric($this->getCell($data, 'tax_percentage', 'tax'));
        $stock = (int)$this->parseNumeric($this->getCell($data, 'stock_quantity', 'stock'), 0);
        $status = $this->parseBoolStatus($this->getCell($data, 'status'), 1);
        $featured = $this->parseBoolStatus($this->getCell($data, 'featured'), 0);

        $shortDesc = trim($this->getCell($data, 'short_description'));
        $fullDesc = trim($this->getCell($data, 'full_description', 'description'));
        $tags = trim($this->getCell($data, 'tags'));
        $colors = trim($this->getCell($data, 'colors'));
        $sizes = trim($this->getCell($data, 'sizes'));

        // Compile weight, colors, sizes into additional info or specifications
        $specifications = [];
        if ($colors !== '') $specifications['Colors'] = $colors;
        if ($sizes !== '') $specifications['Sizes'] = $sizes;
        if (trim($this->getCell($data, 'weight')) !== '') $specifications['Weight'] = trim($this->getCell($data, 'weight'));
        if (trim($this->getCell($data, 'tax_type')) !== '') $specifications['Tax Type'] = trim($this->getCell($data, 'tax_type'));

        try {
            DB::transaction(function () use ($name, $slug, $sku, $brandId, $category, $subcategory, $price, $salePrice, $tax, $stock, $shortDesc, $fullDesc, $featuredImage, $status, $featured, $specifications, $tags, $rowIndex) {
                // Find existing product by SKU or name
                $product = Product::where('sku', $sku)
                    ->orWhere('name', $name)
                    ->first();

                $attributes = [
                    'brand_id' => $brandId,
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory->id,
                    'name' => $name,
                    'slug' => $product ? $product->slug : $slug,
                    'sku' => $sku,
                    'part_code' => $sku, // backward compatibility
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'tax' => $tax,
                    'stock' => $stock,
                    'short_description' => $shortDesc ?: ($product?->short_description ?? ''),
                    'description' => $fullDesc ?: ($product?->description ?? ''),
                    'full_description' => $fullDesc ?: ($product?->full_description ?? ''),
                    'featured_image' => $featuredImage ?: ($product?->featured_image ?? null),
                    'thumbnail' => $featuredImage ?: ($product?->thumbnail ?? null),
                    'status' => $status,
                    'featured' => $featured,
                    'specifications' => !empty($specifications) ? json_encode($specifications) : null,
                    'tags' => $tags,
                ];

                if ($product) {
                    $product->update($attributes);
                } else {
                    $product = Product::create($attributes);
                }

                // Handle Gallery Images (Q, R, S)
                $galleryImages = [];
                foreach (['Q', 'R', 'S'] as $colIndex => $colLetter) {
                    $drawingPath = $this->getExtractedDrawing($rowIndex, $colLetter);
                    if ($drawingPath) {
                        $stored = $this->copyDrawingToFinal($drawingPath, $slug, 'gallery_' . ($colIndex + 1));
                        if ($stored) {
                            $galleryImages[] = $stored;
                        }
                    }
                }

                // Fallback to gallery URLs from spreadsheet
                foreach (['gallery_image_1', 'gallery_image_2', 'gallery_image_3'] as $colIndex => $key) {
                    $url = trim($this->getCell($data, $key));
                    if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                        $downloaded = $this->downloadImageFromUrl(
                            $url,
                            storage_path('app/public/products'),
                            $slug . '_' . time() . '_gallery_' . ($colIndex + 1)
                        );
                        if ($downloaded) {
                            $galleryImages[] = $downloaded;
                        }
                    }
                }

                if (!empty($galleryImages)) {
                    foreach ($galleryImages as $gImg) {
                        // Avoid duplicates
                        if (!ProductImage::where('product_id', $product->id)->where('image', $gImg)->exists()) {
                            ProductImage::create([
                                'product_id' => $product->id,
                                'image' => $gImg,
                            ]);
                        }
                    }
                }
            });

            // Log detailed row
            $this->logDetailedRow($rowIndex, $sku, $name, 'imported', 'Product imported successfully.', $data);

            if ($featuredWarning) {
                $this->logWarningRow($rowIndex, $sku, $name, $featuredWarning, $data);
            }

            $this->applyLogDelta(1, 0, 0, $featuredWarning ? 1 : 0);
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

        $destDir = storage_path('app/public/products');
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
            'row' => $rowIndex,
            'part_code' => $sku,
            'product_name' => $productName,
            'category' => trim($this->getCell($row, 'category')),
            'subcategory' => trim($this->getCell($row, 'subcategory', 'sub_category')),
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toISOString(),
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
            'skipped_rows' => $log->skipped_rows + $skipped,
            'failed_rows' => ($log->failed_rows ?? 0) + $failed,
            'warning_rows' => ($log->warning_rows ?? 0) + $warning,
        ]);
    }

    private function resolveBrandId(string $name): ?int
    {
        $key = Str::lower($name);
        if (array_key_exists($key, $this->brandCache)) {
            return $this->brandCache[$key];
        }
        $brand = Brand::whereRaw('LOWER(name) = ?', [$key])->first();
        if (!$brand) {
            $brand = Brand::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => 1
            ]);
        }
        $id = $brand?->id;
        $this->brandCache[$key] = $id;

        return $id;
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
                'name' => $name,
                'slug' => Str::slug($name),
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
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => 1
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
                    'timeout' => 15,
                    'follow_location' => true,
                    'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
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
        while (Product::where('slug', $slug)->where('sku', '!=', $sku)->exists()) {
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

    private function parseBoolStatus(mixed $value, int $default = 1): int
    {
        if ($value === null || $value === '') {
            return $default;
        }
        $v = Str::lower(trim((string)$value));
        if (in_array($v, ['1', 'true', 'yes', 'active', 'y', 'featured'], true)) {
            return 1;
        }
        return 0;
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
}

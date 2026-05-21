<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductImportLog;
use App\Models\Solution;
use App\Models\Subcategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class ProductsImport implements OnEachRow, WithChunkReading, SkipsEmptyRows, WithHeadingRow
{
    private const ERROR_CAP = 400;

    /** @var array<string, int|null> */
    private array $brandCache = [];

    /** @var array<string, Category|null> */
    private array $categoryCache = [];

    /** @var array<string, Subcategory|null> */
    private array $subcategoryCache = [];

    /** @var array<string, ChildCategory|null> */
    private array $childCategoryCache = [];

    /** @var array<string, int|null> */
    private array $solutionCache = [];

    /** @var array<string, string>|null */
    private ?array $imageIndex = null;

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

        $name = $this->stringValue($this->getCell($data, 'name', 'product_name'));
        $partCode = $this->stringValue($this->getCell($data, 'part_code', 'product_id', 'sku'));

        if ($name === '') {
            $this->logSkippedRow($rowIndex, $partCode, $name, 'Product name is required.', $data);
            return;
        }

        // Auto-generate part_code if still empty to ensure product has identifier
        if ($partCode === '') {
            $partCode = $this->uniquePartCode();
        } else {
            // Check for duplicate part_code if provided
            $existingProduct = Product::withTrashed()->where('part_code', $partCode)->first();
            if ($existingProduct && $existingProduct->name !== $name) {
                $this->logSkippedRow($rowIndex, $partCode, $name, 'Duplicate part_code: ' . $partCode . ' already exists for another product.', $data);
                return;
            }
        }

        $categoryName = $this->stringValue($this->getCell($data, 'category'));
        if ($categoryName === '') {
            $categoryName = 'General';
        }

        $subName = $this->stringValue($this->getCell($data, 'sub_category', 'subcategory'));
        if ($subName === '') {
            $subName = 'General';
        }

        $category = $this->resolveCategory($categoryName);
        if (! $category) {
            $this->logSkippedRow($rowIndex, $partCode, $name, "UNMATCHED CATEGORY: '{$categoryName}' does not exist in the database.", $data, 'category');
            return;
        }

        $subcategory = null;

        if ($subName !== '') {
            $subcategory = $this->resolveSubcategory($category->id, $subName);
            
            if (!$subcategory) {
                $this->logSkippedRow($rowIndex, $partCode, $name, "UNMATCHED SUBCATEGORY: '{$subName}' not found under '{$categoryName}' in system.", $data, 'subcategory');
                return;
            }
        }

        if ($subcategory === null) {
            $this->logSkippedRow($rowIndex, $partCode, $name, "UNMATCHED HIERARCHY: Could not find a valid mapping for the provided categories.", $data, 'subcategory');
            return;
        }

        $brandId = null;
        $brandCell = $this->stringValue($this->getCell($data, 'brand'));
        if ($brandCell !== '') {
            $brandId = $this->resolveBrandId($brandCell);
            if ($brandId === null) {
                $this->logSkippedRow($rowIndex, $partCode, $name, "UNMATCHED BRAND: '{$brandCell}' not found in database.", $data, 'brand');
                return;
            }
        }

        $slugInput = $this->stringValue($this->getCell($data, 'slug'));
        $baseSlug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($name);
        if ($baseSlug === '') {
            $baseSlug = Str::slug($name).'-'.Str::lower(Str::random(6));
        }
        
        $productExists = Product::withTrashed()->where('name', $name)
            ->when($partCode !== '', function($q) use ($partCode) {
                return $q->orWhere('part_code', $partCode);
            })
            ->first();

        $slug = $productExists ? $productExists->slug : $this->uniqueSlug($baseSlug);
        // We will update if it exists, or create if not.

        $dateStr = now()->format('dmY');

        $pictureUrl = $this->stringValue($this->getCell($data, 'product_picture_url', 'picture_url'));
        $thumbCell = $this->stringValue($this->getCell($data, 'thumbnail'));
        
        $thumbnail = null;
        $thumbnailWarning = null;

        if ($pictureUrl !== '' && filter_var($pictureUrl, FILTER_VALIDATE_URL)) {
            $downloaded = $this->downloadImageFromUrl(
                $pictureUrl,
                public_path('uploads/products'),
                $slug . '_' . $dateStr . '_thumbnail'
            );
            if ($downloaded) {
                $thumbnail = $downloaded;
            } else {
                $thumbnailWarning = "Failed to download product image from URL: '{$pictureUrl}'.";
            }
        } elseif ($thumbCell !== '') {
            $thumbnail = $this->copyImageFromZip(
                $thumbCell,
                public_path('uploads/products'),
                $slug . '_' . $dateStr . '_thumbnail'
            );
            if (!$thumbnail) {
                $thumbnailWarning = "Thumbnail image '{$thumbCell}' not found in ZIP archive.";
            }
        } elseif ($productExists && $productExists->thumbnail) {
            $thumbnail = $productExists->thumbnail;
        }

        $status = $this->parseBoolStatus($this->getCell($data, 'status'), true);
        $featured = $this->parseBoolStatus($this->getCell($data, 'featured'), false);
        $isFuture = $this->parseBoolStatus($this->getCell($data, 'is_future', 'future'), false);

        // Build tags array dynamically
        $tag0 = $this->stringValue($this->getCell($data, 'tag0'));
        $tag1 = $this->stringValue($this->getCell($data, 'tag1'));
        $tagsArr = array_filter([$tag0, $tag1]);
        $finalTags = count($tagsArr) > 0 ? implode(', ', $tagsArr) : $this->nullableString($this->getCell($data, 'tags'));

        $priceCell = $this->getCell($data, 'price');
        $price = null;
        if ($priceCell !== null && $priceCell !== '') {
            // sanitize currency symbols, spaces, commas, and parse as a float decimal
            $priceString = preg_replace('/[^0-9.]/', '', str_replace(',', '', (string)$priceCell));
            if (is_numeric($priceString)) {
                $price = (float) $priceString;
            }
        }

        try {
            DB::transaction(function () use ($data, $brandId, $category, $subcategory, $name, $slug, $partCode, $thumbnail, $status, $featured, $dateStr, $isFuture, $productExists, $finalTags, $price) {
                $finalPartCode = $partCode !== '' ? $partCode : ($productExists?->part_code ?? $this->uniquePartCode());
                
                $attributes = [
                    'brand_id' => $brandId,
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory->id,
                    'child_category_id' => null,
                    'part_code' => $finalPartCode,
                    'part_number' => $this->nullableString($this->getCell($data, 'part_number', 'sku')),
                    'thumbnail' => $thumbnail,
                    'short_description' => $this->nullableString($this->getCell($data, 'short_description', 'product_description', 'description')),
                    'variant' => null,
                    'price' => $price,
                    'specifications' => null,
                    'tags' => $finalTags,
                    'packaging' => null,
                    'additional_info' => $this->nullableString($this->getCell($data, 'additional_info')),
                    'featured' => $featured,
                    'is_future' => $isFuture,
                    'status' => $status,
                    'meta_title' => $this->nullableString($this->getCell($data, 'meta_title')),
                    'meta_description' => $this->nullableString($this->getCell($data, 'meta_description')),
                    'meta_keywords' => $this->nullableString($this->getCell($data, 'meta_keywords')),
                ];

                if ($productExists) {
                    if ($productExists->trashed()) {
                        $productExists->restore();
                    }
                    $productExists->update($attributes);
                    $product = $productExists;
                } else {
                    $attributes['name'] = $name;
                    $attributes['slug'] = $slug;
                    $product = Product::create($attributes);
                }

                // Handle Gallery Images (both custom Excel Picture0/1/2 and standard ZIP gallery_images)
                $galleryRaw = $this->stringValue($this->getCell($data, 'gallery_images', 'gallery'));
                $galleryUrls = [
                    $this->stringValue($this->getCell($data, 'picture0')),
                    $this->stringValue($this->getCell($data, 'picture1')),
                    $this->stringValue($this->getCell($data, 'picture2'))
                ];
                $galleryUrls = array_filter($galleryUrls);

                if (count($galleryUrls) > 0) {
                    $galleryDir = public_path('uploads/products/gallery');
                    foreach ($galleryUrls as $idx => $url) {
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $downloaded = $this->downloadImageFromUrl(
                                $url,
                                $galleryDir,
                                $slug . '_' . $dateStr . '_gallery_' . ($idx + 1)
                            );
                            if ($downloaded) {
                                if (!ProductImage::where('product_id', $product->id)->where('image', $downloaded)->exists()) {
                                    ProductImage::create([
                                        'product_id' => $product->id,
                                        'image' => $downloaded,
                                    ]);
                                }
                            } else {
                                $this->logWarningRow($rowIndex, $finalPartCode, $name, "Failed to download gallery image from URL: '{$url}'.");
                            }
                        }
                    }
                } elseif ($galleryRaw !== '') {
                    $names = preg_split('/\s*[,;|]\s*/', $galleryRaw, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $galleryDir = public_path('uploads/products/gallery');
                    foreach ($names as $index => $imageName) {
                        $stored = $this->copyImageFromZip(
                            $imageName, 
                            $galleryDir,
                            $slug . '_' . $dateStr . '_gallery_' . ($index + 1)
                        );
                        if ($stored) {
                            if (!ProductImage::where('product_id', $product->id)->where('image', $stored)->exists()) {
                                ProductImage::create([
                                    'product_id' => $product->id,
                                    'image' => $stored,
                                ]);
                            }
                        } else {
                            // Log warning for missing gallery image
                            $this->logWarningRow($rowIndex, $finalPartCode, $name, "Gallery image '{$imageName}' not found in ZIP archive.");
                        }
                    }
                }
            });

            // Log Success
            $this->logDetailedRow($rowIndex, $partCode, $name, 'imported', 'Product imported successfully.', $data);

            // Log warning if thumbnail was missing
            if ($thumbnailWarning) {
                $this->logWarningRow($rowIndex, $partCode, $name, $thumbnailWarning, $data);
            }

            $this->applyLogDelta(1, 0, 0, $thumbnailWarning ? 1 : 0, []);
        } catch (\Throwable $e) {
            $this->logFailedRow($rowIndex, $partCode, $name, 'Database error: ' . $e->getMessage(), $data);
        }
    }

    private function skipRow(?int $rowIndex, string $message): void
    {
        $log = ProductImportLog::find($this->importLogId);
        $errors = [];
        if ($log && count($log->errors ?? []) < self::ERROR_CAP) {
            $errors[] = ['row' => $rowIndex, 'message' => $message];
        }

        $this->applyLogDelta(0, 1, $errors);
    }

    private function logSkippedRow(int $rowIndex, string $partCode, string $productName, string $message, array $row = [], ?string $failedOn = null): void
    {
        $this->logDetailedRow($rowIndex, $partCode, $productName, 'skipped', $message, $row, $failedOn);
        $this->applyLogDelta(0, 1, 0, 0, []);
    }

    private function logFailedRow(int $rowIndex, string $partCode, string $productName, string $message, array $row = [], ?string $failedOn = null): void
    {
        $this->logDetailedRow($rowIndex, $partCode, $productName, 'failed', $message, $row, $failedOn);
        $this->applyLogDelta(0, 0, 1, 0, []);
    }

    private function logWarningRow(int $rowIndex, string $partCode, string $productName, string $message, array $row = [], ?string $failedOn = null): void
    {
        $this->logDetailedRow($rowIndex, $partCode, $productName, 'warning', $message, $row, $failedOn);
        $this->applyLogDelta(0, 0, 0, 1, []);
    }

    private function logDetailedRow(int $rowIndex, string $partCode, string $productName, string $status, string $message, array $row = [], ?string $failedOn = null): void
    {
        $log = ProductImportLog::find($this->importLogId);
        if (!$log) {
            return;
        }

        $detailedLog = [
            'row' => $rowIndex,
            'part_code' => $partCode,
            'product_name' => $productName,
            'category' => $this->stringValue($this->getCell($row, 'category')),
            'subcategory' => $this->stringValue($this->getCell($row, 'sub_category', 'subcategory')),
            'child_category' => $this->stringValue($this->getCell($row, 'child_category', 'childcategory')),
            'failed_on' => $failedOn,
            'status' => $status,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];

        $existingLogs = $log->detailed_logs ?? [];
        $existingLogs[] = $detailedLog;

        $log->update(['detailed_logs' => $existingLogs]);
    }

    /**
     * @param  list<array{row: int|null, message: string}>  $newErrors
     */
    private function applyLogDelta(int $imported, int $skipped, int $failed = 0, int $warning = 0, array $newErrors = []): void
    {
        if ($imported === 0 && $skipped === 0 && $failed === 0 && $warning === 0 && $newErrors === []) {
            return;
        }

        $log = ProductImportLog::find($this->importLogId);
        if (! $log) {
            return;
        }

        $merged = array_merge($log->errors ?? [], $newErrors);
        if (count($merged) > self::ERROR_CAP) {
            $merged = array_slice($merged, -self::ERROR_CAP);
        }

        $log->update([
            'imported_rows' => $log->imported_rows + $imported,
            'skipped_rows' => $log->skipped_rows + $skipped,
            'failed_rows' => ($log->failed_rows ?? 0) + $failed,
            'warning_rows' => ($log->warning_rows ?? 0) + $warning,
            'errors' => $merged,
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
        $key = $categoryId.'|'.Str::lower($name);
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

            if (! is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            file_put_contents($destDir.DIRECTORY_SEPARATOR.$newName, $contents);

            return $newName;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function resolveChildCategory(int $categoryId, ?int $subcategoryId, string $name): ?ChildCategory
    {
        $key = $categoryId.'|'.($subcategoryId === null ? 'null' : $subcategoryId).'|'.Str::lower($name);
        if (array_key_exists($key, $this->childCategoryCache)) {
            return $this->childCategoryCache[$key];
        }
        $query = ChildCategory::where('category_id', $categoryId)
            ->whereRaw('LOWER(name) = ?', [Str::lower($name)]);

        if ($subcategoryId !== null) {
            $query->where('subcategory_id', $subcategoryId);
        }

        $child = $query->first();
        if (!$child && $subcategoryId !== null) {
            $child = ChildCategory::create([
                'category_id' => $categoryId,
                'subcategory_id' => $subcategoryId,
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => 1
            ]);
        }
        $this->childCategoryCache[$key] = $child;

        return $child;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function uniquePartCode(): string
    {
        do {
            $code = 'IMP-'.strtoupper(Str::random(10));
        } while (Product::withTrashed()->where('part_code', $code)->exists());

        return $code;
    }

    private function copyImageFromZip(string $filename, string $destDir, ?string $customName = null): ?string
    {
        $filename = trim(str_replace('\\', '/', $filename));
        if ($filename === '') {
            return null;
        }

        $idx = $this->imageIndex();
        $key = Str::lower($filename);
        $source = $idx[$key] ?? null;

        if ($source === null) {
            $basename = basename($filename);
            $key = Str::lower($basename);
            $source = $idx[$key] ?? null;
        }

        if (! $source || ! is_file($source)) {
            return null;
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION) ?: pathinfo($filename, PATHINFO_EXTENSION);
        $newName = $customName
            ? $customName . '.' . $extension
            : time().'_'.uniqid('', true).'_'.basename($source);

        if (! is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        if (! @copy($source, $destDir.DIRECTORY_SEPARATOR.$newName)) {
            return null;
        }

        return $newName;
    }

    /**
     * @return array<string, string>
     */
    private function imageIndex(): array
    {
        if ($this->imageIndex !== null) {
            return $this->imageIndex;
        }

        $this->imageIndex = [];
        if (! is_dir($this->imagesExtractPath)) {
            return $this->imageIndex;
        }

        $basePath = rtrim($this->imagesExtractPath, DIRECTORY_SEPARATOR);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $pathName = $file->getPathname();
            $relative = ltrim(str_replace('\\', '/', substr($pathName, strlen($basePath))), '/');
            $lowerRelative = Str::lower($relative);
            $lowerBasename = Str::lower($file->getBasename());

            $this->imageIndex[$lowerRelative] = $pathName;
            if (! isset($this->imageIndex[$lowerBasename])) {
                $this->imageIndex[$lowerBasename] = $pathName;
            }
        }

        return $this->imageIndex;
    }

    private function parseBoolStatus(mixed $value, bool $defaultActive): int
    {
        if ($value === null || $value === '') {
            return $defaultActive ? 1 : 0;
        }
        if (is_numeric($value)) {
            return ((int) $value) === 1 ? 1 : 0;
        }

        $v = Str::lower(trim((string) $value));

        return in_array($v, ['1', 'true', 'yes', 'active', 'y'], true) ? 1 : 0;
    }

    private function stringValue(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_numeric($v)) {
            return trim((string) $v);
        }

        return trim((string) $v);
    }

    private function nullableString(mixed $v): ?string
    {
        $s = $this->stringValue($v);

        return $s === '' ? null : $s;
    }

    /**
     * @param  array<int|string, mixed>  $row
     */
    private function getCell(array $row, string ...$keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }

        return null;
    }

    /**
     * @return array<int>
     */
    private function resolveSolutionIds(string $solutionNames): array
    {
        if (trim($solutionNames) === '') {
            return [];
        }

        $names = preg_split('/\s*[,;|]\s*/', $solutionNames, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $ids = [];

        foreach ($names as $name) {
            $trimmedName = trim($name);
            $key = Str::lower($trimmedName);
            if (array_key_exists($key, $this->solutionCache)) {
                $id = $this->solutionCache[$key];
                if ($id !== null) {
                    $ids[] = $id;
                }
            } else {
                $solution = Solution::whereRaw('LOWER(name) = ?', [$key])->first();
                if (!$solution) {
                    $solution = Solution::create([
                        'name' => $trimmedName,
                        'slug' => Str::slug($trimmedName),
                        'status' => 1
                    ]);
                }
                $id = $solution?->id;
                $this->solutionCache[$key] = $id;
                if ($id !== null) {
                    $ids[] = $id;
                }
            }
        }

        return array_unique($ids);
    }
}

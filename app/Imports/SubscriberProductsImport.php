<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProductImage;
use App\Models\SubscriberProductVariant;
use App\Models\SubscriberProductAttributeValue;
use App\Models\Attribute;
use App\Models\ProductImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class SubscriberProductsImport implements OnEachRow, WithChunkReading, SkipsEmptyRows, WithHeadingRow
{
    private const ERROR_CAP = 400;

    public function __construct(
        private int $importLogId,
        private int $subscriberId,
        private string $imagesExtractPath,
        private int $categoryId
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

        $name = trim($data['name'] ?? $data['product_name'] ?? '');
        $sku = trim($data['sku'] ?? $data['part_code'] ?? '');

        if ($name === '') {
            $this->logSkippedRow($rowIndex, $sku, $name, 'Product name is required.');
            return;
        }

        // Check subscriber product limits
        $user = \App\Models\User::find($this->subscriberId);
        $sub = $user?->activeSubscription();
        $limit = $sub?->plan?->product_limit ?? 1000;
        $currCount = SubscriberProduct::where('user_id', $this->subscriberId)->count();
        if ($currCount >= $limit) {
            $this->logFailedRow($rowIndex, $sku, $name, "Subscription limit reached. Your plan allows max {$limit} products.");
            return;
        }

        if ($sku === '') {
            $sku = 'SKU-' . strtoupper(Str::random(8));
        }

        // Check for duplicates in Subscriber's workspace
        $exists = SubscriberProduct::where('user_id', $this->subscriberId)
            ->where(function($q) use ($name, $sku) {
                $q->where('name', $name)->orWhere('sku', $sku);
            })->first();

        if ($exists) {
            $this->logSkippedRow($rowIndex, $sku, $name, "Product with name or SKU already exists in your catalog.");
            return;
        }

        $subcategoryName = trim($data['subcategory'] ?? $data['sub_category'] ?? '');
        $subcategory = null;
        if ($subcategoryName !== '') {
            $subcategory = Subcategory::where('category_id', $this->categoryId)
                ->where('name', 'like', '%' . $subcategoryName . '%')
                ->first();
        }

        $mrp = isset($data['mrp']) ? floatval($data['mrp']) : null;
        $offerPrice = isset($data['offer_price']) ? floatval($data['offer_price']) : null;
        $stock = isset($data['stock']) ? intval($data['stock']) : 0;

        $thumbnail = null;
        $thumbCell = trim($data['thumbnail'] ?? '');
        if ($thumbCell !== '') {
            $thumbnail = $this->copyImageFromZip($thumbCell);
        }

        try {
            DB::transaction(function () use ($name, $sku, $subcategory, $mrp, $offerPrice, $stock, $thumbnail, $data, $rowIndex) {
                // 1. Create Subscriber Product
                $product = SubscriberProduct::create([
                    'user_id'           => $this->subscriberId,
                    'category_id'       => $this->categoryId,
                    'subcategory_id'    => $subcategory?->id,
                    'name'              => $name,
                    'slug'              => Str::slug($name) . '-' . Str::random(6),
                    'sku'               => $sku,
                    'mrp'               => $mrp,
                    'offer_price'       => $offerPrice,
                    'thumbnail'         => $thumbnail,
                    'short_description' => trim($data['short_description'] ?? $data['description'] ?? ''),
                    'full_description'  => trim($data['full_description'] ?? ''),
                    'status'            => 'active',
                    'approval_status'   => 'pending', // Requires admin review
                ]);

                // 2. Initialize default Variant (for stock/inventory)
                SubscriberProductVariant::create([
                    'subscriber_product_id' => $product->id,
                    'variant_sku'           => $product->sku,
                    'price'                 => $product->offer_price ?: $product->mrp,
                    'stock'                 => $stock,
                    'status'                => true,
                ]);

                // 3. Save dynamic attribute values
                // Find all attributes mapped to this category template
                $categoryAttributes = \App\Models\CategoryAttribute::where('category_id', $this->categoryId)
                    ->with('attribute')
                    ->get();

                foreach ($categoryAttributes as $catAttr) {
                    $attr = $catAttr->attribute;
                    if (!$attr) continue;

                    // Match header by slug or lowercase name
                    $slugKey = Str::slug($attr->name);
                    $headingKey = str_replace('-', '_', $slugKey);
                    
                    $val = null;
                    if (isset($data[$headingKey])) {
                        $val = $data[$headingKey];
                    } elseif (isset($data[$slugKey])) {
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
            });

            $this->logDetailedRow($rowIndex, $sku, $name, 'imported', 'Product imported successfully.');
            $this->applyLogDelta(1, 0, 0);
        } catch (\Throwable $e) {
            $this->logFailedRow($rowIndex, $sku, $name, 'Database error: ' . $e->getMessage());
        }
    }

    private function logSkippedRow(int $rowIndex, string $sku, string $name, string $message): void
    {
        $this->logDetailedRow($rowIndex, $sku, $name, 'skipped', $message);
        $this->applyLogDelta(0, 1, 0);
    }

    private function logFailedRow(int $rowIndex, string $sku, string $name, string $message): void
    {
        $this->logDetailedRow($rowIndex, $sku, $name, 'failed', $message);
        $this->applyLogDelta(0, 0, 1);
    }

    private function logDetailedRow(int $rowIndex, string $sku, string $name, string $status, string $message): void
    {
        $log = ProductImportLog::find($this->importLogId);
        if (!$log) return;

        $detailedLog = [
            'row'          => $rowIndex,
            'part_code'    => $sku,
            'product_name' => $name,
            'status'       => $status,
            'message'      => $message,
            'timestamp'    => now()->toISOString(),
        ];

        $existingLogs = $log->detailed_logs ?? [];
        $existingLogs[] = $detailedLog;
        $log->update(['detailed_logs' => $existingLogs]);
    }

    private function applyLogDelta(int $imported, int $skipped, int $failed): void
    {
        $log = ProductImportLog::find($this->importLogId);
        if (!$log) return;

        $log->update([
            'imported_rows' => $log->imported_rows + $imported,
            'skipped_rows'  => $log->skipped_rows + $skipped,
            'failed_rows'   => ($log->failed_rows ?? 0) + $failed,
        ]);
    }

    private function copyImageFromZip(string $filename): ?string
    {
        $filename = trim(str_replace('\\', '/', $filename));
        if ($filename === '') return null;

        $source = $this->imagesExtractPath . '/' . $filename;
        if (!file_exists($source)) {
            // Try lowercase or simple basename fallback
            $source = $this->imagesExtractPath . '/' . strtolower(basename($filename));
            if (!file_exists($source)) {
                return null;
            }
        }

        $destDir = public_path('uploads/subscriber-products');
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        $extension = pathinfo($source, PATHINFO_EXTENSION);
        $newName = Str::random(20) . '.' . $extension;

        if (@copy($source, $destDir . '/' . $newName)) {
            return $newName;
        }

        return null;
    }
}

<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProductVariant;
use App\Models\ProductImportLog;
use App\Models\User;
use App\Models\SubscriberProfile;
use App\Jobs\SubscriberProductImportJob;
use Database\Seeders\DefaultPermissionsAndRolesSeeder;
use Database\Seeders\SubscriberRoleAndPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    private User $subscriber;
    private Category $category;
    private Subcategory $subcategory;
    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultPermissionsAndRolesSeeder::class);
        $this->seed(SubscriberRoleAndPlansSeeder::class);

        $this->subscriber = User::factory()->create();
        $this->subscriber->assignRole('Subscriber');
        $this->subscriber->refresh();

        SubscriberProfile::create([
            'user_id' => $this->subscriber->id,
            'company_name' => 'Import Test Corp',
            'company_slug' => 'import-test-corp',
            'status' => 'approved',
        ]);

        // Seed subscription
        $plan = \App\Models\SubscriptionPlan::where('slug', 'enterprise')->first();
        \App\Models\Subscription::create([
            'user_id' => $this->subscriber->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // Seed basic category, subcategory, brand
        $this->category = Category::create([
            'subscriber_id' => $this->subscriber->id,
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 1,
        ]);

        $this->subcategory = Subcategory::create([
            'subscriber_id' => $this->subscriber->id,
            'category_id' => $this->category->id,
            'name' => 'Smartphones',
            'slug' => 'smartphones',
            'status' => 1,
        ]);

        $this->brand = Brand::create([
            'subscriber_id' => $this->subscriber->id,
            'name' => 'Samsung',
            'slug' => 'samsung',
            'status' => 1,
        ]);
    }

    public function test_upsert_and_partial_import_behavior(): void
    {
        Storage::fake('local');

        // Create an existing product to test SKU-based update and Name-based update
        $existingSkuProd = SubscriberProduct::create([
            'user_id' => $this->subscriber->id,
            'category_id' => [$this->category->id],
            'subcategory_id' => [$this->subcategory->id],
            'brand_id' => [$this->brand->id],
            'name' => 'Old Samsung S21',
            'slug' => 'old-samsung-s21',
            'sku' => 'SAM-S21',
            'mrp' => 80000,
            'offer_price' => 70000,
            'price' => 70000,
            'moq' => 1,
            'stock' => 10,
            'stock_status' => 'in_stock',
            'status' => 'active',
        ]);

        SubscriberProductVariant::create([
            'subscriber_product_id' => $existingSkuProd->id,
            'variant_sku' => $existingSkuProd->sku,
            'price' => $existingSkuProd->offer_price,
            'stock' => $existingSkuProd->stock,
            'status' => true,
        ]);

        $existingNameProd = SubscriberProduct::create([
            'user_id' => $this->subscriber->id,
            'category_id' => [$this->category->id],
            'subcategory_id' => [$this->subcategory->id],
            'brand_id' => [$this->brand->id],
            'name' => 'Samsung S22',
            'slug' => 'samsung-s22',
            'sku' => 'SAM-S22-OLD',
            'mrp' => 90000,
            'offer_price' => 85000,
            'price' => 85000,
            'moq' => 1,
            'stock' => 5,
            'stock_status' => 'in_stock',
            'status' => 'active',
        ]);

        SubscriberProductVariant::create([
            'subscriber_product_id' => $existingNameProd->id,
            'variant_sku' => $existingNameProd->sku,
            'price' => $existingNameProd->offer_price,
            'stock' => $existingNameProd->stock,
            'status' => true,
        ]);

        // Create spreadsheet with the following rows:
        // 1. Heading row
        // 2. New product (Insert) -> Samsung S23
        // 3. Existing product by SKU (Update SKU SAM-S21, change name/price/stock)
        // 4. Existing product by Name (Update Name "Samsung S22", change SKU/price)
        // 5. Invalid row: Non-existent Category (Fail)
        // 6. Invalid row: Invalid Offer Price format (Fail)
        // Total data rows = 5

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Product Name', 'SKU', 'Slug', 'Brand', 'Category', 'Subcategory',
            'MRP', 'Offer Price', 'MOQ', 'Stock Quantity', 'Stock Status',
            'Short Description', 'Full Description', 'Status', 'Featured'
        ];

        foreach ($headers as $colIndex => $header) {
            $sheet->setCellValueByColumnAndRow($colIndex + 1, 1, $header);
        }

        $rowsData = [
            // Row 2 (New product)
            ['Samsung S23', 'SAM-S23', 'samsung-s23', 'Samsung', 'Electronics', 'Smartphones', '100000', '95000', '1', '15', 'in_stock', 'New S23', 'Full S23', 'active', 'yes'],
            // Row 3 (Update Old Samsung S21 by SKU)
            ['New Samsung S21 Ultra', 'SAM-S21', 'samsung-s21', 'Samsung', 'Electronics', 'Smartphones', '82000', '71000', '2', '22', 'in_stock', 'Updated S21', 'Full S21', 'active', 'yes'],
            // Row 4 (Update Samsung S22 by Name)
            ['Samsung S22', 'SAM-S22-NEW', 'samsung-s22', 'Samsung', 'Electronics', 'Smartphones', '91000', '86000', '1', '8', 'in_stock', 'Updated S22', 'Full S22', 'active', 'yes'],
            // Row 5 (Success: Auto-created Category NonExistent)
            ['Samsung S24', 'SAM-S24', 'samsung-s24', 'Samsung', 'NonExistent', 'Smartphones', '120000', '110000', '1', '10', 'in_stock', 'New S24', 'Full S24', 'active', 'no'],
            // Row 6 (Success: Invalid Price format ignored)
            ['Samsung S25', 'SAM-S25', 'samsung-s25', 'Samsung', 'Electronics', 'Smartphones', '130000', 'abc', '1', '10', 'in_stock', 'New S25', 'Full S25', 'active', 'no'],
            // Row 7 (Success: Empty category & subcategory)
            ['Samsung S26', 'SAM-S26', 'samsung-s26', 'Samsung', '', '', '140000', '120000', '1', '12', 'in_stock', 'New S26', 'Full S26', 'active', 'no'],
            // Row 8 (Fail: Empty Product Name)
            ['', 'SAM-S27', 'samsung-s27', 'Samsung', 'Electronics', 'Smartphones', '150000', '130000', '1', '10', 'in_stock', 'New S27', 'Full S27', 'active', 'no'],
        ];

        foreach ($rowsData as $rowIndex => $rowData) {
            foreach ($rowData as $colIndex => $val) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $val);
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'import_test');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        // Put file in Storage
        $log = ProductImportLog::create([
            'user_id' => $this->subscriber->id,
            'scope' => 'subscriber',
            'filename' => 'products_test.xlsx',
            'status' => 'pending',
            'errors' => [],
        ]);

        $base = 'imports/' . $log->id;
        Storage::disk('local')->makeDirectory($base);
        Storage::disk('local')->putFileAs($base, new \Illuminate\Http\File($tempPath), 'products.xlsx');
        unlink($tempPath);

        // Dispatch the import job directly
        $job = new SubscriberProductImportJob($log->id, $this->subscriber->id);
        $job->handle();

        $log->refresh();

        // 1. Verify log stats
        $this->assertEquals('completed', $log->status);
        $this->assertEquals(7, $log->total_rows);
        $this->assertEquals(4, $log->imported_rows); // 4 new inserts (Samsung S23, S24, S25, S26)
        $this->assertEquals(2, $log->updated_rows);  // 2 updates (Samsung S21, S22)
        $this->assertEquals(1, $log->failed_rows);   // 1 failure (S27 empty name)

        // 2. Verify database records updated/inserted
        // Samsung S23 should be inserted
        $s23 = SubscriberProduct::where('sku', 'SAM-S23')->first();
        $this->assertNotNull($s23);
        $this->assertEquals('Samsung S23', $s23->name);
        $this->assertEquals(95000, $s23->offer_price);

        // Samsung S24 should be inserted with auto-created NonExistent Category
        $s24 = SubscriberProduct::where('sku', 'SAM-S24')->first();
        $this->assertNotNull($s24);
        $this->assertEquals('Samsung S24', $s24->name);
        $createdCat = Category::withoutGlobalScope('tenant')->where('subscriber_id', $this->subscriber->id)->where('name', 'NonExistent')->first();
        $this->assertNotNull($createdCat);
        $this->assertEquals([$createdCat->id], $s24->category_id);

        // Samsung S25 should be inserted with offer_price as null (since abc is not numeric)
        $s25 = SubscriberProduct::where('sku', 'SAM-S25')->first();
        $this->assertNotNull($s25);
        $this->assertEquals('Samsung S25', $s25->name);
        $this->assertNull($s25->offer_price);

        // Samsung S26 should be inserted (with empty category and subcategory)
        $s26 = SubscriberProduct::where('sku', 'SAM-S26')->first();
        $this->assertNotNull($s26);
        $this->assertEquals('Samsung S26', $s26->name);
        $this->assertEquals([], $s26->category_id);
        $this->assertEquals([], $s26->subcategory_id);

        // Samsung S21 should be updated (name, stock, mrp changed)
        $s21 = SubscriberProduct::where('sku', 'SAM-S21')->first();
        $this->assertNotNull($s21);
        $this->assertEquals('New Samsung S21 Ultra', $s21->name);
        $this->assertEquals(22, $s21->stock);

        $s21Variant = SubscriberProductVariant::where('subscriber_product_id', $s21->id)->first();
        $this->assertNotNull($s21Variant);
        $this->assertEquals(22, $s21Variant->stock);

        // Samsung S22 should be updated by name (SKU updated to SAM-S22-NEW)
        $s22 = SubscriberProduct::where('name', 'Samsung S22')->first();
        $this->assertNotNull($s22);
        $this->assertEquals('SAM-S22-NEW', $s22->sku);
        $this->assertEquals(8, $s22->stock);

        // Samsung S27 should not be in the database
        $this->assertDatabaseMissing('subscriber_products', ['sku' => 'SAM-S27']);

        // 3. Test dynamic error report download CSV
        $response = $this->actingAs($this->subscriber)
            ->get(route('subscriber.products.import-logs.download-errors', $log->id));

        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertMatchesRegularExpression(
            '/^attachment; filename="import_errors_' . $log->id . '_\d{8}_\d{6}\.csv"$/',
            $contentDisposition
        );

        $csvContent = $response->streamedContent();
        $this->assertStringContainsString('"Row Number",SKU,"Product Name",Category,Subcategory,"Failure Reason"', $csvContent);
        $this->assertStringContainsString('8,SAM-S27,,Electronics,Smartphones,"Product name is required."', $csvContent);
    }
}

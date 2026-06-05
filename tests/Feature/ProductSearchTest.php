<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProfile;
use App\Models\User;
use Database\Seeders\DefaultPermissionsAndRolesSeeder;
use Database\Seeders\SubscriberRoleAndPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $subscriber1;
    protected User $subscriber2;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultPermissionsAndRolesSeeder::class);
        $this->seed(SubscriberRoleAndPlansSeeder::class);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        // Create subscribers
        $this->subscriber1 = User::factory()->create();
        $this->subscriber1->assignRole('Subscriber');
        $this->subscriber1->refresh();

        $this->subscriber2 = User::factory()->create();
        $this->subscriber2->assignRole('Subscriber');
        $this->subscriber2->refresh();

        SubscriberProfile::create([
            'user_id' => $this->subscriber1->id,
            'company_name' => 'Subscriber One Store',
            'company_slug' => 'subscriber-one',
            'status' => 'approved',
            'store_status' => 'live',
        ]);

        SubscriberProfile::create([
            'user_id' => $this->subscriber2->id,
            'company_name' => 'Subscriber Two Store',
            'company_slug' => 'subscriber-two',
            'status' => 'approved',
            'store_status' => 'live',
        ]);

        // Create Admin
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Admin');
        $this->adminUser->refresh();
    }

    public function test_search_scoping_rules(): void
    {
        // 1. Setup Demo (Global) Brand, Category, Subcategory
        $demoBrand = Brand::create(['name' => 'DemoBrand', 'status' => 1]);
        $demoCategory = Category::create(['name' => 'DemoCat', 'slug' => 'demo-cat', 'status' => 1]);
        $demoSubcategory = Subcategory::create(['name' => 'DemoSubcat', 'slug' => 'demo-subcat', 'category_id' => $demoCategory->id, 'status' => 1]);

        // Demo Products
        Product::withoutEvents(fn () => Product::create([
            'name' => 'Demo Travel Bag',
            'slug' => 'demo-travel-bag',
            'sku' => 'DEMO-SKU-123',
            'status' => 1,
            'category_id' => [$demoCategory->id],
            'subcategory_id' => [$demoSubcategory->id],
            'brand_id' => [$demoBrand->id],
        ]));

        // Subscriber 1 Specific Data
        $sub1Brand = Brand::create(['name' => 'Sub1Brand', 'subscriber_id' => $this->subscriber1->id, 'status' => 1]);
        $sub1Category = Category::create(['name' => 'Sub1Cat', 'slug' => 'sub1-cat', 'subscriber_id' => $this->subscriber1->id, 'status' => 1]);
        $sub1Subcategory = Subcategory::create(['name' => 'Sub1Subcat', 'slug' => 'sub1-subcat', 'category_id' => $sub1Category->id, 'subscriber_id' => $this->subscriber1->id, 'status' => 1]);

        SubscriberProduct::create([
            'user_id' => $this->subscriber1->id,
            'name' => 'Subscriber 1 Laptop Bag',
            'slug' => 'sub1-laptop-bag',
            'sku' => 'SUB1-SKU-456',
            'status' => 'active',
            'approval_status' => 'approved',
            'category_id' => [$sub1Category->id],
            'subcategory_id' => [$sub1Subcategory->id],
            'brand_id' => [$sub1Brand->id],
        ]);

        // Subscriber 2 Specific Data
        SubscriberProduct::create([
            'user_id' => $this->subscriber2->id,
            'name' => 'Subscriber 2 Office Bag',
            'slug' => 'sub2-office-bag',
            'sku' => 'SUB2-SKU-789',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        // Scenario A: Guest visitor on main homepage (no store context)
        // Search should search Product table -> should return "Demo Travel Bag"
        $response = $this->get(route('search', ['query' => 'bag']));
        $response->assertStatus(200);
        $response->assertSee('Demo Travel Bag');
        $response->assertDontSee('Subscriber 1 Laptop Bag');
        $response->assertDontSee('Subscriber 2 Office Bag');

        // Scenario B: Guest visitor on Subscriber 1 storefront (company_slug is provided)
        // Search should search Subscriber 1 products only -> should return "Subscriber 1 Laptop Bag"
        $response = $this->get(route('search', ['query' => 'bag', 'company_slug' => 'subscriber-one']));
        $response->assertStatus(200);
        $response->assertSee('Subscriber 1 Laptop Bag');
        $response->assertDontSee('Demo Travel Bag');
        $response->assertDontSee('Subscriber 2 Office Bag');

        // Scenario C: Logged-in Subscriber 1
        // Search should strictly be limited to Subscriber 1 products regardless of url params
        $response = $this->actingAs($this->subscriber1)
            ->get(route('search', ['query' => 'bag']));
        $response->assertStatus(200);
        $response->assertSee('Subscriber 1 Laptop Bag');
        $response->assertDontSee('Demo Travel Bag');
        $response->assertDontSee('Subscriber 2 Office Bag');

        // Scenario D: Logged-in Admin
        // Search should strictly search demo products table
        $response = $this->actingAs($this->adminUser)
            ->get(route('search', ['query' => 'bag']));
        $response->assertStatus(200);
        $response->assertSee('Demo Travel Bag');
        $response->assertDontSee('Subscriber 1 Laptop Bag');
        $response->assertDontSee('Subscriber 2 Office Bag');
    }

    public function test_search_matching_fields(): void
    {
        $demoBrand = Brand::create(['name' => 'AwesomeBrand', 'status' => 1]);
        $demoCategory = Category::create(['name' => 'FashionCategory', 'slug' => 'fashion-cat', 'status' => 1]);
        $demoSubcategory = Subcategory::create(['name' => 'BackpacksSub', 'slug' => 'backpacks-sub', 'category_id' => $demoCategory->id, 'status' => 1]);

        Product::withoutEvents(fn () => Product::create([
            'name' => 'Leather Wallet',
            'slug' => 'leather-wallet',
            'sku' => 'WALLET-SKU-99',
            'status' => 1,
            'category_id' => [$demoCategory->id],
            'subcategory_id' => [$demoSubcategory->id],
            'brand_id' => [$demoBrand->id],
        ]));

        // Search by Brand name -> should match
        $response = $this->get(route('search', ['query' => 'Awesome']));
        $response->assertSee('Leather Wallet');

        // Search by Category name -> should match
        $response = $this->get(route('search', ['query' => 'Fashion']));
        $response->assertSee('Leather Wallet');

        // Search by Subcategory name -> should match
        $response = $this->get(route('search', ['query' => 'Backpack']));
        $response->assertSee('Leather Wallet');

        // Search by partial SKU -> should match
        $response = $this->get(route('search', ['query' => 'SKU-99']));
        $response->assertSee('Leather Wallet');
    }

    public function test_catalogue_page_filters(): void
    {
        $cat1 = Category::create(['name' => 'CatOne', 'slug' => 'cat-one', 'status' => 1]);
        $cat2 = Category::create(['name' => 'CatTwo', 'slug' => 'cat-two', 'status' => 1]);

        $p1 = Product::withoutEvents(fn () => Product::create([
            'name' => 'First Product',
            'slug' => 'first-product',
            'sku' => 'P1-SKU',
            'status' => 1,
            'category_id' => [$cat1->id],
        ]));

        $p2 = Product::withoutEvents(fn () => Product::create([
            'name' => 'Second Product',
            'slug' => 'second-product',
            'sku' => 'P2-SKU',
            'status' => 1,
            'category_id' => [$cat2->id],
        ]));

        // 1. Without catalogue filters, both show
        $response = $this->get(route('search', ['query' => 'Product']));
        $response->assertSee('First Product');
        $response->assertSee('Second Product');

        // 2. Filter by category slug 'cat-one' -> only First Product should show
        $response = $this->get(route('search', ['query' => 'Product', 'category' => 'cat-one']));
        $response->assertSee('First Product');
        $response->assertDontSee('Second Product');

        // 3. Filter by products parameter (limit to $p2's ID) -> only Second Product should show
        $response = $this->get(route('search', ['query' => 'Product', 'products' => (string)$p2->id]));
        $response->assertDontSee('First Product');
        $response->assertSee('Second Product');
    }
}

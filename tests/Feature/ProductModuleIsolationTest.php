<?php

namespace Tests\Feature;

use App\Exports\ProductsExport;
use App\Models\Product;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProfile;
use App\Models\User;
use Database\Seeders\DefaultPermissionsAndRolesSeeder;
use Database\Seeders\SubscriberRoleAndPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductModuleIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultPermissionsAndRolesSeeder::class);
        $this->seed(SubscriberRoleAndPlansSeeder::class);
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    }

    public function test_subscriber_duplicate_validation_is_scoped_to_same_user(): void
    {
        $firstSubscriber = User::factory()->create();
        $firstSubscriber->assignRole('Subscriber');
        $firstSubscriber->refresh();
        $secondSubscriber = User::factory()->create();
        $secondSubscriber->assignRole('Subscriber');
        $secondSubscriber->refresh();
        SubscriberProfile::create([
            'user_id' => $firstSubscriber->id,
            'company_name' => 'First Subscriber',
            'company_slug' => 'first-subscriber',
            'status' => 'approved',
        ]);
        SubscriberProfile::create([
            'user_id' => $secondSubscriber->id,
            'company_name' => 'Second Subscriber',
            'company_slug' => 'second-subscriber',
            'status' => 'approved',
        ]);

        $this->assertTrue($firstSubscriber->isSubscriber());
        $this->assertTrue($secondSubscriber->isSubscriber());

        SubscriberProduct::create([
            'user_id' => $firstSubscriber->id,
            'name' => 'Shared Product',
            'slug' => 'shared-product',
            'sku' => 'SHARED-SKU',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('subscriber_products', [
            'user_id' => $firstSubscriber->id,
            'name' => 'Shared Product',
        ]);

        $response = $this->actingAs($firstSubscriber)
            ->post(route('subscriber.products.store'), [
                'name' => 'Shared Product',
                'sku' => 'SHARED-SKU-2',
                'status' => 'active',
            ]);

        $this->assertDatabaseCount('products', 0);
        $response->assertSessionHasErrors('name');

        $this->actingAs($secondSubscriber)
            ->post(route('subscriber.products.store'), [
                'name' => 'Shared Product',
                'sku' => 'SHARED-SKU-2',
                'status' => 'active',
            ])
            ->assertRedirect(route('subscriber.products.index'));

        $this->assertDatabaseCount('subscriber_products', 2);
    }

    public function test_admin_duplicate_validation_and_access_are_scoped_to_same_admin_user(): void
    {
        $firstAdmin = User::factory()->create();
        $firstAdmin->assignRole('Admin');
        $firstAdmin->refresh();
        $secondAdmin = User::factory()->create();
        $secondAdmin->assignRole('Admin');
        $secondAdmin->refresh();

        $firstProduct = Product::withoutEvents(fn () => Product::create([
            'subscriber_id' => $firstAdmin->id,
            'name' => 'Admin Scoped Product',
            'slug' => 'admin-scoped-product',
            'sku' => 'ADMIN-SKU',
            'status' => 1,
        ]));

        $this->actingAs($firstAdmin)
            ->post(route('admin.products.store'), [
                'name' => 'Admin Scoped Product',
                'sku' => 'ADMIN-SKU-2',
                'status' => 1,
            ])
            ->assertSessionHasErrors('name');

        $this->actingAs($secondAdmin)
            ->post(route('admin.products.store'), [
                'name' => 'Admin Scoped Product',
                'sku' => 'ADMIN-SKU-2',
                'status' => 1,
            ])
            ->assertRedirect(route('admin.products.index'));

        $this->actingAs($secondAdmin)
            ->get(route('admin.products.edit', $firstProduct->id))
            ->assertNotFound();

        $this->assertDatabaseCount('products', 2);
    }

    public function test_admin_export_query_only_contains_current_admin_products(): void
    {
        $firstAdmin = User::factory()->create();
        $firstAdmin->assignRole('Admin');
        $firstAdmin->refresh();
        $secondAdmin = User::factory()->create();
        $secondAdmin->assignRole('Admin');
        $secondAdmin->refresh();

        Product::withoutEvents(fn () => Product::create([
            'subscriber_id' => $firstAdmin->id,
            'name' => 'Export Mine',
            'slug' => 'export-mine',
            'sku' => 'EXPORT-MINE',
            'status' => 1,
        ]));
        Product::withoutEvents(fn () => Product::create([
            'subscriber_id' => $secondAdmin->id,
            'name' => 'Export Other',
            'slug' => 'export-other',
            'sku' => 'EXPORT-OTHER',
            'status' => 1,
        ]));

        $this->actingAs($firstAdmin);

        $products = (new ProductsExport($firstAdmin->id))->query()->get();

        $this->assertCount(1, $products);
        $this->assertSame('Export Mine', $products->first()->name);
    }
}

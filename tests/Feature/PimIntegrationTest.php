<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\SubscriberProduct;
use App\Models\SubscriberProfile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\DefaultPermissionsAndRolesSeeder;
use Database\Seeders\SubscriberRoleAndPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PimIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $subscriberUser;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed Roles, Permissions and Subscription Plans
        $this->seed(DefaultPermissionsAndRolesSeeder::class);
        $this->seed(SubscriberRoleAndPlansSeeder::class);

        // 2. Create Super Admin
        $this->adminUser = User::factory()->create([
            'email' => 'admin@catasky.com',
        ]);
        $this->adminUser->assignRole('Super Admin');

        // 3. Create Subscriber User
        $this->subscriberUser = User::factory()->create([
            'email' => 'subscriber@catasky.com',
        ]);
        $this->subscriberUser->assignRole('Subscriber');

        // 4. Set up an active Enterprise Subscription for the Subscriber
        $plan = SubscriptionPlan::where('slug', 'enterprise')->first();
        Subscription::create([
            'user_id' => $this->subscriberUser->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        // 5. Set up active Subscriber Profile
        SubscriberProfile::create([
            'user_id' => $this->subscriberUser->id,
            'company_name' => 'Acme Corp',
            'company_slug' => 'acme-corp',
            'status' => 'approved',
        ]);

        // 6. Create a default category
        $this->category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 1,
        ]);
    }

    /**
     * Test the Super Admin PIM Attribute management and Template Assignment.
     */
    public function test_super_admin_can_manage_pim_attributes_and_assign_to_category_templates(): void
    {
        // Act as Super Admin
        $this->actingAs($this->adminUser);

        // 1. Super Admin creates a global attribute with PIM flags
        $response = $this->post(route('admin.attributes.store'), [
            'name' => 'RAM Capacity',
            'type' => 'text',
            'is_required' => 1,
            'is_filterable' => 1,
            'is_comparable' => 1,
            'is_variant_enabled' => 1,
            'is_global' => 1,
            'is_active' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attributes', [
            'name' => 'RAM Capacity',
            'is_filterable' => true,
            'is_comparable' => true,
            'is_variant_enabled' => true,
            'is_global' => true,
        ]);

        $attribute = Attribute::where('name', 'RAM Capacity')->first();

        // 2. Super Admin maps the attribute to the category template via PUT/PATCH
        $response = $this->put(route('admin.templates.update', $this->category->id), [
            'attributes' => [
                $attribute->id => [
                    'checked' => 1,
                    'is_required' => 1,
                    'sort_order' => 1,
                ]
            ]
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('category_attributes', [
            'category_id' => $this->category->id,
            'attribute_id' => $attribute->id,
        ]);
    }

    /**
     * Test the dynamic category-attributes AJAX loading for Subscriber.
     */
    public function test_subscriber_can_fetch_category_attributes_ajax(): void
    {
        // Act as Super Admin to set up template mapping
        $this->actingAs($this->adminUser);
        $attribute = Attribute::create([
            'user_id' => $this->adminUser->id,
            'slug' => 'processor-speed',
            'name' => 'Processor Speed',
            'type' => 'text',
            'is_global' => 1,
            'is_active' => 1,
        ]);

        CategoryAttribute::create([
            'category_id' => $this->category->id,
            'attribute_id' => $attribute->id,
            'is_required' => 1,
            'sort_order' => 1,
        ]);

        // Act as Subscriber
        $this->actingAs($this->subscriberUser);

        // Access the AJAX category-attributes endpoint
        $response = $this->getJson(route('subscriber.api.category-attributes', $this->category->id));

        $response->assertOk();
        $response->assertJsonFragment([
            'name' => 'Processor Speed',
        ]);
    }

    /**
     * Test Subscriber Product creation with dynamic attributes.
     */
    public function test_subscriber_can_create_product_with_dynamic_attributes(): void
    {
        // Act as Super Admin to map attribute to Electronics
        $this->actingAs($this->adminUser);
        $attribute = Attribute::create([
            'user_id' => $this->adminUser->id,
            'slug' => 'storage-space',
            'name' => 'Storage Space',
            'type' => 'text',
            'is_global' => 1,
            'is_active' => 1,
        ]);

        CategoryAttribute::create([
            'category_id' => $this->category->id,
            'attribute_id' => $attribute->id,
            'is_required' => 1,
            'sort_order' => 1,
        ]);

        // Act as Subscriber
        $this->actingAs($this->subscriberUser);

        // Create a product with dynamic attributes
        $response = $this->post(route('subscriber.products.store'), [
            'name' => 'Super Phone X',
            'category_id' => [$this->category->id],
            'sku' => 'PHONE-X-001',
            'mrp' => 59999.00,
            'offer_price' => 54999.00,
            'short_description' => 'A super high-end smartphone',
            'full_description' => 'Detailed product description here',
            'status' => 'active',
            'attributes' => [
                $attribute->id => '256GB SSD',
            ]
        ]);

        $response->assertRedirect();
        
        $product = SubscriberProduct::where('name', 'Super Phone X')->first();
        $this->assertNotNull($product);

        $this->assertDatabaseHas('subscriber_product_attribute_values', [
            'subscriber_product_id' => $product->id,
            'attribute_id' => $attribute->id,
            'value' => '256GB SSD',
        ]);
    }

    /**
     * Test Custom Field Approval workflow.
     */
    public function test_custom_field_approval_workflow_by_super_admin(): void
    {
        // Act as Subscriber to create a custom/subscriber attribute
        $this->actingAs($this->subscriberUser);

        $attribute = Attribute::create([
            'user_id' => $this->subscriberUser->id,
            'slug' => 'chemical-purity',
            'name' => 'Chemical Purity',
            'type' => 'text',
            'is_global' => 0,
            'approval_status' => 'pending',
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('attributes', [
            'name' => 'Chemical Purity',
            'is_global' => false,
            'approval_status' => 'pending',
        ]);

        // Act as Super Admin to approve it
        $this->actingAs($this->adminUser);

        $response = $this->post(route('admin.attributes.approve', $attribute->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('attributes', [
            'id' => $attribute->id,
            'is_global' => true,
            'approval_status' => 'approved',
        ]);
    }

    /**
     * Test product details page compiles and renders correctly when brand_id is an array.
     */
    public function test_product_details_page_handles_array_brand_id_without_type_error(): void
    {
        // 1. Create a brand
        $brand = \App\Models\Brand::create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'status' => 1
        ]);

        // 2. Create a product
        $product = \App\Models\Product::create([
            'brand_id' => [$brand->id],
            'category_id' => [$this->category->id],
            'subcategory_id' => [],
            'name' => 'Winner Cup Desk Trophy',
            'slug' => 'winner-cup-desk-trophy-X2w0ys',
            'sku' => 'WINNER-CUP-001',
            'mrp' => 1200.00,
            'offer_price' => 999.00,
            'status' => 1,
            'short_description' => 'A beautiful desk trophy.',
            'variant' => '₹999',
            'specifications' => 'Material: Brass',
            'tags' => 'trophy, desk',
            'packaging' => 'Box',
            'additional_info' => 'No info',
        ]);

        // 3. Make GET request to the product details page
        $response = $this->get(route('product.details', $product->slug));

        // 4. Assert response is successful (no TypeError / 500 error)
        $response->assertStatus(200);

        // 5. Submit B2B enquiry to make sure the single brand_id resolves correctly and saves
        $enquiryData = [
            'product_id' => $product->id,
            'brand_id' => '', // leave empty to force controller fallback logic
            'name' => 'Corporate Customer',
            'email' => 'corporate@example.com',
            'phone' => '1234567890',
            'message' => 'We want to buy 100 units.'
        ];

        $enquiryResponse = $this->post(route('enquiry.submit'), $enquiryData);

        $enquiryResponse->assertRedirect();
        
        // Assert enquiry was logged with the brand ID resolved from product (first element of array)
        $this->assertDatabaseHas('enquiries', [
            'product_id' => $product->id,
            'brand_id' => $brand->id,
            'name' => 'Corporate Customer',
            'email' => 'corporate@example.com',
        ]);
    }
}


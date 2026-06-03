<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CustomDomain;
use App\Models\SubscriberProfile;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Database\Seeders\DefaultPermissionsAndRolesSeeder;
use Database\Seeders\SubscriberRoleAndPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CustomDomainTest extends TestCase
{
    use RefreshDatabase;

    protected $subscriber;
    protected $admin;
    protected $enterprisePlan;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed Roles, Permissions and Subscription Plans
        $this->seed(DefaultPermissionsAndRolesSeeder::class);
        $this->seed(SubscriberRoleAndPlansSeeder::class);

        // 2. Retrieve seeded plans
        $this->enterprisePlan = SubscriptionPlan::where('slug', 'enterprise')->first();

        // 3. Create Admin and Subscriber users
        $this->admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@catasky.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('Super Admin');

        $this->subscriber = User::create([
            'name' => 'Ritik Subscriber',
            'email' => 'ritik@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->subscriber->assignRole('Subscriber');

        // 4. Create Subscriber Profile
        SubscriberProfile::create([
            'user_id' => $this->subscriber->id,
            'company_name' => 'Ritik Store',
            'company_slug' => 'ritik',
            'phone' => '1234567890',
            'status' => 'approved',
            'store_status' => 'live',
        ]);

        // 5. Create Active Subscription for subscriber
        Subscription::create([
            'user_id' => $this->subscriber->id,
            'subscription_plan_id' => $this->enterprisePlan->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
        ]);
    }

    /** @test */
    public function subscriber_can_add_custom_domain_mapping_request()
    {
        $this->actingAs($this->subscriber);

        $response = $this->post(route('subscriber.domain.store'), [
            'domain' => 'ritikstore.com',
        ]);

        $response->assertRedirect(route('subscriber.domain.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('custom_domains', [
            'user_id' => $this->subscriber->id,
            'domain' => 'ritikstore.com',
            'status' => 'Pending DNS Setup',
            'ssl_status' => 'SSL Pending',
            'dns_txt_key' => '@',
            'admin_approved' => false,
        ]);

        $domain = CustomDomain::first();
        $this->assertStringStartsWith('catasky-verification=', $domain->dns_txt_value);
        $this->assertTrue($domain->logs()->where('action', 'created')->exists());
    }

    /** @test */
    public function subscriber_cannot_add_duplicate_domain()
    {
        // 1. Subscriber 1 maps a domain
        CustomDomain::create([
            'user_id' => $this->subscriber->id,
            'domain' => 'ritikstore.com',
            'status' => 'Pending DNS Setup',
            'ssl_status' => 'SSL Pending',
            'dns_txt_key' => '@',
            'dns_txt_value' => 'catasky-verification=token123',
        ]);

        // 2. Create another Subscriber
        $subscriber2 = User::create([
            'name' => 'Other Subscriber',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);
        $subscriber2->assignRole('Subscriber');
        SubscriberProfile::create([
            'user_id' => $subscriber2->id,
            'company_name' => 'Other Store',
            'company_slug' => 'other',
            'status' => 'approved',
            'store_status' => 'live',
        ]);
        Subscription::create([
            'user_id' => $subscriber2->id,
            'subscription_plan_id' => $this->enterprisePlan->id,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'status' => 'active',
        ]);

        // 3. Subscriber 2 tries to map the same domain
        $this->actingAs($subscriber2);
        $response = $this->post(route('subscriber.domain.store'), [
            'domain' => 'ritikstore.com',
        ]);

        $response->assertSessionHasErrors(['domain']);
    }

    /** @test */
    public function subscriber_domain_validation_blocks_invalid_reserved_and_blacklisted_domains()
    {
        $this->actingAs($this->subscriber);

        // A. Invalid characters check (space/underscore)
        $response = $this->post(route('subscriber.domain.store'), ['domain' => 'invalid_char.com']);
        $response->assertSessionHasErrors(['domain']);

        // B. Reserved domain check
        $response = $this->post(route('subscriber.domain.store'), ['domain' => 'api.catasky.com']);
        $response->assertSessionHasErrors(['domain']);

        // C. Blacklisted keyword check
        $response = $this->post(route('subscriber.domain.store'), ['domain' => 'phishing-store.com']);
        $response->assertSessionHasErrors(['domain']);

        // D. Non-existent domain check (only in non-sandbox context)
        $response = $this->post(route('subscriber.domain.store'), ['domain' => 'zxxswqaz.com']);
        $response->assertSessionHasErrors(['domain']);
    }

    /** @test */
    public function subscriber_dns_verification_succeeds_in_sandbox_and_transitions_to_dns_verified()
    {
        $this->actingAs($this->subscriber);

        $domain = CustomDomain::create([
            'user_id' => $this->subscriber->id,
            'domain' => 'test-store.local', // sandbox keyword "test"
            'status' => 'Pending DNS Setup',
            'ssl_status' => 'SSL Pending',
            'dns_txt_key' => '@',
            'dns_txt_value' => 'catasky-verification=token123',
        ]);

        $response = $this->post(route('subscriber.domain.verify'), [
            'domain_id' => $domain->id,
        ]);

        $response->assertJson([
            'success' => true,
        ]);

        $domain->refresh();

        $this->assertTrue($domain->dns_txt_verified);
        $this->assertTrue($domain->dns_a_verified);
        $this->assertTrue($domain->dns_cname_verified);
        $this->assertTrue($domain->dns_verified);
        $this->assertEquals('DNS Verified', $domain->status);
        $this->assertEquals('SSL Pending', $domain->ssl_status);

        // Verify that custom domain is NOT synced to subscriber profile yet because Admin has not approved
        $profile = $this->subscriber->subscriberProfile;
        $this->assertNull($profile->custom_domain);
        $this->assertFalse((bool)$profile->domain_verified);
    }

    /** @test */
    public function admin_can_approve_domain_and_activate_routing_with_ssl_generation()
    {
        // 1. First, Subscriber verifies DNS
        $domain = CustomDomain::create([
            'user_id' => $this->subscriber->id,
            'domain' => 'test-store.local',
            'status' => 'Pending DNS Setup',
            'ssl_status' => 'SSL Pending',
            'dns_txt_key' => '@',
            'dns_txt_value' => 'catasky-verification=token123',
        ]);

        $domain->verifyDns();
        $domain->refresh();
        $this->assertEquals('DNS Verified', $domain->status);

        // 2. Admin approves domain
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.saas.domains.approve', $domain->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $domain->refresh();
        $this->assertTrue($domain->admin_approved);
        $this->assertEquals('Active', $domain->status);
        $this->assertEquals('SSL Active', $domain->ssl_status);
        $this->assertNotNull($domain->domain_expires_at);
        $this->assertNotNull($domain->ssl_expires_at);

        // 3. Confirm domain is synced to Subscriber Profile
        $profile = $this->subscriber->subscriberProfile->refresh();
        $this->assertEquals('test-store.local', $profile->custom_domain);
        $this->assertTrue((bool)$profile->domain_verified);
    }

    /** @test */
    public function admin_can_reject_domain_with_reason()
    {
        $domain = CustomDomain::create([
            'user_id' => $this->subscriber->id,
            'domain' => 'test-store.local',
            'status' => 'Pending DNS Setup',
            'ssl_status' => 'SSL Pending',
            'dns_txt_key' => '@',
            'dns_txt_value' => 'catasky-verification=token123',
        ]);

        // Admin rejects domain request
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.saas.domains.reject', $domain->id), [
            'rejection_reason' => 'Invalid DNS txt record configuration.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $domain->refresh();
        $this->assertEquals('Rejected', $domain->status);
        $this->assertFalse($domain->admin_approved);
        $this->assertEquals('Invalid DNS txt record configuration.', $domain->rejection_reason);

        // Verify subscriber profile has no custom domain mapping
        $profile = $this->subscriber->subscriberProfile->refresh();
        $this->assertNull($profile->custom_domain);
        $this->assertFalse((bool)$profile->domain_verified);
    }

    /** @test */
    public function middleware_restricts_access_to_non_active_custom_domains()
    {
        // 1. Create a custom domain mapping which is unverified/unapproved
        $domain = CustomDomain::create([
            'user_id' => $this->subscriber->id,
            'domain' => 'test-store.local',
            'status' => 'Pending DNS Setup',
            'ssl_status' => 'SSL Pending',
            'dns_txt_key' => '@',
            'dns_txt_value' => 'catasky-verification=token123',
        ]);

        // 2. Request through middleware with the custom domain host absolute URL
        $response = $this->get('http://test-store.local/store/ritik');

        // Should abort with 403 because it's not Active and SSL is not SSL Active
        $response->assertStatus(403);

        // 3. Perform verification and approve
        $domain->verifyDns();
        $domain->update([
            'admin_approved' => true,
            'ssl_status' => 'SSL Active'
        ]);
        $domain->checkAndActivate();

        // 4. Request again - should pass and load storefront successfully (rendered page)
        $response = $this->get('http://test-store.local/store/ritik');
        $response->assertStatus(200);
    }

    /** @test */
    public function daily_revalidation_command_auto_disables_mismatched_domains()
    {
        // 1. Set up an active verified domain
        $domain = CustomDomain::create([
            'user_id' => $this->subscriber->id,
            'domain' => 'test-store.local',
            'status' => 'Active',
            'ssl_status' => 'SSL Active',
            'dns_txt_key' => '@',
            'dns_txt_value' => 'catasky-verification=token123',
            'dns_txt_verified' => true,
            'dns_a_verified' => true,
            'dns_cname_verified' => true,
            'dns_verified' => true,
            'admin_approved' => true
        ]);

        // Sync to profile
        $profile = $this->subscriber->subscriberProfile;
        $profile->update([
            'custom_domain' => $domain->domain,
            'domain_verified' => true
        ]);

        // Mock DNS verification fails by changing domain to a non-sandbox name (simulates check failure)
        $domain->update(['domain' => 'failed-check.xyz']);

        // Run artisan command
        Artisan::call('custom-domain:revalidate');

        $domain->refresh();
        $this->assertEquals('Pending DNS Setup', $domain->status);
        $this->assertEquals('SSL Pending', $domain->ssl_status);
        $this->assertTrue($domain->dns_mismatch_detected);
        $this->assertTrue($domain->logs()->where('action', 'auto_disabled')->exists());

        // Profile mapping should be cleared
        $profile->refresh();
        $this->assertNull($profile->custom_domain);
        $this->assertFalse((bool)$profile->domain_verified);
    }

    /** @test */
    public function subscriber_dns_verification_fails_for_real_mismatched_domain()
    {
        $this->actingAs($this->subscriber);

        $domain = CustomDomain::create([
            'user_id' => $this->subscriber->id,
            'domain' => 'mirashka.net.in', // real public domain name, no sandbox keyword
            'status' => 'Pending DNS Setup',
            'ssl_status' => 'SSL Pending',
            'dns_txt_key' => '@',
            'dns_txt_value' => 'catasky-verification=token123',
        ]);

        $response = $this->post(route('subscriber.domain.verify'), [
            'domain_id' => $domain->id,
        ]);

        $response->assertJson([
            'success' => false,
        ]);

        $domain->refresh();

        $this->assertFalse($domain->dns_verified);
        $this->assertEquals('Pending DNS Setup', $domain->status);
        $this->assertTrue($domain->logs()->where('action', 'dns_check')->where('status', 'failed')->exists());

        // Check that the JSON response contains the descriptive failure message
        $data = $response->json();
        $this->assertStringContainsString('TXT record not found', $data['message']);
    }
}


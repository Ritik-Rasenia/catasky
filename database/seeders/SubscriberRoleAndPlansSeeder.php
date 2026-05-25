<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\SubscriptionPlan;

class SubscriberRoleAndPlansSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define subscriber-specific permissions
        $subscriberPermissions = [
            ['name' => 'subscriber-dashboard',         'description' => 'View Subscriber Dashboard'],
            ['name' => 'manage-subscriber-products',    'description' => 'Manage Own Products'],
            ['name' => 'manage-subscriber-attributes',  'description' => 'Manage Own Attributes'],
            ['name' => 'manage-subscriber-share-links', 'description' => 'Manage Share Links'],
            ['name' => 'manage-subscriber-profile',     'description' => 'Manage Subscriber Profile'],
            ['name' => 'manage-subscriber-subscription','description' => 'Manage Subscription & Billing'],
        ];

        foreach ($subscriberPermissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                ['description' => $perm['description']]
            );
        }

        // Admin permissions for managing subscribers
        $adminSubscriberPermissions = [
            ['name' => 'view-subscribers',     'description' => 'View All Subscribers'],
            ['name' => 'create-subscribers',   'description' => 'Create Subscriber Accounts'],
            ['name' => 'edit-subscribers',     'description' => 'Edit Subscriber Accounts'],
            ['name' => 'delete-subscribers',   'description' => 'Delete Subscriber Accounts'],
            ['name' => 'suspend-subscribers',  'description' => 'Suspend/Unsuspend Subscribers'],
            ['name' => 'assign-plans',     'description' => 'Assign Subscription Plans'],
            ['name' => 'manage-plans',     'description' => 'Manage Subscription Plans'],
            ['name' => 'view-all-payments','description' => 'View All Subscriber Payments'],
        ];

        foreach ($adminSubscriberPermissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name']],
                ['description' => $perm['description']]
            );
        }

        // Create Subscriber role
        $subscriberRole = Role::firstOrCreate(['name' => 'Subscriber', 'guard_name' => 'web']);
        $subscriberRole->syncPermissions(array_column($subscriberPermissions, 'name'));

        // Seed default subscription plans
        $plans = [
            [
                'name'              => 'Starter',
                'slug'              => 'starter',
                'description'       => 'Perfect for small businesses just getting started.',
                'price'             => 499,
                'currency'          => 'INR',
                'duration_days'     => 30,
                'product_limit'     => 50,
                'attribute_limit'   => 20,
                'share_link_limit'  => 100,
                'pdf_sharing'       => true,
                'image_sharing'     => true,
                'watermark_removal' => false,
                'custom_branding'   => false,
                'analytics'         => false,
                'is_trial'          => false,
                'trial_days'        => 14,
                'is_active'         => true,
                'sort_order'        => 0,
                'features'          => ['50 Products', '20 Attributes', '100 Share Links', 'PDF & Image Sharing', 'WhatsApp Sharing'],
            ],
            [
                'name'              => 'Business',
                'slug'              => 'business',
                'description'       => 'For growing businesses with advanced operational needs.',
                'price'             => 1299,
                'currency'          => 'INR',
                'duration_days'     => 30,
                'product_limit'     => 250,
                'attribute_limit'   => 100,
                'share_link_limit'  => 500,
                'pdf_sharing'       => true,
                'image_sharing'     => true,
                'watermark_removal' => true,
                'custom_branding'   => true,
                'analytics'         => true,
                'is_trial'          => false,
                'trial_days'        => 14,
                'is_active'         => true,
                'sort_order'        => 1,
                'features'          => ['250 Products', '100 Attributes', '500 Share Links', 'Custom Branding', 'No Watermark', 'Analytics'],
            ],
            [
                'name'              => 'Enterprise',
                'slug'              => 'enterprise',
                'description'       => 'Unlimited power for large enterprises.',
                'price'             => 3999,
                'currency'          => 'INR',
                'duration_days'     => 30,
                'product_limit'     => 9999,
                'attribute_limit'   => 9999,
                'share_link_limit'  => 9999,
                'pdf_sharing'       => true,
                'image_sharing'     => true,
                'watermark_removal' => true,
                'custom_branding'   => true,
                'analytics'         => true,
                'is_trial'          => false,
                'trial_days'        => 14,
                'is_active'         => true,
                'sort_order'        => 2,
                'features'          => ['Unlimited Products', 'Unlimited Attributes', 'Unlimited Share Links', 'Priority Support', 'Custom Domain Ready'],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('Subscriber role, permissions, and subscription plans seeded successfully.');
    }
}

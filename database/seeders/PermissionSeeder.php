<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'dashboard.analytics',

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            'orders.manage',
            'settings.manage',
            'vendors.manage',

            'brands.view',
            'brands.create',
            'brands.edit',
            'brands.delete',

            'categories.view',
            'categories.create',
            'categories.edit',
            'categories.delete',

            'subscribers.manage',
            'roles.manage',
            'permissions.manage',
            'system.manage',

            'enquiries.view',
            'enquiries.manage',
            'newsletters.view',

            // Legacy compatibility permissions used by the existing codebase
            'view-dashboard',
            'view-brands',
            'create-brands',
            'edit-brands',
            'delete-brands',
            'view-categories',
            'create-categories',
            'edit-categories',
            'delete-categories',
            'view-subcategories',
            'create-subcategories',
            'edit-subcategories',
            'delete-subcategories',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
            'import-products',
            'export-products',
            'view-enquiries',
            'delete-enquiries',
            'mark-enquiries-read',
            'view-newsletters',
            'delete-newsletters',
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles',
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            'view-settings',
            'edit-settings',
            'edit-profile',
            'manage-system',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $subscriber = Role::firstOrCreate(['name' => 'Subscriber']);

        $superAdmin->syncPermissions(Permission::all());

        $subscriberPermissions = Permission::whereIn('name', [
            'dashboard.view',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'view-dashboard',
            'view-products',
            'create-products',
            'edit-products',
            'delete-products',
        ])->get();

        $subscriber->syncPermissions($subscriberPermissions);

        $firstUser = User::first();
        if ($firstUser && ! $firstUser->hasRole('Super Admin')) {
            $firstUser->assignRole('Super Admin');
        }
    }
}

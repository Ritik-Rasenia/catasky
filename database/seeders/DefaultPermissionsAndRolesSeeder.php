<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DefaultPermissionsAndRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Dashboard
            ['name' => 'view-dashboard', 'description' => 'View Dashboard'],

            // Brands
            ['name' => 'view-brands', 'description' => 'View Brands'],
            ['name' => 'create-brands', 'description' => 'Create Brands'],
            ['name' => 'edit-brands', 'description' => 'Edit Brands'],
            ['name' => 'delete-brands', 'description' => 'Delete Brands'],

            // Categories
            ['name' => 'view-categories', 'description' => 'View Categories'],
            ['name' => 'create-categories', 'description' => 'Create Categories'],
            ['name' => 'edit-categories', 'description' => 'Edit Categories'],
            ['name' => 'delete-categories', 'description' => 'Delete Categories'],

            // Subcategories
            ['name' => 'view-subcategories', 'description' => 'View Subcategories'],
            ['name' => 'create-subcategories', 'description' => 'Create Subcategories'],
            ['name' => 'edit-subcategories', 'description' => 'Edit Subcategories'],
            ['name' => 'delete-subcategories', 'description' => 'Delete Subcategories'],

            // Products
            ['name' => 'view-products', 'description' => 'View Products'],
            ['name' => 'create-products', 'description' => 'Create Products'],
            ['name' => 'edit-products', 'description' => 'Edit Products'],
            ['name' => 'delete-products', 'description' => 'Delete Products'],
            ['name' => 'import-products', 'description' => 'Import Products'],
            ['name' => 'export-products', 'description' => 'Export Products'],

            // Enquiries
            ['name' => 'view-enquiries', 'description' => 'View Enquiries'],
            ['name' => 'delete-enquiries', 'description' => 'Delete Enquiries'],
            ['name' => 'mark-enquiries-read', 'description' => 'Mark Enquiries as Read'],

            // Newsletter
            ['name' => 'view-newsletters', 'description' => 'View Newsletters'],
            ['name' => 'delete-newsletters', 'description' => 'Delete Newsletters'],

            // Users
            ['name' => 'view-users', 'description' => 'View Users'],
            ['name' => 'create-users', 'description' => 'Create Users'],
            ['name' => 'edit-users', 'description' => 'Edit Users'],
            ['name' => 'delete-users', 'description' => 'Delete Users'],
            ['name' => 'assign-roles', 'description' => 'Assign Roles to Users'],

            // Roles
            ['name' => 'view-roles', 'description' => 'View Roles'],
            ['name' => 'create-roles', 'description' => 'Create Roles'],
            ['name' => 'edit-roles', 'description' => 'Edit Roles'],
            ['name' => 'delete-roles', 'description' => 'Delete Roles'],

            // Permissions
            ['name' => 'view-permissions', 'description' => 'View Permissions'],
            ['name' => 'create-permissions', 'description' => 'Create Permissions'],
            ['name' => 'edit-permissions', 'description' => 'Edit Permissions'],
            ['name' => 'delete-permissions', 'description' => 'Delete Permissions'],

            // Settings
            ['name' => 'view-settings', 'description' => 'View Settings'],
            ['name' => 'edit-settings', 'description' => 'Edit Settings'],

            // Profile
            ['name' => 'edit-profile', 'description' => 'Edit Own Profile'],

            // System Management
            ['name' => 'manage-system', 'description' => 'Manage System Maintenance and Commands'],

            // Module-level Permissions (Requirement 9)
            ['name' => 'dashboard-access', 'description' => 'Dashboard Access'],
            ['name' => 'user-management', 'description' => 'User Management'],
            ['name' => 'subscriber-management', 'description' => 'Subscriber Management'],
            ['name' => 'plan-management', 'description' => 'Plan Management'],
            ['name' => 'role-management', 'description' => 'Role Management'],
            ['name' => 'permission-management', 'description' => 'Permission Management'],
            ['name' => 'product-management', 'description' => 'Product Management'],
            ['name' => 'category-management', 'description' => 'Category Management'],
            ['name' => 'brand-management', 'description' => 'Brand Management'],
            ['name' => 'order-management', 'description' => 'Order Management'],
            ['name' => 'domain-management', 'description' => 'Domain Management'],
            ['name' => 'reports', 'description' => 'Reports Access'],
            ['name' => 'settings', 'description' => 'Settings Management'],
            ['name' => 'system-configuration', 'description' => 'System Configuration'],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description'] ?? '']
            );
        }

        // Create roles
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);

        // Assign all permissions to Super Admin
        $superAdmin->syncPermissions(Permission::all());
    }
}

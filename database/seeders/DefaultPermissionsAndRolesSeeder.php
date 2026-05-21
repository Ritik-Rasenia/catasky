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

            // Child Categories
            ['name' => 'view-childcategories', 'description' => 'View Child Categories'],
            ['name' => 'create-childcategories', 'description' => 'Create Child Categories'],
            ['name' => 'edit-childcategories', 'description' => 'Edit Child Categories'],
            ['name' => 'delete-childcategories', 'description' => 'Delete Child Categories'],

            // Products
            ['name' => 'view-products', 'description' => 'View Products'],
            ['name' => 'create-products', 'description' => 'Create Products'],
            ['name' => 'edit-products', 'description' => 'Edit Products'],
            ['name' => 'delete-products', 'description' => 'Delete Products'],
            ['name' => 'import-products', 'description' => 'Import Products'],
            ['name' => 'export-products', 'description' => 'Export Products'],

            // Solutions
            ['name' => 'view-solutions', 'description' => 'View Solutions'],
            ['name' => 'create-solutions', 'description' => 'Create Solutions'],
            ['name' => 'edit-solutions', 'description' => 'Edit Solutions'],
            ['name' => 'delete-solutions', 'description' => 'Delete Solutions'],

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
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description'] ?? '']
            );
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $staff = Role::firstOrCreate(['name' => 'Staff']);

        // Assign all permissions to Admin
        $admin->syncPermissions(Permission::all());

        // Assign permissions to Staff (operational access)
        $staffPermissions = Permission::whereNotIn('name', [
            'view-permissions',
            'create-permissions',
            'edit-permissions',
            'delete-permissions',
            'view-roles',
            'create-roles',
            'edit-roles',
            'delete-roles',
            'view-settings',
            'edit-settings',
            'manage-system',
            'create-users',
            'edit-users',
            'delete-users',
            'assign-roles'
        ])->pluck('id')->toArray();
        $staff->syncPermissions($staffPermissions);
    }
}

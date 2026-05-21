<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions (scoped per module)
        $permissions = [
            // Users
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Roles & Permissions
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',

            // Products
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Newsletters / enquiries
            'enquiries.view',
            'enquiries.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Roles and their permissions
        $roles = [
            'admin' => Permission::pluck('name')->toArray(),
            'manager' => [
                'users.view', 'products.view', 'products.create', 'products.edit', 'enquiries.view'
            ],
            'editor' => [
                'products.view', 'products.create', 'products.edit'
            ],
            'viewer' => [
                'products.view'
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        // Assign admin role to the first user (if exists)
        $user = User::first();
        if ($user) {
            $user->assignRole('admin');
        }
    }
}

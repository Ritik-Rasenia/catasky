<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class AssignAllPermissionsToAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:assign-admin-permissions';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Assign all permissions to admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Find admin user (first user or user with admin role)
        $admin = User::where('email', 'like', '%admin%')
                    ->orWhere('name', 'like', '%admin%')
                    ->first();

        if (!$admin) {
            // If no admin found, try first user
            $admin = User::first();
        }

        if (!$admin) {
            $this->error('No users found in the system!');
            return 1;
        }

        $this->info("Found admin user: {$admin->name} ({$admin->email})");

        // Get all permissions
        $permissions = Permission::all();
        $permissionCount = $permissions->count();

        // Assign all permissions
        $admin->syncPermissions($permissions);

        $this->info("✅ Successfully assigned {$permissionCount} permissions to {$admin->name}");
        $this->info("User {$admin->name} now has all system permissions!");

        return 0;
    }
}

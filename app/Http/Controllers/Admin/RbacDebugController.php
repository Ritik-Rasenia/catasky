<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Gate;

class RbacDebugController extends Controller
{
    /**
     * Display a security audit debug overview for the current user.
     */
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'User not authenticated.');
        }

        // Get user roles and direct/all permissions
        $roles = $user->getRoleNames();
        $directPermissions = $user->getDirectPermissions()->pluck('name');
        $rolePermissions = $user->getPermissionsViaRoles()->pluck('name');
        $allPermissions = $user->getAllPermissions()->pluck('name');

        // Core system modules to audit
        $modules = [
            [
                'name' => 'Dashboard Console',
                'description' => 'Main administration panel landing page & analytics overview.',
                'permission' => 'dashboard.view',
                'category' => 'Core'
            ],
            [
                'name' => 'System Analytics',
                'description' => 'Vibrant chart reporting, visitor metrics, and subscriber analytics.',
                'permission' => 'dashboard.analytics',
                'category' => 'Analytics'
            ],
            [
                'name' => 'Brand Management',
                'description' => 'Create, read, update, and delete catalog manufacturers/brands.',
                'permission' => 'brands.view',
                'category' => 'Catalogue'
            ],
            [
                'name' => 'Category Management',
                'description' => 'Manage high-level catalog product categories and mapping structures.',
                'permission' => 'categories.view',
                'category' => 'Catalogue'
            ],
            [
                'name' => 'Subcategory Management',
                'description' => 'Manage nested catalog subcategories and category associations.',
                'permission' => 'categories.view',
                'category' => 'Catalogue'
            ],
            [
                'name' => 'Products Management',
                'description' => 'View, modify, import, and export global administration catalog products.',
                'permission' => 'products.view',
                'category' => 'Catalogue'
            ],
            [
                'name' => 'B2B Enquiries',
                'description' => 'View corporate customer catalog inquiries, mark as read, and delete logs.',
                'permission' => 'enquiries.view',
                'category' => 'Engagement'
            ],
            [
                'name' => 'Newsletters',
                'description' => 'Manage newsletter subscriber mailing lists and remove inactive entries.',
                'permission' => 'newsletters.view',
                'category' => 'Engagement'
            ],
            [
                'name' => 'Subscribers List',
                'description' => 'Manage and approve subscriber profiles, stores, and custom domains.',
                'permission' => 'subscribers.manage',
                'category' => 'SaaS Management'
            ],
            [
                'name' => 'Subscription Plans',
                'description' => 'Configure subscription tier prices, limits, features, and sort orders.',
                'permission' => 'subscribers.manage',
                'category' => 'SaaS Management'
            ],
            [
                'name' => 'Global Settings',
                'description' => 'Manage administrative settings, site logo, title, and emails.',
                'permission' => 'settings.manage',
                'category' => 'System'
            ],
            [
                'name' => 'System Tools',
                'description' => 'Run artisan commands, clear system logs, and trigger symlink actions.',
                'permission' => 'system.manage',
                'category' => 'System'
            ],
            [
                'name' => 'Users Administration',
                'description' => 'Manage global admin logins, assign roles, and delete backend accounts.',
                'permission' => 'users.view',
                'category' => 'Access Control'
            ],
            [
                'name' => 'Roles & Policies',
                'description' => 'Create roles, edit permissions matrix, and configure standard RBAC rules.',
                'permission' => 'roles.manage',
                'category' => 'Access Control'
            ],
            [
                'name' => 'Permissions Registry',
                'description' => 'Configure low-level custom permission entries and granular API keys.',
                'permission' => 'permissions.manage',
                'category' => 'Access Control'
            ]
        ];

        $accessibleModules = [];
        $restrictedModules = [];

        foreach ($modules as $module) {
            if ($user->can($module['permission'])) {
                $accessibleModules[] = $module;
            } else {
                $restrictedModules[] = $module;
            }
        }

        // Database health statistics for security console
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $totalUsers = \App\Models\User::count();

        return view('admin.rbac-debug', compact(
            'user',
            'roles',
            'directPermissions',
            'rolePermissions',
            'allPermissions',
            'accessibleModules',
            'restrictedModules',
            'totalRoles',
            'totalPermissions',
            'totalUsers'
        ));
    }
}

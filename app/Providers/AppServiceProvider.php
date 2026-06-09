<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Pagination\Paginator;

use App\Events\Analytics\VisitLogged;
use App\Listeners\Analytics\RecordVisit;
use App\Events\Analytics\ProductViewed;
use App\Listeners\Analytics\RecordProductView;
use App\Events\Analytics\DownloadLogged;
use App\Listeners\Analytics\RecordDownload;
use App\Events\Analytics\OrderLogged;
use App\Listeners\Analytics\RecordOrder;
use App\Events\Analytics\EngagementLogged;
use App\Listeners\Analytics\RecordEngagement;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Event::listen(VisitLogged::class, RecordVisit::class);
        Event::listen(ProductViewed::class, RecordProductView::class);
        Event::listen(DownloadLogged::class, RecordDownload::class);
        Event::listen(OrderLogged::class, RecordOrder::class);
        Event::listen(EngagementLogged::class, RecordEngagement::class);

        // Implicitly grant "Super Admin" and "Admin" roles all permissions and dynamically map permissions for other roles
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->hasAnyRole(['Super Admin', 'Admin', 'admin'])) {
                return true;
            }

            // Normalise legacy dot format checks to standard hyphenated action-level checks (e.g. 'brands.view' to 'view-brands')
            $normalizedAbility = $ability;
            $parts = explode('.', $ability);
            if (count($parts) === 2) {
                $module = $parts[0];
                $action = $parts[1];
                if ($action === 'view') {
                    $normalizedAbility = 'view-' . $module;
                } elseif ($action === 'manage') {
                    $normalizedAbility = 'view-' . $module; // Default fallback for manage checks
                }
            }

            // High-level module mapping (Requirement 9)
            // Maps granular permissions and format variations to high-level parent module permissions
            $moduleMappings = [
                'view-dashboard' => ['dashboard-access'],
                'dashboard.view' => ['dashboard-access'],
                'dashboard.analytics' => ['reports'],
                
                'view-brands' => ['brand-management'],
                'create-brands' => ['brand-management'],
                'edit-brands' => ['brand-management'],
                'delete-brands' => ['brand-management'],
                'brands.view' => ['brand-management'],
                
                'view-categories' => ['category-management'],
                'create-categories' => ['category-management'],
                'edit-categories' => ['category-management'],
                'delete-categories' => ['category-management'],
                'categories.view' => ['category-management'],
                
                'view-subcategories' => ['category-management'],
                'create-subcategories' => ['category-management'],
                'edit-subcategories' => ['category-management'],
                'delete-subcategories' => ['category-management'],
                
                'view-products' => ['product-management'],
                'create-products' => ['product-management'],
                'edit-products' => ['product-management'],
                'delete-products' => ['product-management'],
                'import-products' => ['product-management'],
                'export-products' => ['product-management'],
                'products.view' => ['product-management'],
                
                'view-enquiries' => ['order-management'],
                'delete-enquiries' => ['order-management'],
                'mark-enquiries-read' => ['order-management'],
                'enquiries.view' => ['order-management'],
                
                'view-users' => ['user-management'],
                'create-users' => ['user-management'],
                'edit-users' => ['user-management'],
                'delete-users' => ['user-management'],
                'assign-roles' => ['user-management'],
                'users.view' => ['user-management'],
                
                'view-roles' => ['role-management'],
                'create-roles' => ['role-management'],
                'edit-roles' => ['role-management'],
                'delete-roles' => ['role-management'],
                'roles.manage' => ['role-management'],
                
                'view-permissions' => ['permission-management'],
                'create-permissions' => ['permission-management'],
                'edit-permissions' => ['permission-management'],
                'delete-permissions' => ['permission-management'],
                'permissions.manage' => ['permission-management'],
                
                'view-settings' => ['settings'],
                'edit-settings' => ['settings'],
                'settings.manage' => ['settings'],
                
                'manage-system' => ['system-configuration'],
                'system.manage' => ['system-configuration'],
                
                'view-newsletters' => ['reports'],
                'delete-newsletters' => ['reports'],
                'newsletters.view' => ['reports'],

                'view-analytics' => ['reports'],
                'dashboard.analytics' => ['reports'],
                'admin.analytics' => ['reports'],
                'subscriber.analytics' => ['reports'],

                'subscribers.manage' => ['subscriber-management', 'plan-management', 'domain-management'],
            ];

            // Helper to safely check permissions without throwing Spatie PermissionDoesNotExist exceptions
            $hasPerm = function ($permName) use ($user) {
                try {
                    return $user->hasPermissionTo($permName);
                } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                    return false;
                }
            };

            // If user has the normalized ability directly, return true
            if ($hasPerm($normalizedAbility)) {
                return true;
            }

            // Check if user has any parent module-level permission assigned
            if (isset($moduleMappings[$ability])) {
                foreach ($moduleMappings[$ability] as $parentPermission) {
                    if ($hasPerm($parentPermission)) {
                        return true;
                    }
                }
            }
            if (isset($moduleMappings[$normalizedAbility])) {
                foreach ($moduleMappings[$normalizedAbility] as $parentPermission) {
                    if ($hasPerm($parentPermission)) {
                        return true;
                    }
                }
            }

            return null; // Fall back to standard checks
        });

        // Blade conditional helper for permissions using Laravel policies
        Blade::if('permission', function ($permission) {
            return auth()->check() && auth()->user()->can($permission);
        });

        // Database validation: automatically sync missing permissions (Requirement 9)
        if (app()->runningInConsole() === false) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                    $dbCount = \Spatie\Permission\Models\Permission::count();
                    if ($dbCount < 69) {
                        // Automatically run seeders to sync missing permissions
                        \Illuminate\Support\Facades\Artisan::call('db:seed', [
                            '--class' => 'Database\Seeders\DefaultPermissionsAndRolesSeeder'
                        ]);
                        \Illuminate\Support\Facades\Artisan::call('db:seed', [
                            '--class' => 'Database\Seeders\SubscriberRoleAndPlansSeeder'
                        ]);
                        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                    }
                }
            } catch (\Exception $e) {
                // Prevent database errors from breaking during migrations or initial setup
            }
        }
    }
}

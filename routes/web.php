<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\SubscriberController as AdminSubscriberController;
use App\Http\Controllers\Admin\AttributeController as AdminAttributeController;
use App\Http\Controllers\Admin\TemplateController as AdminTemplateController;
use App\Http\Controllers\Subscriber\Auth\SubscriberAuthController;
use App\Http\Controllers\Subscriber\DashboardController as SubscriberDashboardController;
use App\Http\Controllers\Subscriber\ProductController as SubscriberProductController;
use App\Http\Controllers\Subscriber\AttributeController;
use App\Http\Controllers\Subscriber\AttributeGroupController;
use App\Http\Controllers\Subscriber\ShareController;
use App\Http\Controllers\Subscriber\SubscriptionController;
use App\Http\Controllers\Subscriber\ProfileController as SubscriberProfileController;
use App\Http\Controllers\Subscriber\VariantController as SubscriberVariantController;
use App\Http\Controllers\Subscriber\InventoryController as SubscriberInventoryController;
use App\Http\Controllers\Subscriber\BulkUploadController as SubscriberBulkUploadController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\FrontendAnalyticsController;
use App\Http\Controllers\TrackingRedirectController;



/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/catalog', [FrontendController::class, 'catalogue'])->name('catalogue');
Route::get('/catalogue', function () {
    return redirect()->route('catalogue', request()->query());
});
Route::get('/privacy-policy', [FrontendController::class, 'privacyPolicy'])->name('privacy.policy');
Route::get('/refund-policy', [FrontendController::class, 'refundPolicy'])->name('refund.policy');
Route::get('/terms-and-conditions', [FrontendController::class, 'termsConditions'])->name('terms.conditions');
Route::get('/demo', [FrontendController::class, 'demoCatalogue'])->name('demo');
Route::get('/brands', [FrontendController::class, 'brands'])->name('brands');
Route::get('/categories', [FrontendController::class, 'categories'])->name('categories');
Route::get('/sub-categories', [FrontendController::class, 'subcategories'])->name('subcategories');
Route::get('/category/{slug}', [FrontendController::class, 'categorySubcategories'])->name('category.subcategories');
Route::get('/category-products/{slug}', [FrontendController::class, 'categoryProducts'])->name('category.products');
Route::get('/subcategory/{slug}', [FrontendController::class, 'subcategoryProducts'])->name('subcategory.products');
Route::get('/product/{slug}', [FrontendController::class, 'productDetails'])->name('product.details');
Route::get('/brand/{slug}', [FrontendController::class, 'brandDetails'])->name('brand.details');
Route::get('/brand/{slug}/products', [FrontendController::class, 'brandProducts'])->name('brand.products');
Route::get('/search', [FrontendController::class, 'search'])->name('search');
Route::get('/part-list', [FrontendController::class, 'partList'])->name('part.list');
Route::get('/contact-us', [FrontendController::class, 'contact'])->name('contact');
Route::post('/enquiry/submit', [FrontendController::class, 'enquirySubmit'])->name('enquiry.submit');
Route::post('/newsletter/submit', [FrontendController::class, 'newsletterSubmit'])->name('newsletter.submit');
Route::get('/thank-you', [FrontendController::class, 'thankYou'])->name('thank.you');

// DoubleTick.io Sharing & Tracking
Route::post('/api/doubletick/share', [App\Http\Controllers\DoubleTickController::class, 'share'])->name('doubletick.share');
Route::get('/c/{code}', [App\Http\Controllers\DoubleTickController::class, 'viewCatalogue'])->name('doubletick.view');
Route::post('/api/catalogue-track/heartbeat', [App\Http\Controllers\DoubleTickController::class, 'heartbeat'])->name('doubletick.heartbeat');
Route::post('/api/doubletick/webhook', [App\Http\Controllers\DoubleTickController::class, 'webhook'])->name('doubletick.webhook');
Route::post('/api/pdf/upload-temp', [App\Http\Controllers\DoubleTickController::class, 'uploadTempPdf'])->name('pdf.upload-temp');
Route::get('/catalogues/pdf/{filename}', [App\Http\Controllers\DoubleTickController::class, 'downloadPdf'])->name('pdf.download');
Route::post('/api/image/upload-temp', [App\Http\Controllers\DoubleTickController::class, 'uploadTempImage'])->name('image.upload-temp');
Route::get('/catalogues/image/{filename}', [App\Http\Controllers\DoubleTickController::class, 'downloadImage'])->name('image.download');

// SEO Landing Pages
Route::get('/panduit-distributor-india', [FrontendController::class, 'panduitLanding'])->name('panduit.distributor');
Route::get('/panduit-distributor-india/', [FrontendController::class, 'panduitLanding']);
Route::get('/legrand-distributor-india', [FrontendController::class, 'legrandLanding'])->name('legrand.distributor');
Route::get('/structured-cabling-solutions-india', [FrontendController::class, 'structuredCablingLanding'])->name('structured.cabling');
Route::get('/datacenter-solutions-india', [FrontendController::class, 'datacenterLanding'])->name('datacenter.solutions');
Route::get('/it-infrastructure-solutions-india', [FrontendController::class, 'itInfrastructureLanding'])->name('it.infrastructure');
Route::get('/apisubcategories/{category_id}', [FrontendController::class, 'getApiSubcategories']);
Route::get('/apifeatured-products', [FrontendController::class, 'getApiFeaturedProducts']);
Route::get('/api/products-details', [FrontendController::class, 'apiProductsDetails']);
Route::get('/api/product-details/{id}', [FrontendController::class, 'apiProductDetails']);
Route::get('/product/{id}/details', [FrontendController::class, 'productQuickView'])->name('product.quickview');
Route::get('/pricing', [FrontendController::class, 'pricing'])->name('pricing');
Route::get('/manifest.json', [FrontendController::class, 'storeManifest'])->name('main.manifest');
Route::get('/store/{company_slug}/manifest.json', [FrontendController::class, 'storeManifest'])->name('store.manifest');
Route::get('/store/{company_slug}', [FrontendController::class, 'storeCatalog'])->name('store.catalog');
Route::get('/store/{company_slug}/contact', [FrontendController::class, 'storeContact'])->name('store.contact');
Route::get('/subscriber_store/{company_slug}', [FrontendController::class, 'storeCatalog'])->name('subscriber_store');
Route::get('/subscriber_store/{company_slug}/contact', [FrontendController::class, 'storeContact'])->name('subscriber_store.contact');

/*
|--------------------------------------------------------------------------
| Guest Auth Routes
|--------------------------------------------------------------------------
*/
Route::get('/secure-admin-login', [AuthController::class, 'login'])->name('login');

Route::middleware('guest')->group(function () {
    Route::post('/secure-admin-login', [AuthController::class, 'loginSubmit'])->name('login.submit');
});

Route::prefix('subscriber')->name('subscriber.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [SubscriberAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [SubscriberAuthController::class, 'login'])->name('login.submit');
        Route::get('/register', [SubscriberAuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [SubscriberAuthController::class, 'register'])->name('register.submit');
        Route::get('/verify-otp', [SubscriberAuthController::class, 'showOtpForm'])->name('verify-otp');
        Route::post('/verify-otp', [SubscriberAuthController::class, 'verifyOtp'])->name('verify-otp.submit');
        Route::get('/forgot-password', [SubscriberAuthController::class, 'showForgotForm'])->name('forgot');
        Route::post('/forgot-password', [SubscriberAuthController::class, 'sendResetLink'])->name('forgot.submit');
        Route::get('/reset-password/{token}', [SubscriberAuthController::class, 'showResetPasswordForm'])->name('reset-password');
        Route::post('/reset-password', [SubscriberAuthController::class, 'resetPassword'])->name('reset-password.submit');
        Route::post('/resend-otp', [SubscriberAuthController::class, 'resendOtp'])->name('resend-otp');
    });
});

/*
|--------------------------------------------------------------------------
| UNIFIED DASHBOARD SYSTEM (ALL AUTHENTICATED USERS)
|--------------------------------------------------------------------------
*/
Route::prefix('dashboard')->middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Shared / Overlapping Dispatcher Routes
    |--------------------------------------------------------------------------
    */
    // Dashboard Home
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/admin-redirect', function () { return redirect()->route('dashboard'); })->name('admin.dashboard');
    Route::get('/subscriber-redirect', function () { return redirect()->route('dashboard'); })->name('subscriber.dashboard');

    // --- Products Dispatcher ---
    // Admin Products
    Route::get('/admin/products', [App\Http\Controllers\DashboardDispatcherController::class, 'productsIndex'])->name('admin.products.index');
    Route::get('/admin/products/create', [App\Http\Controllers\DashboardDispatcherController::class, 'productsCreate'])->name('admin.products.create');
    Route::post('/admin/products', [App\Http\Controllers\DashboardDispatcherController::class, 'productsStore'])->name('admin.products.store');
    Route::get('/admin/products/{product}', [App\Http\Controllers\DashboardDispatcherController::class, 'productsShow'])->name('admin.products.show');
    Route::get('/admin/products/{product}/edit', [App\Http\Controllers\DashboardDispatcherController::class, 'productsEdit'])->name('admin.products.edit');
    Route::put('/admin/products/{product}', [App\Http\Controllers\DashboardDispatcherController::class, 'productsUpdate'])->name('admin.products.update');
    Route::delete('/admin/products/{product}', [App\Http\Controllers\DashboardDispatcherController::class, 'productsDestroy'])->name('admin.products.destroy');

    // Subscriber Products
    Route::get('/products', [App\Http\Controllers\DashboardDispatcherController::class, 'productsIndex'])->name('subscriber.products.index');
    Route::get('/products/create', [App\Http\Controllers\DashboardDispatcherController::class, 'productsCreate'])->name('subscriber.products.create');
    Route::post('/products', [App\Http\Controllers\DashboardDispatcherController::class, 'productsStore'])->name('subscriber.products.store');
    Route::get('/products/{product}', [App\Http\Controllers\DashboardDispatcherController::class, 'productsShow'])->name('subscriber.products.show');
    Route::get('/products/{product}/edit', [App\Http\Controllers\DashboardDispatcherController::class, 'productsEdit'])->name('subscriber.products.edit');
    Route::put('/products/{product}', [App\Http\Controllers\DashboardDispatcherController::class, 'productsUpdate'])->name('subscriber.products.update');
    Route::delete('/products/{product}', [App\Http\Controllers\DashboardDispatcherController::class, 'productsDestroy'])->name('subscriber.products.destroy');


    // --- Attributes Dispatcher ---
    // Admin Attributes
    Route::get('/admin/attributes', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesIndex'])->name('admin.attributes.index');
    Route::get('/admin/attributes/create', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesCreate'])->name('admin.attributes.create');
    Route::post('/admin/attributes', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesStore'])->name('admin.attributes.store');
    Route::get('/admin/attributes/{attribute}', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesShow'])->name('admin.attributes.show');
    Route::get('/admin/attributes/{attribute}/edit', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesEdit'])->name('admin.attributes.edit');
    Route::put('/admin/attributes/{attribute}', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesUpdate'])->name('admin.attributes.update');
    Route::delete('/admin/attributes/{attribute}', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesDestroy'])->name('admin.attributes.destroy');

    // Subscriber Attributes
    Route::get('/attributes', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesIndex'])->name('subscriber.attributes.index');
    Route::get('/attributes/create', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesCreate'])->name('subscriber.attributes.create');
    Route::post('/attributes', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesStore'])->name('subscriber.attributes.store');
    Route::get('/attributes/{attribute}', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesShow'])->name('subscriber.attributes.show');
    Route::get('/attributes/{attribute}/edit', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesEdit'])->name('subscriber.attributes.edit');
    Route::put('/attributes/{attribute}', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesUpdate'])->name('subscriber.attributes.update');
    Route::delete('/attributes/{attribute}', [App\Http\Controllers\DashboardDispatcherController::class, 'attributesDestroy'])->name('subscriber.attributes.destroy');
    Route::post('/attributes/custom', [App\Http\Controllers\Subscriber\AttributeController::class, 'storeCustom'])->name('subscriber.attributes.storeCustom');


    // --- Profile Dispatcher ---
    // Admin Profile
    Route::get('/admin/profile', [App\Http\Controllers\DashboardDispatcherController::class, 'profileEdit'])->name('admin.profile.edit');
    Route::post('/admin/profile', [App\Http\Controllers\DashboardDispatcherController::class, 'profileUpdate'])->name('admin.profile.update');
    Route::post('/admin/profile/password', [App\Http\Controllers\DashboardDispatcherController::class, 'profilePassword'])->name('admin.profile.password');

    // Subscriber Profile
    Route::get('/profile', [App\Http\Controllers\DashboardDispatcherController::class, 'profileEdit'])->name('subscriber.profile.edit');
    Route::post('/profile', [App\Http\Controllers\DashboardDispatcherController::class, 'profileUpdate'])->name('subscriber.profile.update');
    Route::post('/profile/password', [App\Http\Controllers\DashboardDispatcherController::class, 'profilePassword'])->name('subscriber.profile.password');

    /*
    |--------------------------------------------------------------------------
    | SUPER ADMIN & STAFF-ONLY ROUTES (Protected by superadmin middleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin_panel')->name('admin.')->group(function () {
        // Reports Access (Analytics Dashboard)
        Route::get('/tracking-analytics', [App\Http\Controllers\DoubleTickController::class, 'analyticsDashboard'])
            ->name('tracking.analytics')
            ->middleware('permission:reports');

        // Advanced Analytics
        Route::get('/admin/analytics', [AnalyticsController::class, 'adminAnalytics'])->name('analytics')->middleware('permission:reports');
        Route::get('/admin/analytics/timeline/{visitorUuid}', [AnalyticsController::class, 'activityTimeline'])->name('analytics.timeline')->middleware('permission:reports');
        Route::get('/admin/analytics/export', [AnalyticsController::class, 'exportExcel'])->name('analytics.export')->middleware('permission:reports');
        Route::get('/admin/analytics/realtime', [AnalyticsController::class, 'realtimeData'])->name('analytics.realtime')->middleware('permission:reports');

        // Frontend-Only Analytics Dashboard
        Route::get('/admin/frontend-analytics', [FrontendAnalyticsController::class, 'adminDashboard'])->name('frontend-analytics');
        Route::get('/admin/frontend-analytics/realtime', [FrontendAnalyticsController::class, 'adminRealtime'])->name('frontend-analytics.realtime');
        Route::get('/admin/frontend-analytics/export', [FrontendAnalyticsController::class, 'adminExport'])->name('frontend-analytics.export');

        // --- BRANDS (Granular Access) ---
        Route::group(['middleware' => ['permission:create-brands']], function () {
            Route::get('brands/create', [BrandController::class, 'create'])->name('brands.create');
            Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
            Route::post('/brands/quick-store', [BrandController::class, 'quickStore'])->name('brands.quick-store');
        });
        Route::group(['middleware' => ['permission:edit-brands']], function () {
            Route::get('brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
            Route::put('brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        });
        Route::group(['middleware' => ['permission:delete-brands']], function () {
            Route::delete('brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');
        });
        Route::group(['middleware' => ['permission:view-brands']], function () {
            Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
            Route::get('brands/{brand}', [BrandController::class, 'show'])->name('brands.show');
        });

        // --- CATEGORIES (Granular Access) ---
        Route::group(['middleware' => ['permission:create-categories']], function () {
            Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::post('/categories/quick-store', [CategoryController::class, 'quickStore'])->name('categories.quick-store');
        });
        Route::group(['middleware' => ['permission:edit-categories']], function () {
            Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        });
        Route::group(['middleware' => ['permission:delete-categories']], function () {
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });
        Route::group(['middleware' => ['permission:view-categories']], function () {
            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
            Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        });

        // --- SUBCATEGORIES (Granular Access) ---
        Route::group(['middleware' => ['permission:create-subcategories']], function () {
            Route::get('subcategories/create', [SubcategoryController::class, 'create'])->name('subcategories.create');
            Route::post('subcategories', [SubcategoryController::class, 'store'])->name('subcategories.store');
            Route::post('/subcategories/quick-store', [SubcategoryController::class, 'quickStore'])->name('subcategories.quick-store');
        });
        Route::group(['middleware' => ['permission:edit-subcategories']], function () {
            Route::get('subcategories/{subcategory}/edit', [SubcategoryController::class, 'edit'])->name('subcategories.edit');
            Route::put('subcategories/{subcategory}', [SubcategoryController::class, 'update'])->name('subcategories.update');
        });
        Route::group(['middleware' => ['permission:delete-subcategories']], function () {
            Route::delete('subcategories/{subcategory}', [SubcategoryController::class, 'destroy'])->name('subcategories.destroy');
        });
        Route::group(['middleware' => ['permission:view-subcategories']], function () {
            Route::get('subcategories', [SubcategoryController::class, 'index'])->name('subcategories.index');
            Route::get('subcategories/{subcategory}', [SubcategoryController::class, 'show'])->name('subcategories.show');
        });

        // AJAX Routes for Dependent Dropdowns
        Route::get('/get-subcategories/{category_id}', [CategoryController::class, 'getSubcategories']);

        // --- USERS (Granular Access) ---
        Route::group(['middleware' => ['permission:create-users']], function () {
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
        });
        Route::group(['middleware' => ['permission:edit-users']], function () {
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        });
        Route::group(['middleware' => ['permission:delete-users']], function () {
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });
        Route::group(['middleware' => ['permission:view-users']], function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        });

        // --- ROLES (Granular Access) ---
        Route::group(['middleware' => ['permission:create-roles']], function () {
            Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        });
        Route::group(['middleware' => ['permission:edit-roles']], function () {
            Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });
        Route::group(['middleware' => ['permission:delete-roles']], function () {
            Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        });
        Route::group(['middleware' => ['permission:view-roles']], function () {
            Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        });

        // --- PERMISSIONS (Granular Access) ---
        Route::group(['middleware' => ['permission:create-permissions']], function () {
            Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
            Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store');
        });
        Route::group(['middleware' => ['permission:edit-permissions']], function () {
            Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
            Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
        });
        Route::group(['middleware' => ['permission:delete-permissions']], function () {
            Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');
        });
        Route::group(['middleware' => ['permission:view-permissions']], function () {
            Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
            Route::get('permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
        });

        // --- NEWSLETTERS (Granular Access) ---
        Route::group(['middleware' => ['permission:view-newsletters']], function () {
            Route::get('newsletters', [NewsletterController::class, 'index'])->name('newsletters.index');
        });
        Route::group(['middleware' => ['permission:delete-newsletters']], function () {
            Route::delete('newsletters/{newsletter}', [NewsletterController::class, 'destroy'])->name('newsletters.destroy');
        });

        // --- TEMPLATES & ATTRIBUTES (Category & Product Management) ---
        Route::group(['middleware' => ['permission:product-management']], function () {
            Route::resource('templates', AdminTemplateController::class);
            Route::post('/attributes/group', [AdminAttributeController::class, 'storeGroup'])->name('attributes.storeGroup');
            Route::get('/saas/approvals/custom-fields', [AdminAttributeController::class, 'approvals'])->name('saas.approvals.custom-fields');
            Route::post('/attributes/{attribute}/approve', [AdminAttributeController::class, 'approve'])->name('attributes.approve');
            Route::post('/attributes/{attribute}/reject', [AdminAttributeController::class, 'reject'])->name('attributes.reject');
            Route::get('/attributes/subcategory/{subcategory}', [AdminAttributeController::class, 'forSubcategory'])->name('attributes.forSubcategory');
        });

        // --- PRODUCTS IMPORT/EXPORT (Product Management) ---
        Route::group(['middleware' => ['permission:product-management']], function () {
            Route::get('/admin/products-ops/import/template', [AdminProductController::class, 'downloadTemplate'])->name('products.import.template');
            Route::get('/admin/products-ops/import', [AdminProductController::class, 'importPage'])->name('products.import');
            Route::post('/admin/products-ops/import', [AdminProductController::class, 'import'])->name('products.import.submit');
            Route::get('/admin/products-ops/import/status/{id}', [AdminProductController::class, 'importStatus'])->name('products.import.status');
            Route::get('/admin/products-ops/import-logs', [AdminProductController::class, 'importLogs'])->name('products.import-logs');
            Route::get('/admin/products-ops/import-logs/{id}', [AdminProductController::class, 'importLogShow'])->name('products.import-logs.show');
            Route::get('/admin/products-ops/import-logs/{id}/download-errors', [AdminProductController::class, 'downloadImportErrors'])->name('products.import-logs.download-errors');
            Route::get('/admin/products-ops/export', [AdminProductController::class, 'export'])->name('products.export');
            Route::delete('/admin/product-images/{image}', [AdminProductController::class, 'deleteImage'])->name('product-images.destroy');
        });

        Route::group(['middleware' => ['permission:delete-products']], function () {
            Route::delete('/admin/subscriber-products/{id}', [AdminProductController::class, 'destroySubscriberProduct'])->name('subscriber-products.destroy');
        });

        // --- ENQUIRIES (Granular Access) ---
        Route::group(['middleware' => ['permission:view-enquiries']], function () {
            Route::get('/enquiries', [\App\Http\Controllers\Admin\EnquiryController::class, 'index'])->name('enquiries.index');
            Route::get('/enquiries/{id}', [\App\Http\Controllers\Admin\EnquiryController::class, 'show'])->name('enquiries.show');
        });
        Route::group(['middleware' => ['permission:delete-enquiries']], function () {
            Route::delete('/enquiries/{id}', [\App\Http\Controllers\Admin\EnquiryController::class, 'destroy'])->name('enquiries.destroy');
        });
        Route::group(['middleware' => ['permission:mark-enquiries-read']], function () {
            Route::post('/enquiries/{id}/mark-as-read', [\App\Http\Controllers\Admin\EnquiryController::class, 'markAsRead'])->name('enquiries.mark-as-read');
        });

        // --- SETTINGS (Granular Access) ---
        Route::group(['middleware' => ['permission:view-settings']], function () {
            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        });
        Route::group(['middleware' => ['permission:edit-settings']], function () {
            Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
        });

        // --- SYSTEM CONFIGURATION ---
        Route::group(['middleware' => ['permission:manage-system']], function () {
            Route::get('/system', [SystemController::class, 'index'])->name('system.index');
            Route::post('/system/command', [SystemController::class, 'runCommand'])->name('system.command');
            Route::post('/system/storage-link', [SystemController::class, 'storageLink'])->name('system.storage-link');
            Route::post('/system/clear-logs', [SystemController::class, 'clearLogs'])->name('system.clear-logs');
        });

        // --- SUBSCRIBER, DOMAIN, PLANS, PAYMENTS & SaaS (Subscriber Management) ---
        Route::group(['middleware' => ['permission:subscribers.manage']], function () {
            // Subscribers
            Route::get('/subscribers', [AdminSubscriberController::class, 'index'])->name('subscribers.index');
            Route::get('/subscribers/{user}', [AdminSubscriberController::class, 'show'])->name('subscribers.show');
            Route::delete('/subscribers/{user}', [AdminSubscriberController::class, 'destroy'])->name('subscribers.destroy');
            Route::post('/subscribers/{user}/suspend', [AdminSubscriberController::class, 'suspend'])->name('subscribers.suspend');
            Route::post('/subscribers/{user}/unsuspend', [AdminSubscriberController::class, 'unsuspend'])->name('subscribers.unsuspend');
            Route::post('/subscribers/{user}/assign-plan', [AdminSubscriberController::class, 'assignPlan'])->name('subscribers.assign-plan');

            // Plans
            Route::get('/subscription-plans', [AdminSubscriberController::class, 'plans'])->name('subscription-plans.index');
            Route::post('/subscription-plans', [AdminSubscriberController::class, 'storePlan'])->name('subscription-plans.store');
            Route::put('/subscription-plans/{subscriptionPlan}', [AdminSubscriberController::class, 'updatePlan'])->name('subscription-plans.update');

            // SaaS prefix
            Route::prefix('saas')->name('saas.')->group(function () {
                Route::get('/subscribers', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'subscribers'])->name('subscribers.index');
                Route::post('/subscribers/{user}/suspend', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'suspendSubscriber'])->name('subscribers.suspend');
                Route::post('/subscribers/{user}/unsuspend', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'unsuspendSubscriber'])->name('subscribers.unsuspend');
                
                Route::get('/approvals', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'approvals'])->name('approvals.index');
                Route::post('/approvals/account/{profile}/approve', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'approveAccount'])->name('approvals.account.approve');
                Route::post('/approvals/account/{profile}/reject', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'rejectAccount'])->name('approvals.account.reject');
                Route::post('/approvals/store/{profile}/approve', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'approveStore'])->name('approvals.store.approve');
                Route::post('/approvals/store/{profile}/reject', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'rejectStore'])->name('approvals.store.reject');
                Route::post('/approvals/product/{product}/approve', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'approveProduct'])->name('approvals.product.approve');
                Route::post('/approvals/product/{product}/reject', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'rejectProduct'])->name('approvals.product.reject');
                Route::post('/approvals/share/{shareLink}/approve', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'approveShare'])->name('approvals.share.approve');
                Route::post('/approvals/share/{shareLink}/reject', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'rejectShare'])->name('approvals.share.reject');

                Route::get('/domains', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'index'])->name('domains.index');
                Route::get('/domains/{domain}', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'show'])->name('domains.show');
                Route::post('/domains/{domain}/verify', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'verify'])->name('domains.verify');
                Route::post('/domains/{domain}/approve', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'approve'])->name('domains.approve');
                Route::post('/domains/{domain}/reject', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'reject'])->name('domains.reject');
                Route::post('/domains/{domain}/suspend', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'suspend'])->name('domains.suspend');
                Route::post('/domains/{domain}/regenerate-ssl', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'regenerateSsl'])->name('domains.regenerate-ssl');
                Route::delete('/domains/{domain}', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'destroy'])->name('domains.destroy');

                Route::get('/payments', [\App\Http\Controllers\Admin\SaaSPaymentController::class, 'index'])->name('payments.index');
                Route::get('/invoices', [\App\Http\Controllers\Admin\SaaSPaymentController::class, 'invoices'])->name('invoices.index');
                Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Admin\SaaSPaymentController::class, 'downloadInvoice'])->name('invoices.download');

                Route::get('/analytics', [\App\Http\Controllers\Admin\SaaSAnalyticsController::class, 'index'])->name('analytics.index');
                Route::get('/usage', [\App\Http\Controllers\Admin\SaaSAnalyticsController::class, 'usage'])->name('usage.index');
            });
        });

        // Notifications
        Route::get('/admin/notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/admin/notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
        Route::post('/admin/notifications/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('notifications.markRead');
        Route::get('/admin/notifications/{id}/redirect', [\App\Http\Controllers\Admin\NotificationController::class, 'readAndRedirect'])->name('notifications.redirect');

        // RBAC Debug page
        Route::get('/rbac-debug', [\App\Http\Controllers\Admin\RbacDebugController::class, 'index'])->name('rbac.debug');

        // Super Admin Logout
        Route::post('/admin-logout', [AuthController::class, 'logout'])->name('logout');
    });

    /*
    |--------------------------------------------------------------------------
    | SUBSCRIBER-ONLY ROUTES (Protected by subscriber middleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['subscriber'])->name('subscriber.')->group(function () {
        // Brands, Categories, Subcategories for Subscriber Panel (mirroring admin functionality)
        Route::post('/subscriber-brands/quick-store', [\App\Http\Controllers\Subscriber\BrandController::class, 'quickStore'])->name('brands.quick-store');
        Route::post('/subscriber-categories/quick-store', [\App\Http\Controllers\Subscriber\CategoryController::class, 'quickStore'])->name('categories.quick-store');
        Route::post('/subscriber-subcategories/quick-store', [\App\Http\Controllers\Subscriber\SubcategoryController::class, 'quickStore'])->name('subcategories.quick-store');
        Route::resource('subscriber-brands', \App\Http\Controllers\Subscriber\BrandController::class)->names([
            'index' => 'brands.index',
            'create' => 'brands.create',
            'store' => 'brands.store',
            'show' => 'brands.show',
            'edit' => 'brands.edit',
            'update' => 'brands.update',
            'destroy' => 'brands.destroy',
        ]);
        
        Route::resource('subscriber-categories', \App\Http\Controllers\Subscriber\CategoryController::class)->names([
            'index' => 'categories.index',
            'create' => 'categories.create',
            'store' => 'categories.store',
            'show' => 'categories.show',
            'edit' => 'categories.edit',
            'update' => 'categories.update',
            'destroy' => 'categories.destroy',
        ]);

        Route::resource('subscriber-subcategories', \App\Http\Controllers\Subscriber\SubcategoryController::class)->names([
            'index' => 'subcategories.index',
            'create' => 'subcategories.create',
            'store' => 'subcategories.store',
            'show' => 'subcategories.show',
            'edit' => 'subcategories.edit',
            'update' => 'subcategories.update',
            'destroy' => 'subcategories.destroy',
        ]);

        // Extra Product Routes
        Route::delete('/product-images/{image}', [SubscriberProductController::class, 'deleteImage'])->name('product-images.destroy');
        Route::get('/get-subcategories', [SubscriberProductController::class, 'getSubcategories'])->name('get-subcategories');
        Route::get('/get-product-types', [SubscriberProductController::class, 'getProductTypes'])->name('get-product-types');
        Route::get('/api/category-attributes/{category}', [SubscriberProductController::class, 'getCategoryAttributes'])->name('api.category-attributes');
        Route::get('/api/subcategory-attributes/{subcategory?}', [SubscriberProductController::class, 'getSubcategoryAttributes'])->name('api.subcategory-attributes');
        Route::get('/products-ops/import/template', [SubscriberProductController::class, 'downloadTemplate'])->name('products.import.template');
        Route::get('/products-ops/import', [SubscriberProductController::class, 'importPage'])->name('products.import');
        Route::post('/products-ops/import', [SubscriberProductController::class, 'import'])->name('products.import.submit');
        Route::get('/products-ops/import/status/{id}', [SubscriberProductController::class, 'importStatus'])->name('products.import.status');
        Route::get('/products-ops/import-logs', [SubscriberProductController::class, 'importLogs'])->name('products.import-logs');
        Route::get('/products-ops/import-logs/{id}', [SubscriberProductController::class, 'importLogShow'])->name('products.import-logs.show');
        Route::get('/products-ops/import-logs/{id}/download-errors', [SubscriberProductController::class, 'downloadImportErrors'])->name('products.import-logs.download-errors');
        Route::get('/products-ops/export', [SubscriberProductController::class, 'export'])->name('products.export');

        // Variants
        Route::resource('variants', SubscriberVariantController::class);

        // Inventory Stock management
        Route::get('/inventory', [SubscriberInventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/update-stock', [SubscriberInventoryController::class, 'updateStock'])->name('inventory.update-stock');

        // Bulk Upload
        Route::get('/bulk', [SubscriberBulkUploadController::class, 'index'])->name('bulk.index');
        Route::get('/bulk/template', [SubscriberBulkUploadController::class, 'downloadTemplate'])->name('bulk.template');
        Route::post('/bulk/import', [SubscriberBulkUploadController::class, 'import'])->name('bulk.import');
        Route::get('/bulk/status/{id}', [SubscriberBulkUploadController::class, 'status'])->name('bulk.status');

        // Extra Attributes Routes
        Route::post('/attributes/reorder', [AttributeController::class, 'reorder'])->name('attributes.reorder');

        // Attribute Groups
        Route::get('/attribute-groups', [AttributeGroupController::class, 'index'])->name('attribute-groups.index');
        Route::post('/attribute-groups', [AttributeGroupController::class, 'store'])->name('attribute-groups.store');
        Route::put('/attribute-groups/{attributeGroup}', [AttributeGroupController::class, 'update'])->name('attribute-groups.update');
        Route::delete('/attribute-groups/{attributeGroup}', [AttributeGroupController::class, 'destroy'])->name('attribute-groups.destroy');
        Route::post('/attribute-groups/reorder', [AttributeGroupController::class, 'reorder'])->name('attribute-groups.reorder');

        // Share Links
        Route::resource('share', ShareController::class);
        Route::get('/share/{shareLink}/pdf', [ShareController::class, 'generatePdf'])->name('share.pdf');

        // Subscription & Billing
        Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
        Route::get('/subscription/plans', [SubscriptionController::class, 'plans'])->name('subscription.plans');
        Route::get('/subscription/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
        Route::post('/subscription/pay/razorpay-order/{plan}', [SubscriptionController::class, 'createRazorpayOrder'])->name('subscription.razorpay.order');
        Route::post('/subscription/pay/razorpay-verify/{plan}', [SubscriptionController::class, 'verifyRazorpayPayment'])->name('subscription.razorpay.verify');
        Route::post('/subscription/pay/{plan}', [SubscriptionController::class, 'processDummyPayment'])->name('subscription.pay');
        Route::get('/subscription/invoice/{invoice}', [SubscriptionController::class, 'invoiceDownload'])->name('subscription.invoice');

        Route::post('/profile/pdf-template', [SubscriberProfileController::class, 'updatePdfTemplate'])->name('profile.pdf-template');

        // Pending approval waiting screen
        Route::get('/pending-approval', function() {
            $user = auth()->user();
            $profile = $user ? $user->subscriberProfile : null;
            if ($profile && $profile->isApproved()) {
                if ($user->hasActiveSubscription()) {
                    return redirect()->route('dashboard');
                }
                return redirect()->route('subscriber.subscription.plans');
            }
            return view('subscriber-panel.auth.pending-approval');
        })->name('pending-approval');

        // Custom domain tab (Enterprise only)
        Route::get('/domain', [\App\Http\Controllers\Subscriber\DomainController::class, 'index'])->name('domain.index');
        Route::post('/domain', [\App\Http\Controllers\Subscriber\DomainController::class, 'store'])->name('domain.store');
        Route::post('/domain/verify', [\App\Http\Controllers\Subscriber\DomainController::class, 'verify'])->name('domain.verify');
        Route::delete('/domain/{domain}', [\App\Http\Controllers\Subscriber\DomainController::class, 'destroy'])->name('domain.destroy');

        // Analytics
        Route::get('/analytics', [AnalyticsController::class, 'subscriberAnalytics'])->name('analytics');
        Route::get('/analytics/timeline/{visitorUuid}', [AnalyticsController::class, 'subscriberTimeline'])->name('analytics.timeline');
        Route::get('/analytics/export', [AnalyticsController::class, 'subscriberExport'])->name('analytics.export');
        Route::get('/analytics/realtime', [AnalyticsController::class, 'realtimeData'])->name('analytics.realtime');

        // Frontend-Only Analytics Dashboard — REMOVED from subscriber panel (admin-only now)
        // Route::get('/frontend-analytics', [FrontendAnalyticsController::class, 'subscriberDashboard'])->name('frontend-analytics');
        // Route::get('/frontend-analytics/realtime', [FrontendAnalyticsController::class, 'subscriberRealtime'])->name('frontend-analytics.realtime');
        // Route::get('/frontend-analytics/export', [FrontendAnalyticsController::class, 'subscriberExport'])->name('frontend-analytics.export');

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Subscriber\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Subscriber\NotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Subscriber\NotificationController::class, 'markAsRead'])->name('notifications.markRead');
        Route::get('/notifications/{id}/redirect', [\App\Http\Controllers\Subscriber\NotificationController::class, 'readAndRedirect'])->name('notifications.redirect');

        // Subscriber Logout
        Route::post('/subscriber-logout', [SubscriberAuthController::class, 'logout'])->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Public Share Pages
|--------------------------------------------------------------------------
*/
Route::get('/s/{token}', [ShareController::class, 'publicView'])->name('subscriber.share.public');
Route::get('/s/{token}/pdf', [ShareController::class, 'generatePdf'])->name('subscriber.share.pdf');
Route::get('/s/{token}/gallery', [ShareController::class, 'imageGallery'])->name('subscriber.share.gallery');

/*
|--------------------------------------------------------------------------
| Tracking Redirect Routes (Public - no auth required)
|--------------------------------------------------------------------------
*/
Route::get('/track/pdf-click', [TrackingRedirectController::class, 'pdfProductClick'])->name('track.pdf.click');
Route::get('/track/catalogue-open', [TrackingRedirectController::class, 'catalogueOpen'])->name('track.catalogue.open');

/*
|--------------------------------------------------------------------------
| Analytics Tracking API (Public - no auth required)
|--------------------------------------------------------------------------
*/
Route::prefix('api/analytics')->group(function () {
    Route::post('/visit', [App\Http\Controllers\API\AnalyticsApiController::class, 'logVisit'])->name('analytics.api.visit');
    Route::post('/heartbeat', [App\Http\Controllers\API\AnalyticsApiController::class, 'heartbeat'])->name('analytics.api.heartbeat');
    Route::post('/product-view', [App\Http\Controllers\API\AnalyticsApiController::class, 'logProductView'])->name('analytics.api.product-view');
    Route::post('/download', [App\Http\Controllers\API\AnalyticsApiController::class, 'logDownload'])->name('analytics.api.download');
    Route::post('/order', [App\Http\Controllers\API\AnalyticsApiController::class, 'logOrder'])->name('analytics.api.order');
    Route::post('/engagement', [App\Http\Controllers\API\AnalyticsApiController::class, 'logEngagement'])->name('analytics.api.engagement');
});

/*
|--------------------------------------------------------------------------
| Frontend-Only Event Tracking API (Public - no auth required)
| Fires only from frontend JS via sendBeacon / fetch.
| Admin & subscriber panels never call this route.
|--------------------------------------------------------------------------
*/
Route::post('/api/track-event', [App\Http\Controllers\API\FrontendEventController::class, 'store'])
    ->name('frontend.track-event');

/*
|--------------------------------------------------------------------------
| API Notifications Routes (Linked to Enquiries)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('api/notifications')->group(function () {
    Route::get('/', function () {
        $enquiries = \App\Models\Enquiry::where('is_read', false)->latest()->get();
        $notifications = $enquiries->map(function ($enq) {
            return [
                'id' => $enq->id,
                'title' => 'New B2B Inquiry',
                'message' => "Inquiry from {$enq->name} regarding " . ($enq->product?->name ?? $enq->subject ?? 'Corporate Products'),
                'priority' => 'high',
                'is_read' => (bool)$enq->is_read,
                'read_at' => null,
                'created_at' => $enq->created_at->toISOString(),
            ];
        });
        return response()->json($notifications);
    });

    Route::post('/{id}/read', function ($id) {
        $enq = \App\Models\Enquiry::find($id);
        if ($enq) {
            $enq->update(['is_read' => true]);
        }
        return response()->json(['success' => true]);
    });

    Route::post('/mark-all-read', function () {
        \App\Models\Enquiry::where('is_read', false)->update(['is_read' => true]);
        return response()->json(['success' => true]);
    });

    Route::get('/unread-count', function () {
        $count = \App\Models\Enquiry::where('is_read', false)->count();
        return response()->json(['count' => $count]);
    });
});

/*
|--------------------------------------------------------------------------
| Legacy Redirects (Fallback for nested dashboard routes)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', function () { return redirect()->route('dashboard'); });
    Route::get('/subscriber/dashboard', function () { return redirect()->route('dashboard'); });
    
    // Redirect nested legacy panel routes
    Route::get('/admin/{any?}', function () { return redirect()->route('dashboard'); })->where('any', '.*');
    Route::get('/subscriber/{any?}', function () { return redirect()->route('dashboard'); })->where('any', '.*');
});

/*
|--------------------------------------------------------------------------
| Custom B2B & SEO Routes
|--------------------------------------------------------------------------
*/
Route::post('/product/{slug}/review', [FrontendController::class, 'submitReview'])->name('product.review.submit');
Route::get('/product/{slug}/pdf', [FrontendController::class, 'downloadProductPdf'])->name('product.pdf');
Route::get('/sitemap.xml', [FrontendController::class, 'sitemap'])->name('sitemap');
Route::get('/api/products-filter', [FrontendController::class, 'apiFilterProducts'])->name('api.products.filter');

Route::get('/route-clear', function() {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    return "Route cache and all other system caches cleared successfully!";
})->name('route-clear');

Route::get('/{company_slug}', [FrontendController::class, 'storeCatalog'])->name('store.public');

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    abort(404);
});

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\ProductController;
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



/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/catalogue', [FrontendController::class, 'catalogue'])->name('catalogue');
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
Route::get('/store/{company_slug}', [FrontendController::class, 'storeCatalog'])->name('store.catalog');

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
        Route::get('/forgot-password', [SubscriberAuthController::class, 'showForgotForm'])->name('forgot');
        Route::post('/forgot-password', [SubscriberAuthController::class, 'sendResetLink'])->name('forgot.submit');
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
    Route::middleware('superadmin')->name('admin.')->group(function () {
        Route::get('/tracking-analytics', [App\Http\Controllers\DoubleTickController::class, 'analyticsDashboard'])->name('tracking.analytics');

        Route::resource('brands', BrandController::class)->middleware('permission:view-brands');
        Route::resource('categories', CategoryController::class)->middleware('permission:view-categories');
        Route::resource('subcategories', SubcategoryController::class)->middleware('permission:view-subcategories');

        // AJAX Routes for Dependent Dropdowns
        Route::get('/get-subcategories/{category_id}', [CategoryController::class, 'getSubcategories']);

        // Products Operations
        Route::get('/products-ops/import/template', [ProductController::class, 'downloadTemplate'])->name('products.import.template')->middleware('permission:import-products');
        Route::get('/products-ops/import', [ProductController::class, 'importPage'])->name('products.import')->middleware('permission:import-products');
        Route::post('/products-ops/import', [ProductController::class, 'import'])->name('products.import.submit')->middleware('permission:import-products');
        Route::get('/products-ops/import/status/{id}', [ProductController::class, 'importStatus'])->name('products.import.status')->middleware('permission:import-products');
        Route::get('/products-ops/import-logs', [ProductController::class, 'importLogs'])->name('products.import-logs')->middleware('permission:import-products');
        Route::get('/products-ops/import-logs/{id}', [ProductController::class, 'importLogShow'])->name('products.import-logs.show')->middleware('permission:import-products');
        Route::get('/products-ops/export', [ProductController::class, 'export'])->name('products.export')->middleware('permission:export-products');
        Route::delete('/product-images/{id}', [ProductController::class, 'deleteImage'])->name('product-images.destroy')->middleware('permission:edit-products');

        Route::resource('users', UserController::class)->middleware('permission:view-users');
        Route::resource('roles', RoleController::class)->middleware('permission:roles.manage');
        Route::resource('permissions', PermissionController::class)->middleware('permission:permissions.manage');
        Route::resource('newsletters', NewsletterController::class)->middleware('permission:view-newsletters');

        // Attributes Management Extras
        Route::post('/attributes/group', [AdminAttributeController::class, 'storeGroup'])->name('attributes.storeGroup');
        Route::get('/saas/approvals/custom-fields', [AdminAttributeController::class, 'approvals'])->name('saas.approvals.custom-fields');
        Route::post('/attributes/{attribute}/approve', [AdminAttributeController::class, 'approve'])->name('attributes.approve');
        Route::post('/attributes/{attribute}/reject', [AdminAttributeController::class, 'reject'])->name('attributes.reject');
        Route::get('/attributes/subcategory/{subcategory}', [AdminAttributeController::class, 'forSubcategory'])->name('attributes.forSubcategory');

        Route::resource('templates', AdminTemplateController::class);

        // Enquiries Management
        Route::get('/enquiries', [\App\Http\Controllers\Admin\EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('/enquiries/{id}', [\App\Http\Controllers\Admin\EnquiryController::class, 'show'])->name('enquiries.show');
        Route::delete('/enquiries/{id}', [\App\Http\Controllers\Admin\EnquiryController::class, 'destroy'])->name('enquiries.destroy');
        Route::post('/enquiries/{id}/mark-as-read', [\App\Http\Controllers\Admin\EnquiryController::class, 'markAsRead'])->name('enquiries.mark-as-read');

        // General settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index')->middleware('permission:settings.manage');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update')->middleware('permission:settings.manage');

        // System
        Route::get('/system', [SystemController::class, 'index'])->name('system.index')->middleware('permission:manage-system');
        Route::post('/system/command', [SystemController::class, 'runCommand'])->name('system.command')->middleware('permission:manage-system');
        Route::post('/system/storage-link', [SystemController::class, 'storageLink'])->name('system.storage-link')->middleware('permission:manage-system');
        Route::post('/system/clear-logs', [SystemController::class, 'clearLogs'])->name('system.clear-logs')->middleware('permission:manage-system');

        // Subscriber & SaaS Platform Management
        Route::get('/subscribers', [AdminSubscriberController::class, 'index'])->name('subscribers.index')->middleware('permission:subscribers.manage');
        Route::get('/subscribers/{user}', [AdminSubscriberController::class, 'show'])->name('subscribers.show')->middleware('permission:subscribers.manage');
        Route::delete('/subscribers/{user}', [AdminSubscriberController::class, 'destroy'])->name('subscribers.destroy')->middleware('permission:subscribers.manage');
        Route::post('/subscribers/{user}/suspend', [AdminSubscriberController::class, 'suspend'])->name('subscribers.suspend')->middleware('permission:subscribers.manage');
        Route::post('/subscribers/{user}/unsuspend', [AdminSubscriberController::class, 'unsuspend'])->name('subscribers.unsuspend')->middleware('permission:subscribers.manage');
        Route::post('/subscribers/{user}/assign-plan', [AdminSubscriberController::class, 'assignPlan'])->name('subscribers.assign-plan')->middleware('permission:subscribers.manage');

        Route::get('/subscription-plans', [AdminSubscriberController::class, 'plans'])->name('subscription-plans.index')->middleware('permission:subscribers.manage');
        Route::post('/subscription-plans', [AdminSubscriberController::class, 'storePlan'])->name('subscription-plans.store')->middleware('permission:subscribers.manage');
        Route::put('/subscription-plans/{subscriptionPlan}', [AdminSubscriberController::class, 'updatePlan'])->name('subscription-plans.update')->middleware('permission:subscribers.manage');

        Route::prefix('saas')->name('saas.')->middleware('permission:subscribers.manage')->group(function () {
            Route::get('/subscribers', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'subscribers'])->name('subscribers.index');
            Route::post('/subscribers/{user}/suspend', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'suspendSubscriber'])->name('subscribers.suspend');
            Route::post('/subscribers/{user}/unsuspend', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'unsuspendSubscriber'])->name('subscribers.unsuspend');
            
            Route::get('/approvals', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'approvals'])->name('approvals.index');
            Route::post('/approvals/store/{profile}/approve', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'approveStore'])->name('approvals.store.approve');
            Route::post('/approvals/store/{profile}/reject', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'rejectStore'])->name('approvals.store.reject');
            Route::post('/approvals/product/{product}/approve', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'approveProduct'])->name('approvals.product.approve');
            Route::post('/approvals/product/{product}/reject', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'rejectProduct'])->name('approvals.product.reject');
            Route::post('/approvals/share/{shareLink}/approve', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'approveShare'])->name('approvals.share.approve');
            Route::post('/approvals/share/{shareLink}/reject', [\App\Http\Controllers\Admin\SaaSApprovalController::class, 'rejectShare'])->name('approvals.share.reject');

            Route::get('/domains', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'index'])->name('domains.index');
            Route::post('/domains/{domain}/verify', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'verify'])->name('domains.verify');
            Route::post('/domains/{domain}/approve', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'approve'])->name('domains.approve');
            Route::post('/domains/{domain}/reject', [\App\Http\Controllers\Admin\SaaSDomainController::class, 'reject'])->name('domains.reject');

            Route::get('/payments', [\App\Http\Controllers\Admin\SaaSPaymentController::class, 'index'])->name('payments.index');
            Route::get('/invoices', [\App\Http\Controllers\Admin\SaaSPaymentController::class, 'invoices'])->name('invoices.index');
            Route::get('/invoices/{invoice}/download', [\App\Http\Controllers\Admin\SaaSPaymentController::class, 'downloadInvoice'])->name('invoices.download');

            Route::get('/analytics', [\App\Http\Controllers\Admin\SaaSAnalyticsController::class, 'index'])->name('analytics.index');
            Route::get('/usage', [\App\Http\Controllers\Admin\SaaSAnalyticsController::class, 'usage'])->name('usage.index');
        });

        // Super Admin Logout
        Route::post('/admin-logout', [AuthController::class, 'logout'])->name('logout');
    });

    /*
    |--------------------------------------------------------------------------
    | SUBSCRIBER-ONLY ROUTES (Protected by subscriber middleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['subscriber'])->name('subscriber.')->group(function () {
        // Extra Product Routes
        Route::delete('/product-images/{image}', [SubscriberProductController::class, 'deleteImage'])->name('product-images.destroy');
        Route::get('/get-subcategories', [SubscriberProductController::class, 'getSubcategories'])->name('get-subcategories');
        Route::get('/api/category-attributes/{category}', [SubscriberProductController::class, 'getCategoryAttributes'])->name('api.category-attributes');
        Route::get('/api/subcategory-attributes/{subcategory}', [SubscriberProductController::class, 'getSubcategoryAttributes'])->name('api.subcategory-attributes');

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
        Route::post('/subscription/pay/{plan}', [SubscriptionController::class, 'processDummyPayment'])->name('subscription.pay');
        Route::get('/subscription/invoice/{invoice}', [SubscriptionController::class, 'invoiceDownload'])->name('subscription.invoice');

        Route::post('/profile/pdf-template', [SubscriberProfileController::class, 'updatePdfTemplate'])->name('profile.pdf-template');

        // Pending approval waiting screen
        Route::get('/pending-approval', function() {
            return view('subscriber-panel.auth.pending-approval');
        })->name('pending-approval');

        // Custom domain tab (Enterprise only)
        Route::get('/domain', [\App\Http\Controllers\Subscriber\DomainController::class, 'index'])->name('domain.index');
        Route::post('/domain', [\App\Http\Controllers\Subscriber\DomainController::class, 'store'])->name('domain.store');
        Route::post('/domain/verify', [\App\Http\Controllers\Subscriber\DomainController::class, 'verify'])->name('domain.verify');

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

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    abort(404);
});

<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\ChildCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\SolutionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\SystemController;

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
*/
Route::get('/migrate', function () {

    Artisan::call('migrate');

    return "Migration Run Successfully";
});
Route::get('/', [FrontendController::class, 'index'])->name('home');
Route::get('/catalogue', [FrontendController::class, 'catalogue'])->name('catalogue');
Route::get('/brands', [FrontendController::class, 'brands'])->name('brands');
Route::get('/categories', [FrontendController::class, 'categories'])->name('categories');
Route::get('/sub-categories', [FrontendController::class, 'subcategories'])->name('subcategories');
Route::get('/child-categories', [FrontendController::class, 'childCategories'])->name('childcategories');
Route::get('/category/{slug}', [FrontendController::class, 'categorySubcategories'])->name('category.subcategories');
Route::get('/category-products/{slug}', [FrontendController::class, 'categoryProducts'])->name('category.products');
Route::get('/subcategory/{slug}', [FrontendController::class, 'subcategoryProducts'])->name('subcategory.products');
Route::get('/child-category/{slug}', [FrontendController::class, 'childCategoryProducts'])->name('childcategory.products');
Route::get('/product/{slug}', [FrontendController::class, 'productDetails'])->name('product.details');
Route::get('/brand/{slug}', [FrontendController::class, 'brandDetails'])->name('brand.details');
Route::get('/brand/{slug}/products', [FrontendController::class, 'brandProducts'])->name('brand.products');
Route::get('/search', [FrontendController::class, 'search'])->name('search');
Route::get('/part-list', [FrontendController::class, 'partList'])->name('part.list');
Route::get('/contact-us', [FrontendController::class, 'contact'])->name('contact');
Route::post('/enquiry/submit', [FrontendController::class, 'enquirySubmit'])->name('enquiry.submit');
Route::post('/newsletter/submit', [FrontendController::class, 'newsletterSubmit'])->name('newsletter.submit');
Route::get('/thank-you', [FrontendController::class, 'thankYou'])->name('thank.you');
Route::get('/future-products', [FrontendController::class, 'futureProducts'])->name('future.products');
Route::get('/solutions', [FrontendController::class, 'solutions'])->name('solutions.index');
Route::get('/solutions/{slug}', [FrontendController::class, 'solutionDetails'])->name('solutions.show');

// DoubleTick.io Outbound WhatsApp Catalogue Sharing & Analytics Tracking
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
Route::get('/apichildcategories/{subcategory_id}', [FrontendController::class, 'getApiChildcategories']);
Route::get('/apifeatured-products', [FrontendController::class, 'getApiFeaturedProducts']);
Route::get('/api/products-details', [FrontendController::class, 'apiProductsDetails']);
Route::get('/api/product-details/{id}', [FrontendController::class, 'apiProductDetails']);
Route::get('/product/{id}/details', [FrontendController::class, 'productQuickView'])->name('product.quickview');


/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Login Page
    Route::get('/admin/login', [AuthController::class, 'login'])
        ->name('login');

    // Login Submit
    Route::post('/admin/login', [AuthController::class, 'loginSubmit'])
        ->name('login.submit');

    // Register Page
    Route::get('/admin/register', [AuthController::class, 'register'])
        ->name('register');

    // Register Submit
    Route::post('/admin/register', [AuthController::class, 'registerSubmit'])
        ->name('register.submit');

});


/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        |*/
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // DoubleTick WhatsApp Analytics
        Route::get('/tracking-analytics', [App\Http\Controllers\DoubleTickController::class, 'analyticsDashboard'])
            ->name('tracking.analytics');


        /*
        |--------------------------------------------------------------------------
        | Brands
        |--------------------------------------------------------------------------
        |*/
         Route::resource('brands', BrandController::class)->middleware('permission:view-brands');


        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        |*/
        Route::resource('categories', CategoryController::class)->middleware('permission:view-categories');


        /*
        |--------------------------------------------------------------------------
        | Subcategories & Child Categories
        |--------------------------------------------------------------------------
        |*/
        Route::resource('subcategories', SubcategoryController::class)->middleware('permission:view-subcategories');
        Route::resource('childcategories', ChildCategoryController::class)->middleware('permission:view-childcategories');

        // AJAX Routes for Dependent Dropdowns
        Route::get('/get-subcategories/{category_id}', [CategoryController::class, 'getSubcategories']);
        Route::get('/get-childcategories/{subcategory_id}', [SubcategoryController::class, 'getChildcategories']);


        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        |*/
        Route::get('/products/import/template', [ProductController::class, 'downloadTemplate'])->name('products.import.template')->middleware('permission:import-products');
        Route::get('/products/import', [ProductController::class, 'importPage'])->name('products.import')->middleware('permission:import-products');
        Route::post('/products/import', [ProductController::class, 'import'])->name('products.import.submit')->middleware('permission:import-products');
        Route::get('/products/import/status/{id}', [ProductController::class, 'importStatus'])->name('products.import.status')->middleware('permission:import-products');
        Route::get('/products/import-logs', [ProductController::class, 'importLogs'])->name('products.import-logs')->middleware('permission:import-products');
        Route::get('/products/import-logs/{id}', [ProductController::class, 'importLogShow'])->name('products.import-logs.show')->middleware('permission:import-products');
        Route::get('/products/export', [ProductController::class, 'export'])->name('products.export')->middleware('permission:export-products');
        

        Route::resource('products', ProductController::class)->middleware('permission:view-products');
        Route::resource('solutions', SolutionController::class)->middleware('permission:view-solutions');

        Route::delete('/product-images/{id}', [ProductController::class, 'deleteImage'])->name('product-images.destroy')->middleware('permission:edit-products');

        /*
        |--------------------------------------------------------------------------
        | Access Control
        |--------------------------------------------------------------------------
        |*/
        Route::resource('roles', RoleController::class)->middleware('permission:view-roles');
        Route::resource('permissions', PermissionController::class)->middleware('permission:view-permissions');
        Route::resource('users', UserController::class)->middleware('permission:view-users');
        Route::resource('newsletters', NewsletterController::class)->middleware('permission:view-newsletters');

        /*
        |--------------------------------------------------------------------------
        | Enquiries
        |--------------------------------------------------------------------------
        |*/
        Route::get('/enquiries', [\App\Http\Controllers\Admin\EnquiryController::class, 'index'])->name('enquiries.index');
        Route::get('/enquiries/{id}', [\App\Http\Controllers\Admin\EnquiryController::class, 'show'])->name('enquiries.show');
        Route::delete('/enquiries/{id}', [\App\Http\Controllers\Admin\EnquiryController::class, 'destroy'])->name('enquiries.destroy');
        Route::post('/enquiries/{id}/mark-as-read', [\App\Http\Controllers\Admin\EnquiryController::class, 'markAsRead'])->name('enquiries.mark-as-read');


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        /*
        |--------------------------------------------------------------------------
        | General Settings
        |--------------------------------------------------------------------------
        */

        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');

        /*
        |--------------------------------------------------------------------------
        | System Management
        |--------------------------------------------------------------------------
        */
        Route::get('/system', [SystemController::class, 'index'])->name('system.index')->middleware('permission:manage-system');
        Route::post('/system/command', [SystemController::class, 'runCommand'])->name('system.command')->middleware('permission:manage-system');
        Route::post('/system/storage-link', [SystemController::class, 'storageLink'])->name('system.storage-link')->middleware('permission:manage-system');
        Route::post('/system/clear-logs', [SystemController::class, 'clearLogs'])->name('system.clear-logs')->middleware('permission:manage-system');


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');

});


/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    abort(404);
});
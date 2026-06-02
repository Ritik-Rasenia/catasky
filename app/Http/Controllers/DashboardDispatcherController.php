<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Subscriber\ProductController as SubscriberProductController;
use App\Http\Controllers\Admin\AttributeController as AdminAttributeController;
use App\Http\Controllers\Subscriber\AttributeController as SubscriberAttributeController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Subscriber\ProfileController as SubscriberProfileController;

class DashboardDispatcherController extends Controller
{
    // --- Products ---
    public function productsIndex(Request $request) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProductController::class)->index($request);
        }
        return app(SubscriberProductController::class)->index($request);
    }
    
    public function productsCreate() {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProductController::class)->create();
        }
        return app(SubscriberProductController::class)->create();
    }
    
    public function productsStore(Request $request) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProductController::class)->store($request);
        }
        return app(SubscriberProductController::class)->store($request);
    }
    
    public function productsShow($id) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProductController::class)->show($id);
        }
        $product = \App\Models\SubscriberProduct::where('user_id', auth()->id())->findOrFail($id);
        return app(SubscriberProductController::class)->show($product);
    }
    
    public function productsEdit($id) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProductController::class)->edit($id);
        }
        $product = \App\Models\SubscriberProduct::where('user_id', auth()->id())->findOrFail($id);
        return app(SubscriberProductController::class)->edit($product);
    }
    
    public function productsUpdate(Request $request, $id) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProductController::class)->update($request, $id);
        }
        $product = \App\Models\SubscriberProduct::where('user_id', auth()->id())->findOrFail($id);
        return app(SubscriberProductController::class)->update($request, $product);
    }
    
    public function productsDestroy($id) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProductController::class)->destroy($id);
        }
        $product = \App\Models\SubscriberProduct::where('user_id', auth()->id())->findOrFail($id);
        return app(SubscriberProductController::class)->destroy($product);
    }

    // --- Attributes ---
    public function attributesIndex(Request $request) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminAttributeController::class)->index($request);
        }
        return app(SubscriberAttributeController::class)->index($request);
    }
    
    public function attributesCreate() {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminAttributeController::class)->create();
        }
        return app(SubscriberAttributeController::class)->create();
    }
    
    public function attributesStore(Request $request) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminAttributeController::class)->store($request);
        }
        return app(SubscriberAttributeController::class)->store($request);
    }
    
    public function attributesShow($id) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminAttributeController::class)->show($id);
        }
        $attribute = \App\Models\Attribute::where('user_id', auth()->id())->findOrFail($id);
        return app(SubscriberAttributeController::class)->show($attribute);
    }
    
    public function attributesEdit($id) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminAttributeController::class)->edit($id);
        }
        $attribute = \App\Models\Attribute::where('user_id', auth()->id())->findOrFail($id);
        return app(SubscriberAttributeController::class)->edit($attribute);
    }
    
    public function attributesUpdate(Request $request, $id) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminAttributeController::class)->update($request, $id);
        }
        $attribute = \App\Models\Attribute::where('user_id', auth()->id())->findOrFail($id);
        return app(SubscriberAttributeController::class)->update($request, $attribute);
    }
    
    public function attributesDestroy($id) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminAttributeController::class)->destroy($id);
        }
        $attribute = \App\Models\Attribute::where('user_id', auth()->id())->findOrFail($id);
        return app(SubscriberAttributeController::class)->destroy($attribute);
    }

    // --- Profile ---
    public function profileEdit() {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProfileController::class)->edit();
        }
        return app(SubscriberProfileController::class)->edit();
    }
    
    public function profileUpdate(Request $request) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProfileController::class)->updateProfile($request);
        }
        return app(SubscriberProfileController::class)->update($request);
    }
    
    public function profilePassword(Request $request) {
        if (!auth()->user()->isSubscriber()) {
            return app(AdminProfileController::class)->updatePassword($request);
        }
        return app(SubscriberProfileController::class)->updatePassword($request);
    }
}

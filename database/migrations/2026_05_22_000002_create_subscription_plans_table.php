<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency')->default('INR');
            $table->integer('duration_days')->default(30);
            $table->integer('product_limit')->default(50);
            $table->integer('attribute_limit')->default(20);
            $table->integer('share_link_limit')->default(100);
            $table->boolean('pdf_sharing')->default(true);
            $table->boolean('image_sharing')->default(true);
            $table->boolean('watermark_removal')->default(false);
            $table->boolean('custom_branding')->default(false);
            $table->boolean('analytics')->default(false);
            $table->json('features')->nullable();
            $table->boolean('is_trial')->default(false);
            $table->integer('trial_days')->default(14);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};

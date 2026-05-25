<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriber_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // subscriber user
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subcategory_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable();
            $table->decimal('mrp', 12, 2)->nullable();
            $table->decimal('offer_price', 12, 2)->nullable();
            $table->string('currency')->default('INR');
            $table->string('thumbnail')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('full_description')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('featured')->default(false);
            $table->enum('status', ['active', 'inactive', 'draft'])->default('draft');

            // PDF visibility controls
            $table->boolean('pdf_show_mrp')->default(true);
            $table->boolean('pdf_show_offer_price')->default(true);
            $table->boolean('pdf_show_description')->default(true);
            $table->boolean('pdf_show_attributes')->default(true);
            $table->boolean('pdf_show_images')->default(true);
            $table->boolean('pdf_show_short_desc')->default(true);

            // Share page visibility controls
            $table->boolean('share_show_mrp')->default(true);
            $table->boolean('share_show_offer_price')->default(true);
            $table->boolean('share_show_description')->default(true);
            $table->boolean('share_show_attributes')->default(true);

            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_products');
    }
};

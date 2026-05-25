<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add approval and global scopes to attributes table
        if (Schema::hasTable('attributes')) {
            Schema::table('attributes', function (Blueprint $table) {
                if (!Schema::hasColumn('attributes', 'is_global')) {
                    $table->boolean('is_global')->default(true)->after('user_id');
                }
                if (!Schema::hasColumn('attributes', 'approval_status')) {
                    $table->string('approval_status')->default('approved')->after('is_active'); // 'pending', 'approved', 'rejected'
                }
            });
        }

        // 2. Map attributes and attribute groups to categories (Templates)
        Schema::create('category_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->foreignId('attribute_group_id')->nullable()->constrained('attribute_groups')->onDelete('set null');
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            // Prevent duplicate attributes per category
            $table->unique(['category_id', 'attribute_id'], 'cat_attr_unique');
        });

        // 3. Product Variant Module: Stock & Price variations
        Schema::create('subscriber_product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_product_id');
            // Explicit constraint name to avoid key limit truncation in MySQL
            $table->foreign('subscriber_product_id', 'sp_variants_product_fk')
                  ->references('id')
                  ->on('subscriber_products')
                  ->onDelete('cascade');
            $table->string('variant_sku')->unique();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // 4. Mapping which variant holds which attribute values (e.g. Red, XL)
        Schema::create('subscriber_product_variant_attributes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('variant_id');
            $table->foreign('variant_id', 'sp_var_attr_variant_fk')
                  ->references('id')
                  ->on('subscriber_product_variants')
                  ->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained('attributes')->onDelete('cascade');
            $table->string('attribute_value'); // e.g. "XL", "Red"
            $table->timestamps();

            // Prevent duplicate attribute specs per variant
            $table->unique(['variant_id', 'attribute_id'], 'var_attr_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriber_product_variant_attributes');
        Schema::dropIfExists('subscriber_product_variants');
        Schema::dropIfExists('category_attributes');

        if (Schema::hasTable('attributes')) {
            Schema::table('attributes', function (Blueprint $table) {
                if (Schema::hasColumn('attributes', 'is_global')) {
                    $table->dropColumn('is_global');
                }
                if (Schema::hasColumn('attributes', 'approval_status')) {
                    $table->dropColumn('approval_status');
                }
            });
        }
    }
};

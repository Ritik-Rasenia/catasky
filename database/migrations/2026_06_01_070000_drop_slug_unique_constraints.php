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
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique('brands_slug_unique');
            $table->index('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_slug_unique');
            $table->index('slug');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropUnique('subcategories_slug_unique');
            $table->index('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_slug_unique');
            $table->index('slug');
        });

        if (Schema::hasTable('subscriber_products')) {
            Schema::table('subscriber_products', function (Blueprint $table) {
                $table->dropUnique('subscriber_products_slug_unique');
                $table->index('slug');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->unique('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->unique('slug');
        });

        Schema::table('subcategories', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->unique('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['slug']);
            $table->unique('slug');
        });

        if (Schema::hasTable('subscriber_products')) {
            Schema::table('subscriber_products', function (Blueprint $table) {
                $table->dropIndex(['slug']);
                $table->unique('slug');
            });
        }
    }
};

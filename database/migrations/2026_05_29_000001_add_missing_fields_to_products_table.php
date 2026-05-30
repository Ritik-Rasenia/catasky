<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add missing fields to the products table.
     * Fixes: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'tags' in 'field list'
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'tags')) {
                $table->string('tags')->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'part_number')) {
                $table->string('part_number')->nullable()->unique()->after('part_code');
            }
            if (!Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 12, 2)->nullable()->after('short_description');
            }
            if (!Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('status');
            }
            if (!Schema::hasColumn('products', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('products', 'meta_keywords')) {
                $table->string('meta_keywords')->nullable()->after('meta_description');
            }
            if (!Schema::hasColumn('products', 'child_category_id')) {
                $table->unsignedBigInteger('child_category_id')->nullable()->after('subcategory_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = ['tags', 'part_number', 'meta_title', 'meta_description', 'meta_keywords', 'child_category_id'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

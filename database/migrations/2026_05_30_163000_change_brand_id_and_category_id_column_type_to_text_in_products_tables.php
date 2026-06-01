<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
 
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop foreign keys safely
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
        });
 
        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['category_id']);
        });
 
        // 2. Change column type to text to support JSON serialized arrays of IDs
        Schema::table('products', function (Blueprint $table) {
            $table->text('brand_id')->nullable()->change();
            $table->text('category_id')->nullable()->change();
        });
 
        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->text('brand_id')->nullable()->change();
            $table->text('category_id')->nullable()->change();
        });
 
        // 3. Convert existing single integer data to JSON arrays (e.g. 2 -> [2])
        DB::table('products')->whereNotNull('brand_id')->get()->each(function ($product) {
            $val = trim($product->brand_id);
            if (!empty($val) && !str_starts_with($val, '[')) {
                DB::table('products')->where('id', $product->id)->update([
                    'brand_id' => json_encode([(int)$val])
                ]);
            }
        });
        
        DB::table('products')->whereNotNull('category_id')->get()->each(function ($product) {
            $val = trim($product->category_id);
            if (!empty($val) && !str_starts_with($val, '[')) {
                DB::table('products')->where('id', $product->id)->update([
                    'category_id' => json_encode([(int)$val])
                ]);
            }
        });
 
        DB::table('subscriber_products')->whereNotNull('brand_id')->get()->each(function ($product) {
            $val = trim($product->brand_id);
            if (!empty($val) && !str_starts_with($val, '[')) {
                DB::table('subscriber_products')->where('id', $product->id)->update([
                    'brand_id' => json_encode([(int)$val])
                ]);
            }
        });

        DB::table('subscriber_products')->whereNotNull('category_id')->get()->each(function ($product) {
            $val = trim($product->category_id);
            if (!empty($val) && !str_starts_with($val, '[')) {
                DB::table('subscriber_products')->where('id', $product->id)->update([
                    'category_id' => json_encode([(int)$val])
                ]);
            }
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable()->change();
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
 
        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable()->change();
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
            
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }
};

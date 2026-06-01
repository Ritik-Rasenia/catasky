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
        // 1. Drop foreign keys
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
        });
 
        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
        });
 
        // 2. Change column type to text to support JSON serialized arrays of IDs
        Schema::table('products', function (Blueprint $table) {
            $table->text('subcategory_id')->nullable()->change();
        });
 
        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->text('subcategory_id')->nullable()->change();
        });
 
        // 3. Convert existing single integer data to JSON arrays (e.g. 2 -> [2])
        DB::table('products')->whereNotNull('subcategory_id')->get()->each(function ($product) {
            $val = trim($product->subcategory_id);
            if (!empty($val) && !str_starts_with($val, '[')) {
                DB::table('products')->where('id', $product->id)->update([
                    'subcategory_id' => json_encode([(int)$val])
                ]);
            }
        });
 
        DB::table('subscriber_products')->whereNotNull('subcategory_id')->get()->each(function ($product) {
            $val = trim($product->subcategory_id);
            if (!empty($val) && !str_starts_with($val, '[')) {
                DB::table('subscriber_products')->where('id', $product->id)->update([
                    'subcategory_id' => json_encode([(int)$val])
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
            $table->unsignedBigInteger('subcategory_id')->nullable()->change();
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('set null');
        });
 
        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->unsignedBigInteger('subcategory_id')->nullable()->change();
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('set null');
        });
    }
};

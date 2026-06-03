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
        Schema::table('subscriber_product_variants', function (Blueprint $table) {
            // Drop global unique constraint on variant_sku
            $table->dropUnique('subscriber_product_variants_variant_sku_unique');
            // Add composite unique constraint on subscriber_product_id and variant_sku
            $table->unique(['subscriber_product_id', 'variant_sku'], 'sub_prod_var_sku_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriber_product_variants', function (Blueprint $table) {
            $table->dropUnique('sub_prod_var_sku_unique');
            $table->unique('variant_sku', 'subscriber_product_variants_variant_sku_unique');
        });
    }
};

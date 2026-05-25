<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriber_product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_product_id');
            $table->foreign('subscriber_product_id', 'sp_attr_val_sub_prod_fk')->references('id')->on('subscriber_products')->onDelete('cascade');
            $table->foreignId('attribute_id')->constrained()->onDelete('cascade');
            $table->longText('value')->nullable(); // JSON for multi-values, plain for others
            $table->timestamps();

            $table->unique(['subscriber_product_id', 'attribute_id'], 'vp_attr_values_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_product_attribute_values');
    }
};

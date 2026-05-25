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
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('slug');
            $table->decimal('sale_price', 12, 2)->nullable()->after('price');
            $table->decimal('tax', 5, 2)->nullable()->after('sale_price');
            $table->integer('stock')->default(0)->after('tax');
            $table->text('description')->nullable()->after('stock');
            $table->string('featured_image')->nullable()->after('thumbnail');

            // Add indexes for optimization
            $table->index('sku');
            $table->index('price');
            $table->index('sale_price');
            $table->index('status');
            $table->index('featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['sku']);
            $table->dropIndex(['price']);
            $table->dropIndex(['sale_price']);
            $table->dropIndex(['status']);
            $table->dropIndex(['featured']);

            $table->dropColumn([
                'sku',
                'sale_price',
                'tax',
                'stock',
                'description',
                'featured_image'
            ]);
        });
    }
};

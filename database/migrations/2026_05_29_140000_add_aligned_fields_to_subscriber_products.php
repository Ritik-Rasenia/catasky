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
        Schema::table('subscriber_products', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriber_products', 'brand_id')) {
                $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null')->after('subcategory_id');
            }
            if (!Schema::hasColumn('subscriber_products', 'price')) {
                $table->decimal('price', 12, 2)->nullable()->after('offer_price');
            }
            if (!Schema::hasColumn('subscriber_products', 'stock')) {
                $table->integer('stock')->default(0)->after('price');
            }
            if (!Schema::hasColumn('subscriber_products', 'stock_status')) {
                $table->string('stock_status')->default('in_stock')->after('stock');
            }
            if (!Schema::hasColumn('subscriber_products', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('status');
            }
            if (!Schema::hasColumn('subscriber_products', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn([
                'brand_id',
                'price',
                'stock',
                'stock_status',
                'meta_title',
                'meta_description'
            ]);
        });
    }
};

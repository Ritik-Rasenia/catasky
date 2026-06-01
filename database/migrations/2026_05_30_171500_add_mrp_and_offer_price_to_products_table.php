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
            if (!Schema::hasColumn('products', 'mrp')) {
                $table->decimal('mrp', 12, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'offer_price')) {
                $table->decimal('offer_price', 12, 2)->nullable()->after('mrp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'mrp')) {
                $table->dropColumn('mrp');
            }
            if (Schema::hasColumn('products', 'offer_price')) {
                $table->dropColumn('offer_price');
            }
        });
    }
};

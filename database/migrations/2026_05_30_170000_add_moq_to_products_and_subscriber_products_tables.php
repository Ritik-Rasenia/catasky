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
            $table->integer('moq')->default(1)->nullable()->after('stock');
        });

        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->integer('moq')->default(1)->nullable()->after('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('moq');
        });

        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->dropColumn('moq');
        });
    }
};

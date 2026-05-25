<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'variant')) {
                $table->text('variant')->nullable()->after('short_description');
            }
            if (!Schema::hasColumn('products', 'price')) {
                $table->integer('price')->nullable()->after('variant');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};

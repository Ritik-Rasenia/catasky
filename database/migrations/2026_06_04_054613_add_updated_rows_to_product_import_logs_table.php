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
        Schema::table('product_import_logs', function (Blueprint $table) {
            $table->unsignedInteger('updated_rows')->default(0)->after('imported_rows');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_import_logs', function (Blueprint $table) {
            $table->dropColumn('updated_rows');
        });
    }
};

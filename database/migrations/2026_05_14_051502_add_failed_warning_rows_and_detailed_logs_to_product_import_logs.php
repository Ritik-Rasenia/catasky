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
            $table->unsignedInteger('failed_rows')->default(0)->after('skipped_rows');
            $table->unsignedInteger('warning_rows')->default(0)->after('failed_rows');
            $table->json('detailed_logs')->nullable()->after('errors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_import_logs', function (Blueprint $table) {
            $table->dropColumn(['failed_rows', 'warning_rows', 'detailed_logs']);
        });
    }
};

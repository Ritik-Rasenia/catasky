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
        if (Schema::hasTable('attributes')) {
            Schema::table('attributes', function (Blueprint $table) {
                if (!Schema::hasColumn('attributes', 'is_filterable')) {
                    $table->boolean('is_filterable')->default(false)->after('is_searchable');
                }
                if (!Schema::hasColumn('attributes', 'is_comparable')) {
                    $table->boolean('is_comparable')->default(false)->after('is_filterable');
                }
                if (!Schema::hasColumn('attributes', 'is_variant_enabled')) {
                    $table->boolean('is_variant_enabled')->default(false)->after('is_comparable');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('attributes')) {
            Schema::table('attributes', function (Blueprint $table) {
                if (Schema::hasColumn('attributes', 'is_filterable')) {
                    $table->dropColumn('is_filterable');
                }
                if (Schema::hasColumn('attributes', 'is_comparable')) {
                    $table->dropColumn('is_comparable');
                }
                if (Schema::hasColumn('attributes', 'is_variant_enabled')) {
                    $table->dropColumn('is_variant_enabled');
                }
            });
        }
    }
};

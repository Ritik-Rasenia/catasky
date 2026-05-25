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
        $tables = [
            'brands',
            'categories',
            'subcategories',
            'products',
            'attributes',
            'catalogue_shares'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'subscriber_id')) {
                    $table->foreignId('subscriber_id')->nullable()->constrained('users')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'brands',
            'categories',
            'subcategories',
            'products',
            'attributes',
            'catalogue_shares'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'subscriber_id')) {
                    $table->dropForeign([ 'subscriber_id']);
                    $table->dropColumn('subscriber_id');
                }
            });
        }
    }
};

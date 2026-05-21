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
            // Drop full_description and add variant and part_number columns
            if (Schema::hasColumn('products', 'full_description')) {
                $table->dropColumn('full_description');
            }
            
            // Add new columns if they don't exist
            if (!Schema::hasColumn('products', 'variant')) {
                $table->text('variant')->nullable()->after('short_description');
            }
            
            if (!Schema::hasColumn('products', 'part_number')) {
                $table->string('part_number')->nullable()->unique()->after('part_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restore full_description and remove variant and part_number
            if (Schema::hasColumn('products', 'variant')) {
                $table->dropColumn('variant');
            }
            
            if (Schema::hasColumn('products', 'part_number')) {
                $table->dropColumn('part_number');
            }
            
            if (!Schema::hasColumn('products', 'full_description')) {
                $table->longText('full_description')->nullable();
            }
        });
    }
};

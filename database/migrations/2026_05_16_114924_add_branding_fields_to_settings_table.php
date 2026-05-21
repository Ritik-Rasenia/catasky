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
        Schema::table('settings', function (Blueprint $table) {
            $table->string('primary_color')->nullable()->default('#4F46E5');
            $table->string('secondary_color')->nullable()->default('#7C3AED');
            $table->string('font_family')->nullable()->default('Poppins');
            $table->string('watermark')->nullable();
            $table->string('pdf_cover_style')->nullable()->default('modern');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'secondary_color', 'font_family', 'watermark', 'pdf_cover_style']);
        });
    }
};

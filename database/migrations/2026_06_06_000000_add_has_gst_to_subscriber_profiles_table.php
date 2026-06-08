<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriber_profiles', function (Blueprint $table) {
            $table->boolean('has_gst')->default(false)->after('gst_number');
        });
    }

    public function down(): void
    {
        Schema::table('subscriber_profiles', function (Blueprint $table) {
            $table->dropColumn('has_gst');
        });
    }
};

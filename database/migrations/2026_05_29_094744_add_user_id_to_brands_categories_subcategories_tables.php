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
        Schema::table('subscriber_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriber_profiles', 'banner')) {
                $table->string('banner')->nullable()->after('logo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriber_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('subscriber_profiles', 'banner')) {
                $table->dropColumn('banner');
            }
        });
    }
};

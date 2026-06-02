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
            if (!Schema::hasColumn('subscriber_profiles', 'custom_domain')) {
                $table->string('custom_domain')->nullable()->unique()->after('company_slug');
            }
            if (!Schema::hasColumn('subscriber_profiles', 'domain_verified')) {
                $table->boolean('domain_verified')->default(false)->after('custom_domain');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriber_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('subscriber_profiles', 'custom_domain')) {
                $table->dropColumn('custom_domain');
            }
            if (Schema::hasColumn('subscriber_profiles', 'domain_verified')) {
                $table->dropColumn('domain_verified');
            }
        });
    }
};

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
        // Drop the old enum columns to completely clear enum constraints
        if (Schema::hasColumn('custom_domains', 'status')) {
            Schema::table('custom_domains', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
        if (Schema::hasColumn('custom_domains', 'ssl_status')) {
            Schema::table('custom_domains', function (Blueprint $table) {
                $table->dropColumn('ssl_status');
            });
        }

        // Add them back as string columns with correct defaults and positions
        Schema::table('custom_domains', function (Blueprint $table) {
            $table->string('status')->default('pending_dns')->after('domain');
            $table->string('ssl_status')->default('pending')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_domains', function (Blueprint $table) {
            $table->dropColumn(['status', 'ssl_status']);
        });

        Schema::table('custom_domains', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'active'])->default('pending')->after('domain');
            $table->enum('ssl_status', ['pending', 'active', 'expired'])->default('pending')->after('status');
        });
    }
};

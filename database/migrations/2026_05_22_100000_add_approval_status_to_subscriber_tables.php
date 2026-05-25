<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
        });

        Schema::table('subscriber_share_links', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('subscriber_products', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });

        Schema::table('subscriber_share_links', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};

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
        Schema::table('custom_domains', function (Blueprint $table) {
            $table->boolean('dns_txt_verified')->default(false)->after('dns_verified');
            $table->boolean('dns_a_verified')->default(false)->after('dns_txt_verified');
            $table->boolean('dns_cname_verified')->default(false)->after('dns_a_verified');
            $table->boolean('admin_approved')->default(false)->after('dns_cname_verified');
            $table->text('rejection_reason')->nullable()->after('admin_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('custom_domains', function (Blueprint $table) {
            $table->dropColumn([
                'dns_txt_verified',
                'dns_a_verified',
                'dns_cname_verified',
                'admin_approved',
                'rejection_reason',
            ]);
        });
    }
};

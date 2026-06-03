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
            $table->date('domain_expires_at')->nullable()->after('rejection_reason');
            $table->date('ssl_expires_at')->nullable()->after('domain_expires_at');
            $table->timestamp('last_revalidated_at')->nullable()->after('ssl_expires_at');
            $table->boolean('dns_mismatch_detected')->default(false)->after('last_revalidated_at');
        });

        Schema::create('custom_domain_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_domain_id')->constrained('custom_domains')->onDelete('cascade');
            $table->string('action'); // e.g. created, dns_check, admin_approved, ssl_generation, daily_revalidation, auto_disabled
            $table->string('status'); // success, failed, info
            $table->text('message');
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_domain_logs');

        Schema::table('custom_domains', function (Blueprint $table) {
            $table->dropColumn([
                'domain_expires_at',
                'ssl_expires_at',
                'last_revalidated_at',
                'dns_mismatch_detected'
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('engagement_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_log_id')->nullable()->constrained('visit_logs')->onDelete('set null');
            $table->foreignId('subscriber_share_link_id')->nullable()->constrained('subscriber_share_links')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('event_type'); // whatsapp_click, call_click, enquiry_submit, catalogue_open, product_detail_open, email_click, direct_link
            $table->foreignId('subscriber_product_id')->nullable()->constrained('subscriber_products')->onDelete('set null');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at'], 'idx_engagement_user_created');
            $table->index(['subscriber_share_link_id', 'created_at'], 'idx_engagement_share_created');
            $table->index(['event_type', 'created_at'], 'idx_engagement_type_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('engagement_logs');
    }
};

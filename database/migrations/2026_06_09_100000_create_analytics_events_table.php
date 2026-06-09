<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unified analytics events table.
     * Single source of truth for ALL frontend-driven tracking events.
     * Replaces fragmented visit_logs, product_view_logs, download_logs,
     * engagement_logs, frontend_events for dashboard metrics.
     */
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();

            // Multi-tenant owner (subscriber user_id); null for anonymous/guest sessions
            $table->unsignedBigInteger('user_id')->nullable();

            // Persistent session identifier from localStorage
            $table->string('session_id', 64);

            // Strict event taxonomy
            $table->string('event_type', 32);
            // catalogue_open | product_view | share_whatsapp | share_any
            // download_pdf   | download_image | enquiry_submit | order_create
            // session_start  | session_end    | link_click

            // Optional context IDs
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('catalogue_id')->nullable(); // subscriber_share_link_id

            // Device fingerprint
            $table->string('device', 16)->default('desktop'); // mobile | tablet | desktop

            // Page context
            $table->text('url')->nullable();
            $table->text('referrer')->nullable();

            // Network
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();

            // Flexible extra data (browse_order, file_type, quantity, duration, etc.)
            $table->json('meta')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // ── Indexes ──────────────────────────────────────────────────
            $table->index('session_id', 'idx_ae_session');
            $table->index('created_at', 'idx_ae_created');
            $table->index('event_type', 'idx_ae_event_type');
            $table->index('product_id', 'idx_ae_product');
            $table->index('catalogue_id', 'idx_ae_catalogue');

            // Composite indexes for dashboard queries
            $table->index(['user_id', 'event_type', 'created_at'], 'idx_ae_user_event_created');
            $table->index(['session_id', 'created_at'], 'idx_ae_session_created');
            $table->index(['catalogue_id', 'event_type', 'created_at'], 'idx_ae_catalogue_event_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};

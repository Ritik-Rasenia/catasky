<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Performance indexes for the Advanced Analytics module.
     * These composite indexes speed up the most common aggregation queries.
     */
    public function up(): void
    {
        // visit_logs: most queries filter by share_link + date, or visitor + date
        Schema::table('visit_logs', function (Blueprint $table) {
            $table->index(['subscriber_share_link_id', 'opened_at'], 'idx_visit_logs_share_opened');
            $table->index(['visitor_uuid', 'opened_at'], 'idx_visit_logs_visitor_opened');
            $table->index(['country', 'opened_at'], 'idx_visit_logs_country_opened');
            $table->index(['bounce', 'opened_at'], 'idx_visit_logs_bounce_opened');
        });

        // product_view_logs: most queries filter by product + date or visit + date
        Schema::table('product_view_logs', function (Blueprint $table) {
            $table->index(['subscriber_product_id', 'viewed_at'], 'idx_pvl_product_viewed');
            $table->index(['visit_log_id', 'viewed_at'], 'idx_pvl_visit_viewed');
        });

        // download_logs: most queries filter by share_link + date or file_type + date
        Schema::table('download_logs', function (Blueprint $table) {
            $table->index(['subscriber_share_link_id', 'downloaded_at'], 'idx_dl_share_downloaded');
            $table->index(['file_type', 'downloaded_at'], 'idx_dl_type_downloaded');
            $table->index(['user_id', 'downloaded_at'], 'idx_dl_user_downloaded');
        });

        // share_tracks: most queries filter by user + date or channel + date
        Schema::table('share_tracks', function (Blueprint $table) {
            $table->index(['user_id', 'shared_at'], 'idx_st_user_shared');
            $table->index(['channel', 'shared_at'], 'idx_st_channel_shared');
        });

        // order_logs: queries filter by status or share_link
        Schema::table('order_logs', function (Blueprint $table) {
            $table->index(['subscriber_share_link_id', 'created_at'], 'idx_ol_share_created');
            $table->index(['status', 'created_at'], 'idx_ol_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_visit_logs_share_opened');
            $table->dropIndex('idx_visit_logs_visitor_opened');
            $table->dropIndex('idx_visit_logs_country_opened');
            $table->dropIndex('idx_visit_logs_bounce_opened');
        });

        Schema::table('product_view_logs', function (Blueprint $table) {
            $table->dropIndex('idx_pvl_product_viewed');
            $table->dropIndex('idx_pvl_visit_viewed');
        });

        Schema::table('download_logs', function (Blueprint $table) {
            $table->dropIndex('idx_dl_share_downloaded');
            $table->dropIndex('idx_dl_type_downloaded');
            $table->dropIndex('idx_dl_user_downloaded');
        });

        Schema::table('share_tracks', function (Blueprint $table) {
            $table->dropIndex('idx_st_user_shared');
            $table->dropIndex('idx_st_channel_shared');
        });

        Schema::table('order_logs', function (Blueprint $table) {
            $table->dropIndex('idx_ol_share_created');
            $table->dropIndex('idx_ol_status_created');
        });
    }
};

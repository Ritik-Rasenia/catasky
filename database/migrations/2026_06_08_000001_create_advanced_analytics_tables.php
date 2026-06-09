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
        Schema::create('share_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('subscriber_share_link_id')->nullable()->constrained('subscriber_share_links')->onDelete('cascade');
            $table->foreignId('subscriber_product_id')->nullable()->constrained('subscriber_products')->onDelete('cascade');
            $table->string('tracking_token', 64)->unique();
            $table->string('channel', 32); // whatsapp, email, facebook, direct_link
            $table->timestamp('shared_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('visit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_share_link_id')->nullable()->constrained('subscriber_share_links')->onDelete('cascade');
            $table->foreignId('share_track_id')->nullable()->constrained('share_tracks')->onDelete('set null');
            $table->string('session_id', 64)->index();
            $table->string('visitor_uuid', 64)->index()->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_type', 32); // Mobile, Tablet, Desktop, Bot
            $table->string('browser', 64);
            $table->string('os', 64);
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('referrer')->nullable();
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->integer('total_time_spent')->default(0); // in seconds
            $table->boolean('bounce')->default(true);
            $table->timestamps();
        });

        Schema::create('product_view_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_log_id')->constrained('visit_logs')->onDelete('cascade');
            $table->foreignId('subscriber_product_id')->constrained('subscriber_products')->onDelete('cascade');
            $table->timestamp('viewed_at')->useCurrent();
            $table->integer('duration')->default(0); // in seconds
            $table->integer('browse_order')->default(1);
            $table->timestamps();
        });

        Schema::create('download_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_log_id')->nullable()->constrained('visit_logs')->onDelete('set null');
            $table->foreignId('subscriber_share_link_id')->constrained('subscriber_share_links')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('ip_address', 45)->nullable();
            $table->string('file_type', 32); // pdf, brochure, catalog, image
            $table->timestamp('downloaded_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('order_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_log_id')->nullable()->constrained('visit_logs')->onDelete('set null');
            $table->foreignId('subscriber_share_link_id')->constrained('subscriber_share_links')->onDelete('cascade');
            $table->foreignId('subscriber_product_id')->nullable()->constrained('subscriber_products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 12, 2)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_email', 255)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 32)->default('pending'); // pending, completed, cancelled
            $table->timestamps();
        });

        Schema::table('enquiries', function (Blueprint $table) {
            $table->foreignId('visit_log_id')->nullable()->after('subscriber_product_id')->constrained('visit_logs')->onDelete('set null');
            $table->foreignId('share_track_id')->nullable()->after('visit_log_id')->constrained('share_tracks')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropForeign(['visit_log_id']);
            $table->dropColumn('visit_log_id');
            $table->dropForeign(['share_track_id']);
            $table->dropColumn('share_track_id');
        });

        Schema::dropIfExists('order_logs');
        Schema::dropIfExists('download_logs');
        Schema::dropIfExists('product_view_logs');
        Schema::dropIfExists('visit_logs');
        Schema::dropIfExists('share_tracks');
    }
};

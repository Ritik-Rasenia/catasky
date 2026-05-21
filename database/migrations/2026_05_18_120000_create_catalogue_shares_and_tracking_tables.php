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
        Schema::create('catalogue_shares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('catalogue_code')->unique(); // Unique 7-character code (e.g. ABCD123)
            $table->text('product_ids'); // Comma-separated or JSON array of product IDs
            $table->string('customer_phone');
            $table->string('message_id')->nullable(); // DoubleTick message ID for webhook status updates
            $table->string('delivery_status')->default('pending'); // pending, sent, delivered, failed
            $table->string('seen_status')->default('unread'); // unread, read
            $table->string('clicked_status')->default('no'); // no, yes
            $table->string('opened_status')->default('no'); // no, yes
            $table->integer('total_view_time')->default(0); // View session duration in seconds
            $table->integer('visit_count')->default(0); // Track repeat visits
            $table->timestamps();
        });

        Schema::create('share_tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('share_id');
            $table->string('event_type'); // delivery, seen, click, open, heartbeat
            $table->timestamp('event_time')->useCurrent();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->foreign('share_id')->references('id')->on('catalogue_shares')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_tracking_logs');
        Schema::dropIfExists('catalogue_shares');
    }
};

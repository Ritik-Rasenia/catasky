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
        Schema::dropIfExists('frontend_events');

        Schema::create('frontend_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscriber_id')->nullable();
            $table->unsignedBigInteger('catalogue_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('session_id', 64)->nullable();
            $table->string('event_type', 32);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('meta_json')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes as requested
            $table->index('subscriber_id');
            $table->index('catalogue_id');
            $table->index('product_id');
            $table->index('event_type');
            $table->index('session_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('frontend_events');
    }
};

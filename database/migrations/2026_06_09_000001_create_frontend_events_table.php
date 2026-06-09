<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frontend_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // subscriber/admin user id (nullable for guest visitors)
            $table->string('event');                            // pdf_download, image_download, whatsapp_share, other_share, product_view
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('file_type')->nullable();           // pdf, image
            $table->json('meta')->nullable();                  // extra data: url, page, referrer, etc.
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at'], 'idx_fe_event_created');
            $table->index(['user_id', 'created_at'], 'idx_fe_user_created');
            $table->index(['product_id', 'created_at'], 'idx_fe_product_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frontend_events');
    }
};

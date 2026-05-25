<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriber_share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // subscriber user
            $table->foreignId('subscriber_product_id')->nullable()->constrained()->onDelete('cascade'); // null = catalog share
            $table->string('token', 64)->unique();
            $table->string('title')->nullable();
            $table->enum('type', ['pdf', 'image', 'catalog', 'whatsapp'])->default('catalog');
            $table->string('password')->nullable(); // optional password protection
            // Visibility settings stored as JSON for flexibility
            $table->json('settings')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->integer('download_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('pdf_path')->nullable(); // generated PDF file path
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_share_links');
    }
};

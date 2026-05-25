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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // if logged in subscriber or admin
            $table->tinyInteger('rating');
            $table->string('reviewer_name');
            $table->string('reviewer_email');
            $table->text('review_content');
            $table->json('images')->nullable(); // JSON array of review attachments
            $table->boolean('is_verified_buyer')->default(false);
            $table->boolean('status')->default(true); // active/visible by default
            $table->timestamps();

            // Indexing for rating queries
            $table->index('product_id');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

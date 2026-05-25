<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriber_pdf_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // subscriber user
            $table->string('name')->default('Default Template');
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_watermark')->default(false);
            $table->string('watermark_text')->nullable();
            $table->boolean('show_qr_code')->default(true);
            $table->boolean('show_page_numbers')->default(true);
            $table->string('brand_color')->default('#4F46E5');
            $table->string('accent_color')->default('#7C3AED');
            $table->enum('layout', ['grid', 'list', 'detailed'])->default('grid');
            $table->enum('paper_size', ['A4', 'A3', 'Letter'])->default('A4');
            $table->enum('orientation', ['portrait', 'landscape'])->default('portrait');
            $table->string('header_text')->nullable();
            $table->string('footer_text')->nullable();
            $table->boolean('is_default')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_pdf_templates');
    }
};

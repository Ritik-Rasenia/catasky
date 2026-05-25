<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // subscriber user
            $table->foreignId('attribute_group_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug');
            $table->enum('type', [
                'text', 'number', 'select', 'multiselect',
                'checkbox', 'radio', 'textarea',
                'image', 'file', 'color', 'date', 'url'
            ])->default('text');
            $table->text('default_value')->nullable();
            $table->json('validation_rules')->nullable();
            $table->string('unit')->nullable(); // e.g., "kg", "cm", "V"
            $table->string('placeholder')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_searchable')->default(false);
            $table->boolean('show_in_pdf')->default(true);
            $table->boolean('show_in_share')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};

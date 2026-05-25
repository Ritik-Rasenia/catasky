<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // subscriber user
            $table->string('domain')->unique();
            $table->enum('status', ['pending', 'approved', 'rejected', 'active'])->default('pending');
            $table->enum('ssl_status', ['pending', 'active', 'expired'])->default('pending');
            $table->string('dns_txt_key')->nullable();
            $table->string('dns_txt_value')->nullable();
            $table->boolean('dns_verified')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_domains');
    }
};

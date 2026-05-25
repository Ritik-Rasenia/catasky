<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriber_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('company_slug')->unique();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->string('pincode')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('logo')->nullable();
            $table->text('bio')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('email_for_inquiries')->nullable();
            $table->string('primary_color')->default('#4F46E5');
            $table->string('secondary_color')->default('#7C3AED');
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
            $table->boolean('is_verified')->default(false);
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriber_profiles');
    }
};

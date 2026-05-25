<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('gateway')->default('dummy'); // razorpay, stripe, paypal, dummy
            $table->string('gateway_payment_id')->nullable();
            $table->string('gateway_order_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('INR');
            $table->enum('status', ['pending', 'success', 'failed', 'refunded'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('frontend_events', function (Blueprint $table) {
            $table->unsignedBigInteger('subscriber_id')->nullable()->after('user_id');
            $table->string('user_agent', 512)->nullable()->after('ip_address');
            $table->index(['subscriber_id', 'created_at'], 'idx_fe_subscriber_created');
            $table->index(['subscriber_id', 'event', 'created_at'], 'idx_fe_sub_event_created');
        });
    }

    public function down(): void
    {
        Schema::table('frontend_events', function (Blueprint $table) {
            $table->dropIndex('idx_fe_subscriber_created');
            $table->dropIndex('idx_fe_sub_event_created');
            $table->dropColumn(['subscriber_id', 'user_agent']);
        });
    }
};

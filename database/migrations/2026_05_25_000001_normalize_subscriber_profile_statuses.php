<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriber_profiles MODIFY status ENUM('pending','approved','rejected','suspended','active') NOT NULL DEFAULT 'pending'");
        }
        DB::table('subscriber_profiles')->where('status', 'active')->update(['status' => 'approved']);
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriber_profiles MODIFY status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriber_profiles MODIFY status ENUM('active','suspended','pending','rejected','approved') NOT NULL DEFAULT 'pending'");
        }
        DB::table('subscriber_profiles')->where('status', 'approved')->update(['status' => 'active']);
        DB::table('subscriber_profiles')->where('status', 'rejected')->update(['status' => 'suspended']);
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE subscriber_profiles MODIFY status ENUM('active','suspended','pending') NOT NULL DEFAULT 'pending'");
        }
    }
};

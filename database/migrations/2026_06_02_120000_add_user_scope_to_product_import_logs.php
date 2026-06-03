<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_import_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('product_import_logs', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('product_import_logs', 'scope')) {
                $table->string('scope')->default('admin')->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_import_logs', function (Blueprint $table) {
            if (Schema::hasColumn('product_import_logs', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
            if (Schema::hasColumn('product_import_logs', 'scope')) {
                $table->dropColumn('scope');
            }
        });
    }
};

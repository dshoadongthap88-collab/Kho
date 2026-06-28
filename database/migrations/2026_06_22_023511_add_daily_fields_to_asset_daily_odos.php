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
        Schema::table('asset_daily_odos', function (Blueprint $table) {
            $table->string('operator')->nullable()->after('shifts_count');
            $table->string('phone')->nullable()->after('operator');
            $table->string('status')->default('approved')->after('phone'); // pending or approved
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_daily_odos', function (Blueprint $table) {
            $table->dropColumn(['operator', 'phone', 'status']);
        });
    }
};

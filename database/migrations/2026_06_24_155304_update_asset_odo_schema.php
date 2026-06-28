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
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('lifetime_odo', 15, 2)->default(0)->after('status');
            $table->decimal('lifetime_hours', 15, 2)->default(0)->after('lifetime_odo');
            $table->decimal('cycle_odo', 15, 2)->default(0)->after('lifetime_hours');
            $table->decimal('cycle_hours', 15, 2)->default(0)->after('cycle_odo');
        });

        Schema::table('asset_daily_odos', function (Blueprint $table) {
            $table->boolean('is_synced')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['lifetime_odo', 'lifetime_hours', 'cycle_odo', 'cycle_hours']);
        });

        Schema::table('asset_daily_odos', function (Blueprint $table) {
            $table->dropColumn('is_synced');
        });
    }
};

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
        $tables = [
            'maintenance_boms',
            'maintenance_bom_items',
            'maintenance_tickets',
            'maintenance_items',
            'maintenance_rules',
            'maintenance_plans',
            'asset_odo_readings',
            'asset_daily_odos'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'house_id')) {
                    $table->unsignedBigInteger('house_id')->nullable()->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'maintenance_boms',
            'maintenance_bom_items',
            'maintenance_tickets',
            'maintenance_items',
            'maintenance_rules',
            'maintenance_plans',
            'asset_odo_readings',
            'asset_daily_odos'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                if (Schema::hasColumn($table->getTable(), 'house_id')) {
                    $table->dropColumn('house_id');
                }
            });
        }
    }
};

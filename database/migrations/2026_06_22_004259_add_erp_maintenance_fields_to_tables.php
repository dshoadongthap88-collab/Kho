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
            $table->decimal('hours_per_shift', 8, 2)->default(8)->after('current_hours');
            $table->integer('maintenance_cycle_hours')->nullable()->after('hours_per_shift');
            $table->integer('maintenance_cycle_odo')->nullable()->after('maintenance_cycle_hours');
            $table->decimal('last_maintenance_hours', 15, 2)->default(0)->after('maintenance_cycle_odo');
            $table->decimal('last_maintenance_odo', 15, 2)->default(0)->after('last_maintenance_hours');
        });

        Schema::table('asset_daily_odos', function (Blueprint $table) {
            $table->decimal('shifts_count', 8, 2)->default(1)->after('reading_date');
        });

        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->json('replaced_materials')->nullable()->after('description');
            $table->decimal('total_cost', 15, 2)->default(0)->after('replaced_materials');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            //
        });
    }
};

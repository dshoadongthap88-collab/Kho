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
        Schema::table('maintenance_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_plans', 'maintenance_bom_id')) {
                $table->unsignedBigInteger('maintenance_bom_id')->nullable()->after('asset_id');
            }
            if (!Schema::hasColumn('maintenance_plans', 'total_cost')) {
                $table->decimal('total_cost', 15, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('maintenance_plans', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('total_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_plans', function (Blueprint $table) {
            $table->dropColumn(['maintenance_bom_id', 'total_cost', 'completed_at']);
        });
    }
};

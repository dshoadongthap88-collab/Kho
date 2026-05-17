<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('engine_oil_cap')->nullable();
            $table->string('hydraulic_oil_cap')->nullable();
            $table->string('engine_oil_filter')->nullable();
            $table->string('hydraulic_filter')->nullable();
            $table->string('air_filter')->nullable();
            $table->string('maintenance_cycle')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'engine_oil_cap', 'hydraulic_oil_cap', 'engine_oil_filter',
                'hydraulic_filter', 'air_filter', 'maintenance_cycle'
            ]);
        });
    }
};

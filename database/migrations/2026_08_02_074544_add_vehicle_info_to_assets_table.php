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
            $table->string('license_plate')->nullable()->after('asset_code');
            $table->string('driver_name')->nullable()->after('license_plate');
            $table->string('phone_number')->nullable()->after('driver_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['license_plate', 'driver_name', 'phone_number']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->decimal('current_odo', 15, 2)->default(0)->after('status');
            $table->decimal('current_hours', 15, 2)->default(0)->after('current_odo');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['current_odo', 'current_hours']);
        });
    }
};

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
        Schema::table('delivery_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_reports', 'due_date')) {
                $table->date('due_date')->nullable()->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_reports', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_reports', 'due_date')) {
                $table->dropColumn('due_date');
            }
        });
    }
};

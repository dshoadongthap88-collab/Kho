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
        Schema::table('stock_out_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_out_items', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('stock_out_items', 'vat_rate')) {
                $table->decimal('vat_rate', 5, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('stock_out_items', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('vat_rate');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_out_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'vat_rate', 'total_amount']);
        });
    }
};

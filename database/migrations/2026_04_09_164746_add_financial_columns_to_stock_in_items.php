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
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->nullable()->after('quantity');
            $table->decimal('vat_rate', 5, 2)->nullable()->after('unit_price');
            $table->decimal('total_amount', 15, 2)->nullable()->after('vat_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'vat_rate', 'total_amount']);
        });
    }
};

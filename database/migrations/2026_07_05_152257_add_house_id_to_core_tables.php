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
            'products',
            'categories',
            'warehouses',
            'inventories',
            'inventory_transactions',
            'stock_ins',
            'stock_outs',
            'stock_transfers',
            'assets'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->unsignedBigInteger('house_id')->nullable()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'products',
            'categories',
            'warehouses',
            'inventories',
            'inventory_transactions',
            'stock_ins',
            'stock_outs',
            'stock_transfers',
            'assets'
        ];

        foreach ($tables as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('house_id');
            });
        }
    }
};

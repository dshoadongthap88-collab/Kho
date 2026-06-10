<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_count_items', function (Blueprint $table) {
            $table->string('product_code')->nullable()->after('product_id');
            $table->string('product_name')->nullable()->after('product_code');
            $table->string('unit')->nullable()->after('product_name');
            $table->string('warehouse_location')->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('stock_count_items', function (Blueprint $table) {
            $table->dropColumn(['warehouse_location', 'unit', 'product_name', 'product_code']);
        });
    }
};
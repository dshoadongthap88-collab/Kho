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
            if (!Schema::hasColumn('stock_in_items', 'stock_in_id')) {
                $table->foreignId('stock_in_id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('stock_in_items', 'product_id')) {
                $table->foreignId('product_id')->constrained();
            }
            if (!Schema::hasColumn('stock_in_items', 'batch_number')) {
                $table->string('batch_number');
            }
            if (!Schema::hasColumn('stock_in_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable();
            }
            if (!Schema::hasColumn('stock_in_items', 'warehouse_location')) {
                $table->string('warehouse_location')->nullable();
            }
            if (!Schema::hasColumn('stock_in_items', 'quantity')) {
                $table->decimal('quantity', 15, 4);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropColumn(['stock_in_id', 'product_id', 'batch_number', 'expiry_date', 'warehouse_location', 'quantity']);
        });
    }
};

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
        Schema::table('stock_recoveries', function (Blueprint $table) {
            // Drop foreign key and column for purchase_order_id if it exists
            if (Schema::hasColumn('stock_recoveries', 'purchase_order_id')) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropColumn('purchase_order_id');
            }
            
            // Add stock_out_id
            $table->foreignId('stock_out_id')->nullable()->constrained('stock_outs')->onDelete('set null')->after('recovery_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_recoveries', function (Blueprint $table) {
            if (Schema::hasColumn('stock_recoveries', 'stock_out_id')) {
                $table->dropForeign(['stock_out_id']);
                $table->dropColumn('stock_out_id');
            }
            
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->onDelete('set null')->after('recovery_number');
        });
    }
};

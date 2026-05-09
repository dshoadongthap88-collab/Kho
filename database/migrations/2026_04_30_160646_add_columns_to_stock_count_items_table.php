<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_count_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_count_items', 'stock_count_id')) {
                $table->foreignId('stock_count_id')->after('id')->constrained('stock_counts')->onDelete('cascade');
            }
            if (!Schema::hasColumn('stock_count_items', 'product_id')) {
                $table->foreignId('product_id')->after('stock_count_id')->constrained('products')->onDelete('cascade');
            }
            if (!Schema::hasColumn('stock_count_items', 'system_quantity')) {
                $table->decimal('system_quantity', 15, 2)->default(0)->after('product_id')->comment('Tồn kho theo hệ thống');
            }
            if (!Schema::hasColumn('stock_count_items', 'actual_quantity')) {
                $table->decimal('actual_quantity', 15, 2)->nullable()->after('system_quantity')->comment('Kiểm đếm thực tế');
            }
            if (!Schema::hasColumn('stock_count_items', 'difference')) {
                $table->decimal('difference', 15, 2)->default(0)->after('actual_quantity')->comment('Chênh lệch = thực tế - hệ thống');
            }
            if (!Schema::hasColumn('stock_count_items', 'note')) {
                $table->text('note')->nullable()->after('difference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_count_items', function (Blueprint $table) {
            $table->dropColumn(['stock_count_id', 'product_id', 'system_quantity', 'actual_quantity', 'difference', 'note']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_count_items', function (Blueprint $table) {
            $table->foreignId('stock_count_id')->after('id')->constrained('stock_counts')->onDelete('cascade');
            $table->foreignId('product_id')->after('stock_count_id')->constrained('products')->onDelete('cascade');
            $table->decimal('system_quantity', 15, 2)->default(0)->after('product_id')->comment('Tồn kho theo hệ thống');
            $table->decimal('actual_quantity', 15, 2)->nullable()->after('system_quantity')->comment('Số lượng kiểm đếm thực tế');
            $table->decimal('difference', 15, 2)->default(0)->after('actual_quantity')->comment('Chênh lệch = thực tế - hệ thống');
            $table->text('note')->nullable()->after('difference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
    }
};

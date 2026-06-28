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
        Schema::create('purchase_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('proposed_quantity', 10, 2);
            $table->decimal('delivered_quantity', 10, 2)->default(0);
            $table->date('expected_delivery_date')->nullable();
            $table->string('status')->default('pending'); // pending (Đề xuất), ordered (Đã đặt hàng), unreceived (Chưa giao), partial (Giao thiếu), completed (Đủ hàng)
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_plans');
    }
};

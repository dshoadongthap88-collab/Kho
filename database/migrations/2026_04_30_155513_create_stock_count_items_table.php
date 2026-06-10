<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_count_id')->constrained('stock_counts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('system_quantity', 15, 2)->default(0)->comment('Ton kho theo he thong');
            $table->decimal('actual_quantity', 15, 2)->nullable()->comment('So luong kiem dem thuc te');
            $table->decimal('difference', 15, 2)->default(0)->comment('Chenh lech = thuc te - he thong');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_count_items');
    }
};
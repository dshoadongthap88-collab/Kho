<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('product_code');
            $table->string('product_name')->nullable()->after('product_id');
            $table->string('unit')->nullable()->after('quantity');
            $table->text('note')->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropColumn(['product_id', 'product_name', 'unit', 'note']);
        });
    }
};

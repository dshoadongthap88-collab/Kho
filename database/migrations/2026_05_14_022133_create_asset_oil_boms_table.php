<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_oil_boms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Oil
            $table->string('bom_code')->nullable();
            $table->decimal('standard_qty', 15, 3)->default(0);
            $table->decimal('min_qty', 15, 3)->default(0);
            $table->decimal('max_qty', 15, 3)->default(0);
            $table->integer('replace_cycle_hour')->nullable();
            $table->integer('replace_cycle_day')->nullable();
            $table->integer('warning_before_day')->nullable();
            $table->string('vendor')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_oil_boms');
    }
};

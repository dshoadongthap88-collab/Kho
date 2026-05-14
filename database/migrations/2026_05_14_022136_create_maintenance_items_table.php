<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_ticket_id')->constrained('maintenance_tickets')->onDelete('cascade');
            $table->foreignId('asset_oil_bom_id')->constrained('asset_oil_boms')->onDelete('cascade');
            $table->decimal('suggested_qty', 15, 3)->default(0);
            $table->decimal('actual_qty', 15, 3)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_items');
    }
};

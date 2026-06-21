<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_code')->unique();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('category')->nullable(); // Hạng mục
            $table->date('expected_date')->nullable();
            $table->decimal('current_odo', 15, 2)->default(0);
            $table->decimal('maintenance_odo', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, doing, completed
            $table->string('assigned_to')->nullable(); // Người phụ trách
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_plans');
    }
};

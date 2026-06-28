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
        Schema::create('purchase_plan_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_plan_id')->constrained()->onDelete('cascade');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->decimal('old_quantity', 15, 2)->nullable();
            $table->decimal('new_quantity', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_plan_histories');
    }
};

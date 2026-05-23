<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('asset_odo_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->date('reading_date');
            $table->decimal('current_hours', 10, 2);
            $table->string('operator', 100)->nullable();
            $table->enum('status', ['maintenance_required', 'maintenance_done', 'normal'])->default('normal');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'reading_date']);
            $table->index(['product_id', 'status']);
            $table->index('reading_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_odo_readings');
    }
};

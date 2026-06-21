<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_daily_odos', function (Blueprint $table) {
            $table->id();
            $table->date('reading_date');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->decimal('old_odo', 15, 2)->default(0);
            $table->decimal('new_odo', 15, 2)->default(0);
            $table->decimal('odo_diff', 15, 2)->default(0);
            $table->decimal('old_hours', 15, 2)->default(0);
            $table->decimal('new_hours', 15, 2)->default(0);
            $table->decimal('hours_diff', 15, 2)->default(0);
            $table->string('updated_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_daily_odos');
    }
};

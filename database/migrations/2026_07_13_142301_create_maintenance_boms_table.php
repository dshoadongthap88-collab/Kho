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
        Schema::create('maintenance_boms', function (Blueprint $table) {
            $table->id();
            $table->string('bom_code')->unique();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('maintenance_level'); // e.g. "1000 giờ"
            $table->integer('cycle'); // e.g. 1000
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_boms');
    }
};

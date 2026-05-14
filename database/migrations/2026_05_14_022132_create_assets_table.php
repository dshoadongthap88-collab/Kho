<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->string('department')->nullable();
            $table->string('machine_type')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->date('installation_date')->nullable();
            $table->string('status')->default('active'); // active, maintenance, inactive
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};

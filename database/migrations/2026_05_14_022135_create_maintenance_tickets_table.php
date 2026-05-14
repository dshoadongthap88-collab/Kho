<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_code')->unique();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->date('maintenance_date');
            $table->string('type')->default('oil_change'); // oil_change, repair, etc.
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_tickets');
    }
};

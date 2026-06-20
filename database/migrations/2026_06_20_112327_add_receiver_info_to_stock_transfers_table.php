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
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->string('sender_phone')->nullable();
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receiver_phone')->nullable();
            $table->unsignedBigInteger('from_project_id')->nullable();
            $table->unsignedBigInteger('to_project_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropForeign(['receiver_id']);
            $table->dropColumn(['sender_phone', 'receiver_id', 'receiver_phone', 'from_project_id', 'to_project_id']);
        });
    }
};

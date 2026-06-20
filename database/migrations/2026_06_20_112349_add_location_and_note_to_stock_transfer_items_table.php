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
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->string('location')->nullable();
            // Note is already in fillable of model, but check if column exists. We can just add it if not exists.
            if (!Schema::hasColumn('stock_transfer_items', 'note')) {
                $table->text('note')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transfer_items', function (Blueprint $table) {
            $table->dropColumn(['location']);
            if (Schema::hasColumn('stock_transfer_items', 'note')) {
                // $table->dropColumn(['note']);
            }
        });
    }
};

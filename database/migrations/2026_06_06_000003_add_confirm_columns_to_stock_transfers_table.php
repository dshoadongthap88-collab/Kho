<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('confirmed_by')->nullable()->after('status');
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('confirmed_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfers', function (Blueprint $table) {
            $table->dropColumn(['confirmed_by', 'confirmed_at', 'cancelled_by', 'cancelled_at']);
        });
    }
};

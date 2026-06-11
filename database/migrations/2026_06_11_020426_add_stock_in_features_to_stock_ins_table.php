<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->date('stock_in_date')->nullable()->after('type');
            $table->boolean('marked_received')->default(false)->after('stock_in_date');
            $table->timestamp('received_at')->nullable()->after('marked_received');
        });
    }

    public function down(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropColumn(['stock_in_date', 'marked_received', 'received_at']);
        });
    }
};

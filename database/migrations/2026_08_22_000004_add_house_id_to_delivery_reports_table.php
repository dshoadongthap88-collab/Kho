<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('house_id')->nullable()->index()->after('id');
        });

        // Backfill: lấy house_id từ stock_outs liên quan
        \Illuminate\Support\Facades\DB::statement('
            UPDATE delivery_reports dr
            JOIN stock_outs so ON so.id = dr.stock_out_id
            SET dr.house_id = so.house_id
            WHERE dr.house_id IS NULL AND so.house_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('delivery_reports', function (Blueprint $table) {
            $table->dropIndex(['house_id']);
            $table->dropColumn('house_id');
        });
    }
};

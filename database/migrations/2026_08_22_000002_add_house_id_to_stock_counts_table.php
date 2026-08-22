<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_counts', function (Blueprint $table) {
            $table->unsignedBigInteger('house_id')->nullable()->index()->after('id');
        });

        Schema::table('stock_count_items', function (Blueprint $table) {
            $table->unsignedBigInteger('house_id')->nullable()->index()->after('id');
        });

        // Backfill: gán house_id từ bảng inventories qua product_id cho các record cũ
        \Illuminate\Support\Facades\DB::statement('
            UPDATE stock_counts sc
            JOIN (
                SELECT sc2.id, inv.house_id
                FROM stock_counts sc2
                JOIN stock_count_items sci ON sci.stock_count_id = sc2.id
                JOIN inventories inv ON inv.product_id = sci.product_id
                WHERE inv.house_id IS NOT NULL
                GROUP BY sc2.id, inv.house_id
            ) t ON sc.id = t.id
            SET sc.house_id = t.house_id
            WHERE sc.house_id IS NULL
        ');

        \Illuminate\Support\Facades\DB::statement('
            UPDATE stock_count_items sci
            JOIN stock_counts sc ON sc.id = sci.stock_count_id
            SET sci.house_id = sc.house_id
            WHERE sci.house_id IS NULL AND sc.house_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('stock_counts', function (Blueprint $table) {
            $table->dropIndex(['house_id']);
            $table->dropColumn('house_id');
        });

        Schema::table('stock_count_items', function (Blueprint $table) {
            $table->dropIndex(['house_id']);
            $table->dropColumn('house_id');
        });
    }
};

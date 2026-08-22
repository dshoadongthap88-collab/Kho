<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chỉ thêm nếu chưa có (house_id đã được code gán thủ công trước đó)
        if (!Schema::hasColumn('purchase_plans', 'house_id')) {
            Schema::table('purchase_plans', function (Blueprint $table) {
                $table->unsignedBigInteger('house_id')->nullable()->index()->after('id');
            });
        } else {
            // Đảm bảo có index
            Schema::table('purchase_plans', function (Blueprint $table) {
                try {
                    $table->index('house_id');
                } catch (\Exception $e) {
                    // Index đã tồn tại, bỏ qua
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('purchase_plans', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_plans', 'house_id')) {
                try { $table->dropIndex(['house_id']); } catch (\Exception $e) {}
                $table->dropColumn('house_id');
            }
        });
    }
};

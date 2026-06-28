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
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->string('maintenance_rule_id')->nullable()->after('asset_id'); // Cấp bảo dưỡng
            $table->decimal('maintenance_odo', 15, 2)->nullable()->after('maintenance_date'); // Giờ máy tại thời điểm bảo dưỡng
            $table->text('materials_used')->nullable()->after('description'); // Vật tư đã sử dụng
            $table->string('staff_name')->nullable()->after('materials_used'); // Nhân viên thực hiện
            $table->string('inspector')->nullable()->after('staff_name'); // Người kiểm tra
            $table->string('result')->nullable()->after('inspector'); // Kết quả sau bảo dưỡng
            $table->string('image_before')->nullable()->after('result'); // Hình trước BD
            $table->string('image_after')->nullable()->after('image_before'); // Hình sau BD
            $table->text('notes')->nullable()->after('image_after'); // Ghi chú
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'maintenance_rule_id', 'maintenance_odo', 'materials_used', 
                'staff_name', 'inspector', 'result', 'image_before', 'image_after', 'notes'
            ]);
        });
    }
};

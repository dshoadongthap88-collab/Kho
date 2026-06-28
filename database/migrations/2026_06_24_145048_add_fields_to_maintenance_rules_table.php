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
        Schema::table('maintenance_rules', function (Blueprint $table) {
            $table->string('rule_code')->nullable()->after('id'); // Mã cấp bảo dưỡng: BD250, BD500
            $table->string('name')->nullable()->after('rule_code'); // Tên cấp bảo dưỡng
            $table->decimal('estimated_time', 8, 2)->nullable()->after('material_needed'); // Thời gian dự kiến (giờ)
            $table->text('notes')->nullable()->after('estimated_time'); // Ghi chú
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_rules', function (Blueprint $table) {
            $table->dropColumn(['rule_code', 'name', 'estimated_time', 'notes']);
        });
    }
};

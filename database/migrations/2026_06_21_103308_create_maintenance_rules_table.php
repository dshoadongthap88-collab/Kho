<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_rules', function (Blueprint $table) {
            $table->id();
            $table->string('machine_type')->nullable(); // Loại thiết bị
            $table->string('category')->nullable(); // Hạng mục (Thay nhớt, bảo dưỡng cấp 1...)
            $table->decimal('cycle_km', 15, 2)->default(0); // Chu kỳ Km
            $table->decimal('cycle_hours', 15, 2)->default(0); // Chu kỳ giờ máy
            $table->integer('cycle_months')->default(0); // Chu kỳ tháng
            $table->text('content')->nullable(); // Nội dung bảo dưỡng
            $table->longText('material_needed')->nullable(); // Vật tư cần thay (lưu ID product)
            $table->string('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_rules');
    }
};

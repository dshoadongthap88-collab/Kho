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
        Schema::create('system_modules', function (Blueprint $table) {
            $table->id();
            $table->string('group_name')->comment('Tên nhóm module để hiển thị gom nhóm');
            $table->string('route_name')->unique()->comment('Tên route để phân quyền');
            $table->string('label')->comment('Tên hiển thị của module');
            $table->boolean('is_active')->default(true)->comment('Trạng thái bật/tắt module');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_modules');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gỡ index unique cũ `inventories_product_id_unique`.
 *
 * Index này có từ thời hệ thống chỉ có một kho. Sang mô hình nhiều dự án thì
 * mỗi vật tư phải có một dòng tồn kho cho MỖI dự án, nên ràng buộc đúng là
 * (house_id, product_id) — đã có sẵn là `inventories_house_product_unique`.
 *
 * Còn giữ index cũ thì:
 *   - Mở modal Sửa ở một dự án chưa có dòng tồn kho -> Duplicate entry ... for
 *     key 'inventories_product_id_unique' (lỗi 1062).
 *   - Nhập Excel tồn kho vào dự án thứ hai cũng vỡ vì lý do y hệt.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('inventories')) {
            return;
        }

        // Đảm bảo ràng buộc đúng đã tồn tại trước khi gỡ ràng buộc cũ
        if (!$this->indexExists('inventories', 'inventories_house_product_unique')) {
            Schema::table('inventories', function ($table) {
                $table->unique(['house_id', 'product_id'], 'inventories_house_product_unique');
            });
        }

        if ($this->indexExists('inventories', 'inventories_product_id_unique')) {
            DB::statement('ALTER TABLE `inventories` DROP INDEX `inventories_product_id_unique`');
        }

        // Giữ lại index thường trên product_id để các truy vấn lọc theo vật tư
        // vẫn nhanh sau khi bỏ unique
        if (!$this->indexExists('inventories', 'inventories_product_id_index')) {
            Schema::table('inventories', function ($table) {
                $table->index('product_id', 'inventories_product_id_index');
            });
        }
    }

    public function down(): void
    {
        // Cố ý không dựng lại index cũ: nó phá vỡ mô hình nhiều dự án.
    }

    private function indexExists(string $table, string $index): bool
    {
        return count(DB::select(
            'SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]
        )) > 0;
    }
};

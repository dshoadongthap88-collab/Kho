<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gỡ index unique cũ `products_code_unique`.
 *
 * Cùng loại lỗi với `inventories_product_id_unique` đã gỡ ở migration
 * 2026_08_23_230000: index này có từ thời hệ thống chỉ một kho. Sang mô hình
 * nhiều dự án thì mỗi dự án có danh mục vật tư riêng, nên hai dự án hoàn toàn
 * có thể dùng chung một mã vật tư. Ràng buộc đúng là (house_id, code) — đã có
 * sẵn là `products_house_code_unique`.
 *
 * Còn giữ index cũ thì nhập kho vào dự án thứ hai với mã vật tư đã tồn tại ở
 * dự án khác sẽ vỡ: 1062 Duplicate entry 'VAP00205' for key 'products_code_unique'.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('products')) {
            return;
        }

        // Đảm bảo ràng buộc đúng đã tồn tại trước khi gỡ ràng buộc cũ
        if (!$this->indexExists('products', 'products_house_code_unique')) {
            Schema::table('products', function ($table) {
                $table->unique(['house_id', 'code'], 'products_house_code_unique');
            });
        }

        if ($this->indexExists('products', 'products_code_unique')) {
            DB::statement('ALTER TABLE `products` DROP INDEX `products_code_unique`');
        }

        // Giữ index thường trên code để tra cứu theo mã vẫn nhanh
        if (!$this->indexExists('products', 'products_code_index')) {
            Schema::table('products', function ($table) {
                $table->index('code', 'products_code_index');
            });
        }
    }

    public function down(): void
    {
        // Cố ý không dựng lại: index cũ phá vỡ mô hình nhiều dự án.
    }

    private function indexExists(string $table, string $index): bool
    {
        return count(DB::select(
            'SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?', [$index]
        )) > 0;
    }
};

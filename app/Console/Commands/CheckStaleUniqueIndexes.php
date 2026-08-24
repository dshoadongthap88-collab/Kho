<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Tìm các index UNIQUE cũ còn sót từ thời hệ thống một kho.
 *
 * Bảng nào có cột house_id mà lại có ràng buộc unique KHÔNG kèm house_id thì
 * hai dự án không thể dùng chung một mã — nhập liệu ở dự án thứ hai sẽ vỡ với
 * lỗi 1062 Duplicate entry. Đã gặp hai lần: inventories_product_id_unique và
 * products_code_unique.
 *
 *   php artisan db:check-unique          # chỉ liệt kê
 *   php artisan db:check-unique --fix    # gỡ luôn (chỉ khi đã có ràng buộc ghép)
 */
class CheckStaleUniqueIndexes extends Command
{
    protected $signature = 'db:check-unique {--fix : Gỡ index cũ, chỉ gỡ khi đã có ràng buộc ghép với house_id}';

    protected $description = 'Tìm index unique cũ không kèm house_id, gây lỗi 1062 khi dùng nhiều dự án';

    public function handle(): int
    {
        $database = DB::connection()->getDatabaseName();

        $tables = DB::select(
            'SELECT TABLE_NAME AS t FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = ? AND COLUMN_NAME = ?',
            [$database, 'house_id']
        );

        $found = [];

        foreach ($tables as $row) {
            $table = $row->t;

            // Gom các cột của từng index unique
            $indexes = [];
            foreach (DB::select("SHOW INDEX FROM `{$table}` WHERE Non_unique = 0") as $i) {
                $indexes[$i->Key_name][] = $i->Column_name;
            }

            $hasComposite = false;
            foreach ($indexes as $cols) {
                if (in_array('house_id', $cols, true)) {
                    $hasComposite = true;
                    break;
                }
            }

            foreach ($indexes as $name => $cols) {
                if ($name === 'PRIMARY' || in_array('house_id', $cols, true)) {
                    continue;
                }

                $found[] = [
                    'table'     => $table,
                    'index'     => $name,
                    'columns'   => implode(', ', $cols),
                    'composite' => $hasComposite,
                ];
            }
        }

        if (empty($found)) {
            $this->info('Không còn index unique cũ nào. Các bảng nhiều dự án đều dùng ràng buộc kèm house_id.');
            return self::SUCCESS;
        }

        $this->warn(sprintf('Tìm thấy %d index unique KHÔNG kèm house_id:', count($found)));
        $this->table(
            ['Bảng', 'Index', 'Cột', 'Đã có ràng buộc ghép?'],
            array_map(fn ($f) => [
                $f['table'], $f['index'], $f['columns'],
                $f['composite'] ? 'có — gỡ được' : 'CHƯA — không nên gỡ',
            ], $found)
        );

        if (!$this->option('fix')) {
            $this->newLine();
            $this->info('Đây là chạy thử. Thêm --fix để gỡ những index đã có ràng buộc ghép.');
            return self::SUCCESS;
        }

        $dropped = 0;
        foreach ($found as $f) {
            if (!$f['composite']) {
                $this->warn(sprintf('  Bỏ qua %s.%s — chưa có ràng buộc ghép với house_id, gỡ sẽ mất kiểm soát trùng lặp.',
                    $f['table'], $f['index']));
                continue;
            }

            DB::statement(sprintf('ALTER TABLE `%s` DROP INDEX `%s`', $f['table'], $f['index']));
            $this->line(sprintf('  Đã gỡ %s.%s', $f['table'], $f['index']));
            $dropped++;
        }

        $this->newLine();
        $this->info(sprintf('Đã gỡ %d index.', $dropped));

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Rà soát toàn hệ thống các lỗi phân tách dự án (multi-tenant).
 *
 * Đã gặp hai lần cùng một loại lỗi: inventories_product_id_unique rồi
 * products_code_unique. Thay vì đợi nổ tiếp rồi vá từng cái, lệnh này kiểm
 * ba lớp rủi ro cùng lúc:
 *
 *   1. RÀNG BUỘC: bảng có house_id nhưng unique lại không kèm house_id
 *      -> dự án thứ hai không dùng được cùng mã, nổ 1062.
 *
 *   2. DỮ LIỆU MỒ CÔI: bản ghi có house_id = NULL
 *      -> không thuộc dự án nào, không hiện ở màn hình nào.
 *
 *   3. TRUY VẤN BỎ LỌC: code gọi withoutGlobalScope('house')
 *      -> có thể đọc/ghi nhầm sang dữ liệu dự án khác.
 *
 *     php artisan audit:multi-project
 */
class AuditMultiProject extends Command
{
    protected $signature = 'audit:multi-project {--verbose-sql : In thêm câu lệnh SQL gợi ý}';

    protected $description = 'Rà soát ràng buộc, dữ liệu mồ côi và truy vấn bỏ lọc dự án';

    public function handle(): int
    {
        $database = DB::connection()->getDatabaseName();

        $tables = collect(DB::select(
            'SELECT TABLE_NAME AS t FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = ? AND COLUMN_NAME = ? ORDER BY TABLE_NAME',
            [$database, 'house_id']
        ))->pluck('t')->all();

        $this->info(sprintf('Có %d bảng phân tách theo dự án.', count($tables)));

        $this->phanMotRangBuoc($tables);
        $this->phanHaiDuLieuMoCoi($tables);
        $this->phanBaTruyVanBoLoc();

        return self::SUCCESS;
    }

    /** 1. Ràng buộc unique thiếu house_id */
    private function phanMotRangBuoc(array $tables): void
    {
        $this->newLine();
        $this->line('<fg=cyan>[1] RÀNG BUỘC UNIQUE THIẾU house_id</>');

        $rows = [];

        foreach ($tables as $table) {
            $indexes = [];
            foreach (DB::select("SHOW INDEX FROM `{$table}` WHERE Non_unique = 0") as $i) {
                $indexes[$i->Key_name][] = $i->Column_name;
            }

            $hasComposite = collect($indexes)->contains(fn ($c) => in_array('house_id', $c, true));

            foreach ($indexes as $name => $cols) {
                if ($name === 'PRIMARY' || in_array('house_id', $cols, true)) {
                    continue;
                }

                // Mã sinh tự động toàn hệ thống thì unique toàn cục vẫn đúng.
                // Chỉ cảnh báo cao khi cột do người dùng nhập.
                $risk = $this->coTrungThucTe($table, $cols);

                $rows[] = [
                    $table,
                    implode(', ', $cols),
                    $hasComposite ? 'có' : 'CHƯA',
                    $risk === null ? '?' : ($risk > 0 ? "ĐANG TRÙNG ({$risk})" : 'chưa trùng'),
                ];
            }
        }

        if (empty($rows)) {
            $this->line('    Không có. Mọi ràng buộc unique đều kèm house_id.');
            return;
        }

        $this->table(['Bảng', 'Cột unique', 'Có ràng buộc ghép?', 'Dữ liệu thực tế'], $rows);
        $this->line('    Cột "Dữ liệu thực tế" đếm số giá trị đang tồn tại ở nhiều dự án.');
        $this->line('    Đang trùng = phải xử lý ngay. Chưa trùng = chưa gấp nhưng vẫn là bom hẹn giờ.');
    }

    /** Đếm giá trị xuất hiện ở nhiều dự án */
    private function coTrungThucTe(string $table, array $cols): ?int
    {
        try {
            return DB::table($table)
                ->select($cols)
                ->groupBy($cols)
                ->havingRaw('COUNT(DISTINCT house_id) > 1')
                ->get()
                ->count();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** 2. Bản ghi mồ côi */
    private function phanHaiDuLieuMoCoi(array $tables): void
    {
        $this->newLine();
        $this->line('<fg=cyan>[2] BẢN GHI KHÔNG THUỘC DỰ ÁN NÀO (house_id = NULL)</>');

        // Bảng tham chiếu: house_id = NULL nghĩa là "dùng chung mọi dự án",
        // không phải mồ côi. Xem chú thích trong trait BelongsToHouse.
        $dungChung = ['categories', 'suppliers'];

        $rows = [];
        $chung = [];

        foreach ($tables as $table) {
            try {
                $orphan = DB::table($table)->whereNull('house_id')->count();
                if ($orphan === 0) {
                    continue;
                }

                $tong = DB::table($table)->count();

                if (in_array($table, $dungChung, true)) {
                    $chung[] = [$table, number_format($orphan), number_format($tong)];
                } else {
                    $rows[] = [$table, number_format($orphan), number_format($tong)];
                }
            } catch (\Throwable $e) {
                // bảng có thể có global scope hoặc soft delete, bỏ qua
            }
        }

        if (empty($rows)) {
            $this->line('    Không có bản ghi mồ côi.');
        } else {
            $this->table(['Bảng', 'Mồ côi', 'Tổng'], $rows);
            $this->line('    Các bản ghi này không hiện ở màn hình nào vì bộ lọc dự án loại chúng ra.');
            $this->line('    Chạy: php artisan db:fix-orphans  để gán dự án suy ra từ quan hệ.');
        }

        if (!empty($chung)) {
            $this->newLine();
            $this->line('    <fg=green>Dữ liệu tham chiếu dùng chung (bình thường, không phải lỗi):</>');
            $this->table(['Bảng', 'Dùng chung', 'Tổng'], $chung);
        }
    }

    /** 3. Chỗ code bỏ lọc dự án */
    private function phanBaTruyVanBoLoc(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>[3] CODE BỎ LỌC DỰ ÁN (withoutGlobalScope)</>');

        // Màn hình HR cố ý xem xuyên dự án (báo cáo tổng hợp, trung tâm mua sắm)
        $coYXuyenDuAn = ['Livewire/Hr/', 'Scopes/ProjectScope.php', 'Console/Commands/AuditMultiProject.php'];

        $hits = [];
        $dir = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path()));

        foreach ($dir as $file) {
            if (!str_ends_with($file->getFilename(), '.php')) {
                continue;
            }

            $rel = str_replace([app_path() . DIRECTORY_SEPARATOR, '\\'], ['', '/'], $file->getPathname());
            $lines = file($file->getPathname());

            foreach ($lines as $no => $line) {
                if (!str_contains($line, 'withoutGlobalScope')) {
                    continue;
                }

                // Bỏ qua dòng chú thích — không phải câu truy vấn
                $t = ltrim($line);
                if (str_starts_with($t, '*') || str_starts_with($t, '//') || str_starts_with($t, '/*')) {
                    continue;
                }

                foreach ($coYXuyenDuAn as $prefix) {
                    if (str_starts_with($rel, $prefix)) {
                        continue 2;
                    }
                }

                // Câu truy vấn trải nhiều dòng, và lý do bỏ lọc thường được ghi
                // ở chú thích ngay phía trên — nhìn cả 3 dòng trước lẫn 6 dòng sau.
                $window = implode('', array_slice($lines, max(0, $no - 3), 10));
                $daLoc = str_contains($window, 'house_id')
                    || str_contains($window, 'currentHouseId')
                    || str_contains($window, 'An toan khi bo loc')
                    || str_contains($window, 'Cung ly do');

                $hits[] = [
                    $rel,
                    $no + 1,
                    $daLoc ? 'đã lọc lại' : 'CẦN XEM',
                    trim(mb_substr(trim($line), 0, 58)),
                ];
            }
        }

        if (empty($hits)) {
            $this->line('    Không còn chỗ nào bỏ lọc dự án mà chưa kiểm soát.');
            $this->line('    (Đã loại các màn hình HR cố ý xem xuyên dự án.)');
            return;
        }

        $canXem = array_filter($hits, fn ($h) => $h[2] === 'CẦN XEM');

        $this->table(['File', 'Dòng', 'Đánh giá', 'Nội dung'], $hits);
        $this->line(sprintf('    %d chỗ bỏ lọc (đã loại màn HR cố ý xuyên dự án), %d chỗ cần xem lại.',
            count($hits), count($canXem)));
    }
}

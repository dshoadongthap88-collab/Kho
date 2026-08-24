<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Gán dự án cho các bản ghi mồ côi (house_id = NULL).
 *
 * Không đoán: mỗi bảng đều suy ra dự án từ quan hệ với một bảng ĐÃ có
 * house_id — ví dụ dòng tồn kho lấy theo dự án của vật tư tương ứng. Bản ghi
 * nào không suy ra được thì để nguyên và báo lại, chứ không gán bừa.
 *
 *   php artisan db:fix-orphans           # chỉ liệt kê
 *   php artisan db:fix-orphans --apply   # ghi thật
 */
class FixOrphanHouseId extends Command
{
    protected $signature = 'db:fix-orphans {--apply : Ghi thật vào CSDL}';

    protected $description = 'Gán house_id cho bản ghi mồ côi, suy ra từ quan hệ';

    /** [bảng con, khoá ngoại, bảng cha, mô tả] */
    private const QUAN_HE = [
        ['inventories',           'product_id',           'products',         'Dòng tồn kho lấy theo dự án của vật tư'],
        ['purchase_plans',        'product_id',           'products',         'Kế hoạch mua lấy theo dự án của vật tư'],
        ['maintenance_boms',      'asset_id',             'assets',           'Định mức bảo trì lấy theo dự án của thiết bị'],
        ['asset_daily_odos',      'asset_id',             'assets',           'Nhật ký ODO lấy theo dự án của thiết bị'],
        ['asset_odo_readings',    'asset_id',             'assets',           'Chỉ số ODO lấy theo dự án của thiết bị'],
        ['maintenance_bom_items', 'maintenance_bom_id',   'maintenance_boms', 'Dòng định mức lấy theo dự án của định mức'],
        ['stock_count_items',     'stock_count_id',       'stock_counts',     'Dòng kiểm kê lấy theo dự án của phiếu kiểm kê'],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $rows = [];
        $tongSua = 0;
        $tongKhongSuyRa = 0;

        foreach (self::QUAN_HE as [$con, $khoa, $cha, $moTa]) {
            if (!$this->coBang($con) || !$this->coBang($cha) || !$this->coCot($con, $khoa)) {
                continue;
            }

            // Đếm theo dự án suy ra được
            $suyRa = DB::table($con . ' as c')
                ->join($cha . ' as p', 'p.id', '=', 'c.' . $khoa)
                ->whereNull('c.house_id')
                ->whereNotNull('p.house_id')
                ->count();

            $khongSuyRa = DB::table($con . ' as c')
                ->leftJoin($cha . ' as p', 'p.id', '=', 'c.' . $khoa)
                ->whereNull('c.house_id')
                ->whereNull('p.house_id')
                ->count();

            if ($suyRa === 0 && $khongSuyRa === 0) {
                continue;
            }

            $rows[] = [$con, $moTa, number_format($suyRa), number_format($khongSuyRa)];
            $tongSua += $suyRa;
            $tongKhongSuyRa += $khongSuyRa;

            if ($apply && $suyRa > 0) {
                DB::statement(sprintf(
                    'UPDATE `%s` c JOIN `%s` p ON p.id = c.`%s`
                        SET c.house_id = p.house_id
                      WHERE c.house_id IS NULL AND p.house_id IS NOT NULL',
                    $con, $cha, $khoa
                ));
            }
        }

        if (empty($rows)) {
            $this->info('Không có bản ghi mồ côi nào suy ra được dự án.');
            return self::SUCCESS;
        }

        $this->table(['Bảng', 'Cách suy ra', 'Gán được', 'Không suy ra được'], $rows);

        if ($apply) {
            $this->info(sprintf('Đã gán dự án cho %s bản ghi.', number_format($tongSua)));
        } else {
            $this->newLine();
            $this->info(sprintf('Chạy thử — sẽ gán được %s bản ghi. Thêm --apply để ghi thật.',
                number_format($tongSua)));
        }

        if ($tongKhongSuyRa > 0) {
            $this->newLine();
            $this->warn(sprintf(
                '%s bản ghi KHÔNG suy ra được vì bản ghi cha cũng mồ côi. Chạy lại lệnh này lần nữa sau khi gán xong lượt đầu — dây chuyền quan hệ sẽ tự nối tiếp.',
                number_format($tongKhongSuyRa)
            ));
        }

        // Các bảng không có quan hệ để suy ra
        $this->newLine();
        $this->line('<fg=cyan>Bảng phải tự quyết định, lệnh này không đụng tới:</>');
        foreach (['suppliers', 'categories', 'stock_ins', 'stock_outs', 'stock_transfers', 'warehouses'] as $t) {
            if (!$this->coBang($t) || !$this->coCot($t, 'house_id')) {
                continue;
            }
            $n = DB::table($t)->whereNull('house_id')->count();
            if ($n > 0) {
                $this->line(sprintf('   %-18s %d bản ghi — không có quan hệ nào để suy ra dự án', $t, $n));
            }
        }

        return self::SUCCESS;
    }

    private function coBang(string $t): bool
    {
        return \Illuminate\Support\Facades\Schema::hasTable($t);
    }

    private function coCot(string $t, string $c): bool
    {
        return \Illuminate\Support\Facades\Schema::hasColumn($t, $c);
    }
}

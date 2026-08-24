<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Xử lý nốt các bản ghi mồ côi mà db:fix-orphans không suy ra được.
 *
 * Ba nhóm, ba cách xử lý khác nhau vì bản chất khác nhau:
 *
 *   A. Kế hoạch mua trỏ tới vật tư đã bị xoá  -> XOÁ
 *      product_id 1..41 nhưng bảng products bắt đầu từ id 42, tức toàn bộ
 *      vật tư cũ đã bị xoá sạch. Các kế hoạch này không thể hiển thị (không
 *      biết mua vật tư gì) và không thể thực hiện.
 *
 *   B. Danh mục nhóm + nhà cung cấp  -> DÙNG CHUNG mọi dự án
 *      Đây là dữ liệu tham chiếu, không phải chứng từ. Gán về một dự án sẽ
 *      làm các dự án khác mất luôn danh sách. Để house_id = NULL và sửa
 *      global scope cho phép đọc bản ghi dùng chung.
 *
 *   C. Chứng từ mồ côi (phiếu nhập/xuất/chuyển kho)  -> gán về dự án gốc
 *      Đều tạo cuối tháng 6, thời điểm hệ thống mới chỉ có Hóc Môn.
 *
 *     php artisan db:clean-orphans
 *     php artisan db:clean-orphans --apply
 */
class CleanOrphanData extends Command
{
    protected $signature = 'db:clean-orphans
                            {--apply : Ghi thật vào CSDL}
                            {--house=1 : Dự án gán cho chứng từ mồ côi}';

    protected $description = 'Xử lý bản ghi mồ côi còn lại: xoá kế hoạch hỏng, gán chứng từ về dự án gốc';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $house = (int) $this->option('house');

        $this->nhomA($apply);
        $this->nhomB();
        $this->nhomC($apply, $house);

        if (!$apply) {
            $this->newLine();
            $this->info('Đây là chạy thử, chưa ghi gì. Thêm --apply để thực hiện.');
        }

        return self::SUCCESS;
    }

    /** A. Kế hoạch mua trỏ tới vật tư đã xoá */
    private function nhomA(bool $apply): void
    {
        $this->newLine();
        $this->line('<fg=cyan>[A] Kế hoạch mua trỏ tới vật tư đã bị xoá</>');

        $hong = DB::table('purchase_plans as pp')
            ->leftJoin('products as p', 'p.id', '=', 'pp.product_id')
            ->whereNull('pp.house_id')
            ->whereNull('p.id');

        $tong = (clone $hong)->count();

        if ($tong === 0) {
            $this->line('    Không có.');
            return;
        }

        // Chỉ xoá cái chưa giao gì — đã giao thì là dữ liệu có thật, phải giữ
        $daGiao = (clone $hong)->where('pp.delivered_quantity', '>', 0)->count();
        $xoaDuoc = $tong - $daGiao;

        $this->line(sprintf('    %d bản ghi, trong đó %d đã giao một phần.', $tong, $daGiao));
        $this->line(sprintf('    Sẽ xoá %d bản ghi chưa giao gì.', $xoaDuoc));

        if ($daGiao > 0) {
            $this->warn(sprintf('    Giữ lại %d bản ghi đã giao — có phát sinh thật, cần xem thủ công.', $daGiao));
        }

        if ($apply && $xoaDuoc > 0) {
            $ids = (clone $hong)->where('pp.delivered_quantity', '<=', 0)->pluck('pp.id');
            DB::table('purchase_plans')->whereIn('id', $ids)->delete();
            $this->info(sprintf('    Đã xoá %d bản ghi.', count($ids)));
        }
    }

    /** B. Dữ liệu tham chiếu dùng chung */
    private function nhomB(): void
    {
        $this->newLine();
        $this->line('<fg=cyan>[B] Danh mục nhóm và nhà cung cấp — dùng chung</>');

        foreach (['categories' => 'nhóm vật tư', 'suppliers' => 'nhà cung cấp'] as $t => $ten) {
            if (!Schema::hasTable($t)) {
                continue;
            }
            $chung = DB::table($t)->whereNull('house_id')->count();
            $rieng = DB::table($t)->whereNotNull('house_id')->count();
            $this->line(sprintf('    %-12s %d dùng chung, %d riêng dự án', $ten, $chung, $rieng));
        }

        $this->line('    Giữ house_id = NULL. Đây là dữ liệu tham chiếu, gán về một dự án');
        $this->line('    sẽ làm các dự án khác mất sạch danh sách chọn.');
        $this->line('    Trait BelongsToHouse đã được sửa để đọc được bản ghi dùng chung.');
    }

    /** C. Chứng từ mồ côi */
    private function nhomC(bool $apply, int $house): void
    {
        $this->newLine();
        $this->line(sprintf('<fg=cyan>[C] Chứng từ mồ côi — gán về dự án %d</>', $house));

        $ten = DB::table('projects')->where('id', $house)->value('name');
        if (!$ten) {
            $this->error(sprintf('    Không có dự án id=%d.', $house));
            return;
        }
        $this->line(sprintf('    Dự án đích: %s', $ten));

        $bang = ['stock_ins', 'stock_outs', 'stock_transfers', 'stock_counts', 'delivery_reports'];
        $tong = 0;

        foreach ($bang as $t) {
            if (!Schema::hasTable($t) || !Schema::hasColumn($t, 'house_id')) {
                continue;
            }

            $n = DB::table($t)->whereNull('house_id')->count();
            if ($n === 0) {
                continue;
            }

            $khoang = DB::table($t)->whereNull('house_id')
                ->selectRaw('MIN(created_at) tu, MAX(created_at) den')->first();

            $this->line(sprintf('    %-18s %d bản ghi  (%s → %s)',
                $t, $n, substr((string) $khoang->tu, 0, 10), substr((string) $khoang->den, 0, 10)));
            $tong += $n;

            if ($apply) {
                DB::table($t)->whereNull('house_id')->update(['house_id' => $house]);
            }
        }

        if ($tong === 0) {
            $this->line('    Không có.');
            return;
        }

        if ($apply) {
            $this->info(sprintf('    Đã gán %d chứng từ về dự án %s.', $tong, $ten));
        }
    }
}

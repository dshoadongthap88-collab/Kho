<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurgeOldData extends Command
{
    /**
     * Xóa data quá 6 tháng theo chính sách lưu trữ hệ thống.
     * Chỉ xóa dữ liệu giao dịch (log, lịch sử), KHÔNG xóa master data (products, suppliers, users).
     */
    protected $signature = 'system:purge-old-data
                            {--months=6 : Số tháng lưu trữ (mặc định 6)}
                            {--dry-run  : Chỉ đếm, không xóa thực sự}
                            {--force    : Xóa mà không hỏi xác nhận}';

    protected $description = 'Xóa dữ liệu giao dịch cũ hơn 6 tháng để tiết kiệm dung lượng';

    public function handle(): int
    {
        $months   = (int)$this->option('months');
        $dryRun   = $this->option('dry-run');
        $force    = $this->option('force');
        $cutoff   = Carbon::now()->subMonths($months)->startOfDay();

        $this->info("📦 Chính sách lưu trữ: xóa data trước ngày {$cutoff->format('d/m/Y')} (>{$months} tháng)");

        if ($dryRun) {
            $this->warn('🔍 CHẾ ĐỘ DRY-RUN — chỉ đếm, không xóa thực sự');
        }

        // Danh sách bảng cần xóa, cột ngày tham chiếu
        $tables = [
            ['table' => 'inventory_transactions', 'col' => 'created_at', 'label' => 'Giao dịch tồn kho'],
            ['table' => 'stock_ins',              'col' => 'created_at', 'label' => 'Phiếu nhập kho'],
            ['table' => 'stock_outs',             'col' => 'created_at', 'label' => 'Phiếu xuất kho'],
            ['table' => 'stock_transfers',        'col' => 'created_at', 'label' => 'Phiếu chuyển kho'],
            ['table' => 'stock_counts',           'col' => 'created_at', 'label' => 'Phiếu kiểm kê'],
            ['table' => 'delivery_reports',       'col' => 'delivered_at', 'label' => 'Báo cáo giao hàng'],
            ['table' => 'maintenance_tickets',    'col' => 'created_at', 'label' => 'Phiếu bảo dưỡng'],
            ['table' => 'maintenance_plans',      'col' => 'created_at', 'label' => 'Kế hoạch bảo dưỡng'],
            ['table' => 'asset_odo_readings',     'col' => 'created_at', 'label' => 'Đọc ODO tài sản'],
            ['table' => 'asset_daily_odos',       'col' => 'created_at', 'label' => 'ODO hàng ngày'],
            ['table' => 'chat_messages',          'col' => 'created_at', 'label' => 'Tin nhắn chat'],
            ['table' => 'notifications',          'col' => 'created_at', 'label' => 'Thông báo'],
        ];

        $totalDeleted = 0;

        $this->table(
            ['Bảng', 'Mô tả', 'Số dòng sẽ xóa'],
            collect($tables)->map(function ($t) use ($cutoff) {
                $count = DB::table($t['table'])
                    ->where($t['col'], '<', $cutoff)
                    ->count();
                return [$t['table'], $t['label'], number_format($count)];
            })->all()
        );

        if ($dryRun) {
            $this->info('✅ Dry-run hoàn tất. Dùng --force để xóa thực sự.');
            return Command::SUCCESS;
        }

        if (!$force && !$this->confirm("Xác nhận xóa vĩnh viễn dữ liệu trên {$months} tháng tuổi?")) {
            $this->info('Đã hủy.');
            return Command::SUCCESS;
        }

        $this->info('🗑️  Đang xóa...');
        $bar = $this->output->createProgressBar(count($tables));
        $bar->start();

        DB::transaction(function () use ($tables, $cutoff, &$totalDeleted, $bar) {
            foreach ($tables as $t) {
                // Xóa child records trước khi xóa parent để tránh FK violation
                if ($t['table'] === 'stock_ins') {
                    $ids = DB::table('stock_ins')->where('created_at', '<', $cutoff)->pluck('id');
                    if ($ids->isNotEmpty()) {
                        DB::table('stock_in_items')->whereIn('stock_in_id', $ids)->delete();
                    }
                }
                if ($t['table'] === 'stock_outs') {
                    $ids = DB::table('stock_outs')->where('created_at', '<', $cutoff)->pluck('id');
                    if ($ids->isNotEmpty()) {
                        DB::table('stock_out_items')->whereIn('stock_out_id', $ids)->delete();
                        DB::table('delivery_reports')->whereIn('stock_out_id', $ids)->delete();
                    }
                }
                if ($t['table'] === 'stock_counts') {
                    $ids = DB::table('stock_counts')->where('created_at', '<', $cutoff)->pluck('id');
                    if ($ids->isNotEmpty()) {
                        DB::table('stock_count_items')->whereIn('stock_count_id', $ids)->delete();
                    }
                }
                if ($t['table'] === 'stock_transfers') {
                    $ids = DB::table('stock_transfers')->where('created_at', '<', $cutoff)->pluck('id');
                    if ($ids->isNotEmpty()) {
                        DB::table('stock_transfer_items')->whereIn('stock_transfer_id', $ids)->delete();
                    }
                }
                if ($t['table'] === 'maintenance_tickets') {
                    $ids = DB::table('maintenance_tickets')->where('created_at', '<', $cutoff)->pluck('id');
                    if ($ids->isNotEmpty()) {
                        DB::table('maintenance_items')->whereIn('maintenance_ticket_id', $ids)->delete();
                    }
                }

                // Bỏ qua delivery_reports nếu đã xóa ở bước stock_outs
                if ($t['table'] === 'delivery_reports') {
                    $deleted = DB::table('delivery_reports')
                        ->where($t['col'], '<', $cutoff)
                        ->delete();
                } else {
                    $deleted = DB::table($t['table'])
                        ->where($t['col'], '<', $cutoff)
                        ->delete();
                }

                $totalDeleted += $deleted;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();
        $this->info("✅ Xóa xong — tổng {$totalDeleted} dòng đã được dọn dẹp.");

        // Log lại
        \Illuminate\Support\Facades\Log::info("PurgeOldData: xóa {$totalDeleted} dòng data cũ hơn {$months} tháng", [
            'cutoff'  => $cutoff->toDateTimeString(),
            'deleted' => $totalDeleted,
        ]);

        return Command::SUCCESS;
    }
}

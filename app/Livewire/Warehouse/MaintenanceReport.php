<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use App\Models\Asset;
use App\Models\MaintenanceRule;
use App\Models\MaintenanceTicket;
use Carbon\Carbon;

class MaintenanceReport extends Component
{
    public $month;
    public $year;

    public function mount()
    {
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function render()
    {
        // Thống kê số lượng thiết bị trạng thái (tương tự Tracking nhưng tính tổng)
        $assets = Asset::where('status', '!=', 'inactive')->get();
        $rules = MaintenanceRule::all();

        $totalAssets = $assets->count();
        $normalCount = 0;
        $warningCount = 0;
        $overdueCount = 0;
        
        $highPriorityAssets = [];

        // Tính trạng thái bảo dưỡng
        foreach ($assets as $asset) {
            $assetRules = $rules->where('machine_type', $asset->machine_type);
            $assetStatus = 'BÌNH THƯỜNG'; // Default
            $assetRemaining = PHP_INT_MAX;
            
            foreach ($assetRules as $rule) {
                $lastTicket = MaintenanceTicket::where('asset_id', $asset->id)
                    ->where('maintenance_rule_id', $rule->rule_code ?: $rule->category)
                    ->where('status', 'completed')
                    ->orderBy('maintenance_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                $lastHours = $lastTicket ? $lastTicket->maintenance_odo : 0; 
                $currentHours = $asset->current_hours ?? 0;
                $cycle = $rule->cycle_hours ?? 0;

                if ($cycle <= 0) continue;

                $targetHours = $lastHours + $cycle;
                $remainingHours = $targetHours - $currentHours;

                if ($remainingHours < 0) {
                    $assetStatus = 'ĐẾN HẠN';
                    $assetRemaining = min($assetRemaining, $remainingHours);
                } elseif ($remainingHours <= 50 && $assetStatus !== 'ĐẾN HẠN') {
                    $assetStatus = 'SẮP ĐẾN';
                    $assetRemaining = min($assetRemaining, $remainingHours);
                }
            }

            if ($assetStatus === 'ĐẾN HẠN') {
                $overdueCount++;
                $highPriorityAssets[] = [
                    'asset' => $asset,
                    'remaining' => $assetRemaining,
                ];
            } elseif ($assetStatus === 'SẮP ĐẾN') {
                $warningCount++;
            } else {
                $normalCount++;
            }
        }

        // Sắp xếp ưu tiên cao nhất
        usort($highPriorityAssets, function($a, $b) {
            return $a['remaining'] <=> $b['remaining'];
        });
        
        // Cắt lấy top 10
        $highPriorityAssets = array_slice($highPriorityAssets, 0, 10);

        // Số thiết bị đã bảo dưỡng trong tháng
        $ticketsThisMonth = MaintenanceTicket::whereMonth('maintenance_date', $this->month)
                                             ->whereYear('maintenance_date', $this->year)
                                             ->where('status', 'completed')
                                             ->get();
        $maintainedCount = $ticketsThisMonth->unique('asset_id')->count();

        // Tổng chi phí vật tư (nếu có nhập tay dạng số hoặc phân tích)
        // Hiện tại vật tư nhập dạng chuỗi, nên total_cost nếu chưa có thì gán 0
        $totalCost = $ticketsThisMonth->sum('total_cost') ?? 0;

        return view('livewire.warehouse.maintenance-report', [
            'totalAssets' => $totalAssets,
            'normalCount' => $normalCount,
            'warningCount' => $warningCount,
            'overdueCount' => $overdueCount,
            'maintainedCount' => $maintainedCount,
            'totalCost' => $totalCost,
            'highPriorityAssets' => $highPriorityAssets,
            'ticketsThisMonth' => $ticketsThisMonth
        ])->layout('components.warehouse-layout', ['title' => 'Báo Cáo Tổng Hợp Bảo Dưỡng']);
    }
}

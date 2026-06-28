<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\MaintenanceRule;
use App\Models\MaintenancePlan;
use App\Models\MaintenanceTicket;

class MaintenanceTracking extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = ''; // ALL, DEN_HAN, SAP_DEN, BINH_THUONG

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $assets = Asset::where('status', '!=', 'inactive')
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('asset_code', 'like', '%' . $this->search . '%')
                          ->orWhere('machine_type', 'like', '%' . $this->search . '%');
                });
            })
            ->get();

        $rules = MaintenanceRule::all();
        $trackingList = [];

        foreach ($assets as $asset) {
            $assetRules = $rules->where('machine_type', $asset->machine_type);
            foreach ($assetRules as $rule) {
                // Determine last maintenance hours for this rule
                $lastTicket = MaintenanceTicket::where('asset_id', $asset->id)
                    ->where('maintenance_rule_id', $rule->rule_code ?: $rule->category)
                    ->where('status', 'completed')
                    ->orderBy('maintenance_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();

                // If no completed ticket, assume 0 or initial value
                $lastHours = $lastTicket ? $lastTicket->maintenance_odo : 0; 
                $currentHours = $asset->current_hours ?? 0;
                $cycle = $rule->cycle_hours ?? 0;

                if ($cycle <= 0) continue; // Only track hours

                $targetHours = $lastHours + $cycle;
                $remainingHours = $targetHours - $currentHours;

                // Status rules
                if ($remainingHours < 0) {
                    $status = 'ĐẾN HẠN';
                    $warning = 'DỪNG MÁY BẢO DƯỠNG';
                    $priority = 'CAO';
                    $color = 'red';
                } elseif ($remainingHours <= 50) {
                    $status = 'SẮP ĐẾN';
                    $warning = 'Chuẩn bị vật tư + kế hoạch';
                    $priority = 'TRUNG BÌNH';
                    $color = 'yellow';
                } else {
                    $status = 'BÌNH THƯỜNG';
                    $warning = 'Theo dõi định kỳ';
                    $priority = 'THẤP';
                    $color = 'green';
                }

                if ($this->statusFilter && $this->statusFilter !== 'ALL') {
                    if ($this->statusFilter === 'DEN_HAN' && $status !== 'ĐẾN HẠN') continue;
                    if ($this->statusFilter === 'SAP_DEN' && $status !== 'SẮP ĐẾN') continue;
                    if ($this->statusFilter === 'BINH_THUONG' && $status !== 'BÌNH THƯỜNG') continue;
                }

                $trackingList[] = [
                    'asset_id' => $asset->id,
                    'asset_code' => $asset->asset_code,
                    'asset_name' => $asset->name,
                    'rule_name' => $rule->name ?? $rule->category,
                    'rule_code' => $rule->rule_code,
                    'cycle' => $cycle,
                    'last_hours' => $lastHours,
                    'current_hours' => $currentHours,
                    'target_hours' => $targetHours,
                    'remaining_hours' => $remainingHours,
                    'status' => $status,
                    'warning' => $warning,
                    'priority' => $priority,
                    'color' => $color,
                ];
            }
        }

        // Sort by priority (remaining hours ascending)
        usort($trackingList, function($a, $b) {
            return $a['remaining_hours'] <=> $b['remaining_hours'];
        });

        // Paginate array manually
        $page = $this->getPage();
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $items = array_slice($trackingList, $offset, $perPage);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items, count($trackingList), $perPage, $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('livewire.warehouse.maintenance-tracking', [
            'trackingList' => $paginator
        ])->layout('components.warehouse-layout', ['title' => 'Theo Dõi Bảo Dưỡng']);
    }
}

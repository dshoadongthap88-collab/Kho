<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\MaintenancePlan;

class MaintenanceDashboard extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function markAsCompleted($planId)
    {
        $plan = MaintenancePlan::findOrFail($planId);
        $plan->status = 'completed';
        $plan->save();

        session()->flash('message', 'Đã đánh dấu hoàn thành bảo dưỡng.');
    }

    public function render()
    {
        // Thống kê tổng quan
        $totalAssets = Asset::count();
        $activeAssets = Asset::where('status', 'active')->count();
        $maintenanceAssets = Asset::where('status', 'maintenance')->count();

        // Kế hoạch bảo dưỡng
        $query = MaintenancePlan::with('asset')
            ->whereHas('asset', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('asset_code', 'like', '%' . $this->search . '%');
            })
            ->where('status', '!=', 'completed')
            ->orderBy('id', 'desc');

        $plans = $query->paginate(15);

        // Tính toán cảnh báo trên collection của page hiện tại hoặc toàn cục tuỳ ý, ở đây tính trực tiếp trong view cho dễ

        $warningCount = MaintenancePlan::where('status', '!=', 'completed')
            ->whereHas('asset', function($q) {
                $q->whereRaw('assets.current_odo >= maintenance_plans.maintenance_odo - 50'); // Sắp đến hạn (ví dụ cách 50km/giờ)
                $q->whereRaw('assets.current_odo < maintenance_plans.maintenance_odo');
            })->count();

        $overdueCount = MaintenancePlan::where('status', '!=', 'completed')
            ->whereHas('asset', function($q) {
                $q->whereRaw('assets.current_odo >= maintenance_plans.maintenance_odo'); // Quá hạn
            })->count();

        return view('livewire.warehouse.maintenance-dashboard', [
            'plans' => $plans,
            'totalAssets' => $totalAssets,
            'activeAssets' => $activeAssets,
            'maintenanceAssets' => $maintenanceAssets,
            'warningCount' => $warningCount,
            'overdueCount' => $overdueCount,
        ])->layout('components.warehouse-layout', ['title' => 'Tổng Quan & Kế Hoạch Bảo Dưỡng']);
    }
}

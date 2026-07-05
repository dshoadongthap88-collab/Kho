<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;

class AssetMaintenanceErp extends Component
{
    public $activeTab = 'dashboard'; 
    
    // Available tabs:
    // 1. dashboard (asset-maintenance-dashboard)
    // 2. asset-manager (asset-manager)
    // 3. bom-manager (asset-bom-manager)
    // 4. ticket-list (ticket-list)
    // 5. odo-manager (daily-odo-manager)
    // 6. shift-log (shift-log-form)
    // 7. ticket-completion (ticket-completion-form)

    protected $queryString = ['activeTab'];

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        return view('livewire.warehouse.asset-maintenance-erp')
            ->layout('layouts.app', ['title' => 'Hệ thống Quản lý Thiết bị & Bảo dưỡng (ERP)']);
    }
}

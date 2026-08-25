<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\Attributes\Url;

class AssetMaintenanceErp extends Component
{
    #[Url(history: true)]
    public $activeTab = 'dashboard'; 
    
    // Available tabs:
    // 1. dashboard (asset-maintenance-dashboard)
    // 2. asset-manager (asset-manager)
    // 3. bom-manager (asset-bom-manager)
    // 4. ticket-list (ticket-list)
    // 5. odo-manager (daily-odo-manager)
    // 6. shift-log (shift-log-form)
    // 7. ticket-completion (ticket-completion-form)

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

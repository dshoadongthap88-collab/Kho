<?php

namespace App\Livewire\Maintenance;

use App\Models\Asset;
use Livewire\Component;

class AssetMaintenanceDashboard extends Component
{
    public function render()
    {
        $assets = Asset::all();
        
        $dashboardStats = [
            'total_assets' => $assets->count(),
            'total_odo' => $assets->sum('current_odo'),
            'due_250h' => 0,
            'due_500h' => 0,
            'overdue' => 0,
        ];

        $assetList = $assets->map(function ($asset) use (&$dashboardStats) {
            $cycle = $asset->maintenance_cycle_odo > 0 ? $asset->maintenance_cycle_odo : 250; // default 250
            
            $hoursRun = $asset->current_odo - $asset->last_maintenance_odo;
            $hoursRemaining = $cycle - $hoursRun;
            
            $isOverdue = $hoursRemaining <= 0;
            $isWarning = $hoursRemaining > 0 && $hoursRemaining <= 20;

            if ($isOverdue) {
                $dashboardStats['overdue']++;
            }
            if ($isWarning || $isOverdue) {
                if ($cycle == 250) $dashboardStats['due_250h']++;
                elseif ($cycle == 500) $dashboardStats['due_500h']++;
            }

            return [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'name' => $asset->name,
                'current_odo' => $asset->current_odo,
                'maintenance_cycle' => $cycle,
                'hours_remaining' => $hoursRemaining,
                'is_overdue' => $isOverdue,
                'is_warning' => $isWarning,
            ];
        })->sortBy('hours_remaining')->values();

        // Count pending tickets
        $pendingTickets = \App\Models\MaintenanceTicket::where('status', 'pending')->count();
        $completedTickets = \App\Models\MaintenanceTicket::where('status', 'completed')->count();
        
        $dashboardStats['pending_tickets'] = $pendingTickets;
        $dashboardStats['completed_tickets'] = $completedTickets;

        return view('components.maintenance.asset-maintenance-dashboard', [
            'assets' => $assetList,
            'stats' => $dashboardStats
        ]);
    }
}

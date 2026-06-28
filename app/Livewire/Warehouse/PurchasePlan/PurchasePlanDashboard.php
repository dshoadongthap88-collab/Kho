<?php

namespace App\Livewire\Warehouse\PurchasePlan;

use Livewire\Component;
use App\Models\PurchasePlan;

class PurchasePlanDashboard extends Component
{
    public function render()
    {
        $totalPlans = PurchasePlan::count();
        $completedPlans = PurchasePlan::where('status', 'completed')->count();
        $partialPlans = PurchasePlan::where('status', 'partial')->count();
        $unreceivedPlans = PurchasePlan::whereIn('status', ['pending', 'ordered', 'unreceived'])->count();
        
        $totalMissing = PurchasePlan::whereNotIn('status', ['completed'])
            ->selectRaw('SUM(proposed_quantity - delivered_quantity) as total_missing')
            ->value('total_missing') ?? 0;

        return view('livewire.warehouse.purchase-plan.purchase-plan-dashboard', [
            'totalPlans' => $totalPlans,
            'completedPlans' => $completedPlans,
            'partialPlans' => $partialPlans,
            'unreceivedPlans' => $unreceivedPlans,
            'totalMissing' => $totalMissing,
        ]);
    }
}

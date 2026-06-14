<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GlobalReport extends Component
{
    public function render()
    {
        $projects = Project::where('status', 'active')->get();
        $totalUsers = User::count();
        
        $stats = [];
        foreach ($projects as $project) {
            $stats[$project->id] = [
                'users' => User::whereJsonContains('allowed_houses', $project->id)->count(),
                // In a real app, query StockIn/Out where project_id = $project->id
                // Since this is just a stub for now, we'll put some random data
                'stock_value' => rand(10000000, 99999999),
                'active_orders' => rand(5, 50)
            ];
        }

        return view('livewire.hr.global-report', compact('projects', 'totalUsers', 'stats'))
            ->layout('layouts.app');
    }
}

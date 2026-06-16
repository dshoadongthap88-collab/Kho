<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use App\Models\User;
use App\Models\Project;
use Livewire\Attributes\Layout;

class HrDashboard extends Component
{
    #[Layout('layouts.app', ['title' => 'Trung tâm Điều khiển HR'])]
    public function render()
    {
        $stats = [
            'total_projects' => Project::count(),
            'total_users' => User::count(),
        ];
        
        return view('livewire.hr.hr-dashboard', compact('stats'));
    }
}

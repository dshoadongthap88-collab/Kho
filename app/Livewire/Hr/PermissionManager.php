<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use App\Models\User;
use App\Models\Project;
use Livewire\WithPagination;

class PermissionManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    
    public $userId;
    public $userName = '';
    public $selectedHouses = [];
    public $role = 'staff';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->userName = $user->name;
        $this->role = $user->role;
        // Ensure allowed_houses is an array
        $this->selectedHouses = is_array($user->allowed_houses) ? $user->allowed_houses : [];
        
        $this->showModal = true;
    }

    public function save()
    {
        $user = User::findOrFail($this->userId);
        
        // Ensure array is stored correctly (cast to integer)
        $houses = array_map('intval', $this->selectedHouses);
        
        $user->update([
            'role' => $this->role,
            'allowed_houses' => $houses,
        ]);

        session()->flash('success', 'Cập nhật phân quyền thành công!');
        
        $this->showModal = false;
    }

    public function render()
    {
        $users = User::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orWhere('phone', 'like', '%' . $this->search . '%')
            ->paginate(10);
            
        $projects = Project::where('status', 'active')->get();

        return view('livewire.hr.permission-manager', compact('users', 'projects'))
            ->layout('layouts.app');
    }
}

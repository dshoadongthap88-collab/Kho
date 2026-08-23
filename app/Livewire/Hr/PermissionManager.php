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
    public $filterHouse = ''; // Lọc nhân viên theo dự án/house
    public $showModal = false;
    
    public $userId;
    public $userName = '';
    public $selectedHouses = [];
    public $selectedPermissions = [];
    public $role = 'staff';

    public $availablePermissions = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterHouse()
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
        $this->selectedPermissions = is_array($user->permissions) ? $user->permissions : [];
        
        $this->showModal = true;
    }

    public function save()
    {
        $user = User::findOrFail($this->userId);
        
        $selectedHouses = is_array($this->selectedHouses) ? $this->selectedHouses : [];
        $houses = array_map('intval', $selectedHouses);
        
        $permissions = is_array($this->selectedPermissions) ? $this->selectedPermissions : [];
        
        $user->update([
            'role' => $this->role,
            'allowed_houses' => $houses,
            'permissions' => $permissions,
        ]);

        session()->flash('success', 'Cập nhật phân quyền thành công!');
        
        $this->showModal = false;
    }

    public function render()
    {
        $query = User::where(function($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('email', 'like', '%' . $this->search . '%')
              ->orWhere('phone', 'like', '%' . $this->search . '%')
              ->orWhere('code', 'like', '%' . $this->search . '%');
        });

        // Lọc nhân viên theo dự án được phép (allowed_houses chứa house_id)
        if ($this->filterHouse !== '') {
            $houseId = (string)$this->filterHouse;
            $query->where(function($q) use ($houseId) {
                $q->whereJsonContains('allowed_houses', (int)$houseId)
                  ->orWhereJsonContains('allowed_houses', $houseId);
            });
        }

        $users = $query->orderBy('name')->paginate(15);
        $projects = Project::where('status', 'active')->orderBy('name')->get();

        return view('livewire.hr.permission-manager', compact('users', 'projects'))
            ->layout('layouts.app');
    }
}

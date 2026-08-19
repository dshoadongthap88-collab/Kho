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
    public $selectedPermissions = [];
    public $role = 'staff';

    public $availablePermissions = [
        'warehouse.inventory' => 'Tồn kho (Xem)',
        'warehouse.stock-in' => 'Nhập kho',
        'warehouse.stock-out' => 'Xuất kho',
        'warehouse.stock-transfer.index' => 'Chuyển kho',
        'warehouse.stock-count' => 'Kiểm kê kho',
        'warehouse.product-catalog' => 'Danh mục vật tư',
        'warehouse.contacts' => 'Đối tác / NCC',
        'warehouse.asset-manager' => 'Quản lý Tài sản',
        'warehouse.maintenance-dashboard' => 'Báo cáo bảo trì',
        'warehouse.maintenance-tracking' => 'Theo dõi bảo trì',
        'warehouse.maintenance-rules' => 'Cấu hình bảo trì',
        'warehouse.maintenance-plans' => 'Kế hoạch bảo trì',
        'warehouse.maintenance-tickets' => 'Phiếu bảo trì',
        'warehouse.purchase-plan' => 'Kế hoạch mua sắm',
        'warehouse.purchase-plan.history' => 'Lịch sử mua sắm',
        'warehouse.reports.transaction-detail' => 'Báo cáo giao dịch',
        'warehouse.reports.stock' => 'Báo cáo kho tổng hợp',
        'warehouse.chat' => 'Nhắn tin nội bộ',
    ];

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
        $users = User::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orWhere('phone', 'like', '%' . $this->search . '%')
            ->paginate(10);
            
        $projects = Project::where('status', 'active')->get();

        return view('livewire.hr.permission-manager', compact('users', 'projects'))
            ->layout('layouts.app');
    }
}

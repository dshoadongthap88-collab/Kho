<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Warehouse;

class WarehouseManager extends Component
{
    use WithPagination;

    public $search = '';
    public $isModalOpen = false;

    // Form fields
    public $warehouseId;
    public $code;
    public $name;
    public $address;
    public $manager_name;
    public $phone;
    public $status = 'active';

    protected $rules = [
        'code' => 'required|string|max:50|unique:warehouses,code',
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:255',
        'manager_name' => 'nullable|string|max:100',
        'phone' => 'nullable|string|max:20',
        'status' => 'required|in:active,inactive,closed',
    ];

    public function mount()
    {
        // Permission check if needed
        // $this->authorize('manage_warehouses');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->warehouseId = null;
        $this->code = '';
        $this->name = '';
        $this->address = '';
        $this->manager_name = '';
        $this->phone = '';
        $this->status = 'active';
    }

    public function edit($id)
    {
        $this->resetValidation();
        $warehouse = Warehouse::findOrFail($id);
        
        $this->warehouseId = $warehouse->id;
        $this->code = $warehouse->code;
        $this->name = $warehouse->name;
        $this->address = $warehouse->address;
        $this->manager_name = $warehouse->manager_name;
        $this->phone = $warehouse->phone;
        $this->status = $warehouse->status;

        $this->isModalOpen = true;
    }

    public function save()
    {
        $rules = $this->rules;
        
        // Cập nhật lại rule unique khi edit
        if ($this->warehouseId) {
            $rules['code'] = 'required|string|max:50|unique:warehouses,code,' . $this->warehouseId;
        }

        $this->validate($rules);

        Warehouse::updateOrCreate(
            ['id' => $this->warehouseId],
            [
                'code' => $this->code,
                'name' => $this->name,
                'address' => $this->address,
                'manager_name' => $this->manager_name,
                'phone' => $this->phone,
                'status' => $this->status,
                'created_by' => auth()->id(),
            ]
        );

        $this->closeModal();
        session()->flash('message', $this->warehouseId ? 'Cập nhật kho thành công.' : 'Thêm mới kho thành công.');
    }

    public function delete($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();
        
        session()->flash('message', 'Đã xóa kho thành công.');
    }

    public function render()
    {
        $warehouses = Warehouse::where('code', 'like', '%' . $this->search . '%')
            ->orWhere('name', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.warehouse.warehouse-manager', [
            'warehouses' => $warehouses
        ])->layout('components.warehouse-layout', ['title' => 'Cấu Hình Kho']);
    }
}

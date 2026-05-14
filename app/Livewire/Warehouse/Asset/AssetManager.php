<?php

namespace App\Livewire\Warehouse\Asset;

use Livewire\Component;
use App\Models\Asset;
use Livewire\WithPagination;

class AssetManager extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedIds = [];
    public $selectAll = false;

    // Form state
    public $isFormOpen = false;
    public $isEditing = false;
    public $editingId = null;

    // Form fields
    public $asset_code;
    public $name;
    public $department;
    public $machine_type;
    public $model;
    public $serial_number;
    public $manufacturer;
    public $installation_date;
    public $status = 'active';

    protected $rules = [
        'asset_code' => 'required|unique:assets,asset_code',
        'name' => 'required|string|max:255',
        'status' => 'required|in:active,maintenance,inactive'
    ];

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedIds = Asset::pluck('id')->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function openForm()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->isFormOpen = true;
        $this->isEditing = false;
    }

    public function closeForm()
    {
        $this->isFormOpen = false;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $asset = Asset::findOrFail($id);
        $this->editingId = $asset->id;
        $this->asset_code = $asset->asset_code;
        $this->name = $asset->name;
        $this->department = $asset->department;
        $this->machine_type = $asset->machine_type;
        $this->model = $asset->model;
        $this->serial_number = $asset->serial_number;
        $this->manufacturer = $asset->manufacturer;
        $this->installation_date = $asset->installation_date;
        $this->status = $asset->status;
        
        $this->isFormOpen = true;
        $this->isEditing = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->isEditing) {
            $rules['asset_code'] = 'required|unique:assets,asset_code,' . $this->editingId;
        }

        $this->validate($rules);

        $data = [
            'asset_code' => $this->asset_code,
            'name' => $this->name,
            'department' => $this->department,
            'machine_type' => $this->machine_type,
            'model' => $this->model,
            'serial_number' => $this->serial_number,
            'manufacturer' => $this->manufacturer,
            'installation_date' => $this->installation_date ?: null,
            'status' => $this->status,
        ];

        if ($this->isEditing) {
            Asset::findOrFail($this->editingId)->update($data);
        } else {
            Asset::create($data);
        }

        $this->closeForm();
        session()->flash('message', 'Đã lưu thông tin tài sản thành công.');
    }

    public function delete($id)
    {
        Asset::findOrFail($id)->delete();
        $this->selectedIds = array_diff($this->selectedIds, [$id]);
        session()->flash('message', 'Đã xóa tài sản.');
    }

    public function deleteSelected()
    {
        Asset::whereIn('id', $this->selectedIds)->delete();
        $this->selectedIds = [];
        $this->selectAll = false;
        session()->flash('message', 'Đã xóa các tài sản được chọn.');
    }

    private function resetForm()
    {
        $this->editingId = null;
        $this->asset_code = '';
        $this->name = '';
        $this->department = '';
        $this->machine_type = '';
        $this->model = '';
        $this->serial_number = '';
        $this->manufacturer = '';
        $this->installation_date = '';
        $this->status = 'active';
    }

    public function render()
    {
        $assets = Asset::where('name', 'like', '%'.$this->search.'%')
            ->orWhere('asset_code', 'like', '%'.$this->search.'%')
            ->orderBy('id', 'desc')
            ->paginate(15);
        return view('livewire.warehouse.asset.asset-manager', compact('assets'));
    }
}

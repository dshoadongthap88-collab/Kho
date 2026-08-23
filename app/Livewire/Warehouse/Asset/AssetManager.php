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
    public $bomItems = [];
    public $machine_type;
    public $model;
    public $serial_number;
    public $manufacturer;
    public $license_plate;
    public $lifetime_odo;
    public $lifetime_hours;
    public $maintenance_cycle_hours;
    public $maintenance_cycle_odo;
    public $house_id;
    public $management_unit;
    public $installation_date;
    public $status = 'active';

    protected $rules = [
        'asset_code' => 'required|unique:assets,asset_code',
        'name' => 'required|string|max:255',
        'status' => 'required|in:active,maintenance,inactive',
        // Cac o so: de trong thi bo qua, nhap chu thi bao loi ngay tren form
        // thay vi de MySQL nem SQLSTATE[22007] ra trang loi.
        'lifetime_odo' => 'nullable|numeric|min:0',
        'lifetime_hours' => 'nullable|numeric|min:0',
        'maintenance_cycle_hours' => 'nullable|integer|min:0',
        'maintenance_cycle_odo' => 'nullable|integer|min:0',
    ];

    protected $messages = [
        'lifetime_odo.numeric' => 'ODO tich luy phai la so.',
        'lifetime_hours.numeric' => 'Gio tich luy phai la so.',
        'maintenance_cycle_hours.integer' => 'Chu ky bao duong theo gio phai la so nguyen.',
        'maintenance_cycle_odo.integer' => 'Chu ky bao duong theo ODO phai la so nguyen.',
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
        $this->bomItems = json_decode($asset->bom_details ?? '[]', true);
        $this->machine_type = $asset->machine_type;
        $this->model = $asset->model;
        $this->serial_number = $asset->serial_number;
        $this->manufacturer = $asset->manufacturer;
        $this->license_plate = $asset->license_plate;
        $this->lifetime_odo = $asset->lifetime_odo;
        $this->lifetime_hours = $asset->lifetime_hours;
        $this->maintenance_cycle_hours = $asset->maintenance_cycle_hours;
        $this->maintenance_cycle_odo = $asset->maintenance_cycle_odo;
        $this->house_id = $asset->house_id;
        $this->management_unit = $asset->management_unit;
        $this->installation_date = $asset->installation_date;
        $this->status = $asset->status;
        
        $this->isFormOpen = true;
        $this->isEditing = true;
    }

    public function addBomItem()
    {
        $this->bomItems[] = ['name' => '', 'quantity' => ''];
    }

    public function removeBomItem($index)
    {
        unset($this->bomItems[$index]);
        $this->bomItems = array_values($this->bomItems);
    }

    /**
     * Doi gia tri o nhap thanh so. O de trong (chuoi rong / null) tra ve gia tri
     * mac dinh: 0 cho cot NOT NULL, null cho cot cho phep NULL.
     */
    private function numberOrDefault($value, $default)
    {
        if ($value === null || trim((string) $value) === '') {
            return $default;
        }

        return is_numeric($value) ? $value + 0 : $default;
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
            'license_plate' => $this->license_plate,
            // Cot decimal NOT NULL: o trong tren form gui len chuoi rong, MySQL
            // che do strict tu choi -> quy ve 0.
            'lifetime_odo' => $this->numberOrDefault($this->lifetime_odo, 0),
            'lifetime_hours' => $this->numberOrDefault($this->lifetime_hours, 0),
            // Cot int cho phep NULL: chuoi rong cung bi tu choi -> quy ve null.
            'maintenance_cycle_hours' => $this->numberOrDefault($this->maintenance_cycle_hours, null),
            'maintenance_cycle_odo' => $this->numberOrDefault($this->maintenance_cycle_odo, null),
            'house_id' => $this->house_id ?: null,
            'management_unit' => $this->management_unit,
            'installation_date' => $this->installation_date ?: null,
            'status' => $this->status,
            'bom_details' => json_encode($this->bomItems),
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
        $this->license_plate = '';
        $this->lifetime_odo = '';
        $this->lifetime_hours = '';
        $this->maintenance_cycle_hours = '';
        $this->maintenance_cycle_odo = '';
        $this->house_id = null;
        $this->management_unit = '';
        $this->installation_date = '';
        $this->status = 'active';
    }

    public function render()
    {
        $assets = Asset::with('house')->where('name', 'like', '%'.$this->search.'%')
            ->orWhere('asset_code', 'like', '%'.$this->search.'%')
            ->orderBy('id', 'desc')
            ->paginate(15);
        $houses = \App\Models\House::all();
        return view('livewire.warehouse.asset.asset-manager', compact('assets', 'houses'));
    }
}

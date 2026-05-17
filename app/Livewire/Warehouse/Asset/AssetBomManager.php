<?php

namespace App\Livewire\Warehouse\Asset;

use Livewire\Component;
use App\Models\Asset;
use Livewire\WithPagination;

class AssetBomManager extends Component
{
    use WithPagination;

    public $search = '';
    
    // Arrays for bulk edit
    public $engine_oil_caps = [];
    public $hydraulic_oil_caps = [];
    public $engine_oil_filters = [];
    public $hydraulic_filters = [];
    public $air_filters = [];
    public $maintenance_cycles = [];

    // Fields for adding new asset
    public $isAddingAsset = false;
    public $new_asset_code = '';
    public $new_name = '';
    public $new_department = '';

    protected $queryString = ['search'];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->initFields();
    }

    public function initFields()
    {
        $assets = Asset::all();
        foreach ($assets as $asset) {
            $this->engine_oil_caps[$asset->id] = $asset->engine_oil_cap;
            $this->hydraulic_oil_caps[$asset->id] = $asset->hydraulic_oil_cap;
            $this->engine_oil_filters[$asset->id] = $asset->engine_oil_filter;
            $this->hydraulic_filters[$asset->id] = $asset->hydraulic_filter;
            $this->air_filters[$asset->id] = $asset->air_filter;
            $this->maintenance_cycles[$asset->id] = $asset->maintenance_cycle;
        }
    }

    public function saveBoms()
    {
        foreach ($this->engine_oil_caps as $id => $val) {
            $asset = Asset::find($id);
            if ($asset) {
                $asset->update([
                    'engine_oil_cap' => $this->engine_oil_caps[$id] ?? null,
                    'hydraulic_oil_cap' => $this->hydraulic_oil_caps[$id] ?? null,
                    'engine_oil_filter' => $this->engine_oil_filters[$id] ?? null,
                    'hydraulic_filter' => $this->hydraulic_filters[$id] ?? null,
                    'air_filter' => $this->air_filters[$id] ?? null,
                    'maintenance_cycle' => $this->maintenance_cycles[$id] ?? null,
                ]);
            }
        }
        
        session()->flash('message', 'Đã lưu tất cả định mức mã tài sản thành công.');
    }

    public function toggleAddAsset()
    {
        $this->isAddingAsset = !$this->isAddingAsset;
        $this->new_asset_code = '';
        $this->new_name = '';
        $this->new_department = '';
    }

    public function addAsset()
    {
        $this->validate([
            'new_asset_code' => 'required|string|unique:assets,asset_code',
            'new_name' => 'required|string|max:255',
            'new_department' => 'nullable|string|max:255',
        ], [
            'new_asset_code.required' => 'Mã tài sản không được để trống.',
            'new_asset_code.unique' => 'Mã tài sản này đã tồn tại.',
            'new_name.required' => 'Tên thiết bị không được để trống.',
        ]);

        $asset = Asset::create([
            'asset_code' => $this->new_asset_code,
            'name' => $this->new_name,
            'department' => $this->new_department,
            'status' => 'active'
        ]);

        $this->engine_oil_caps[$asset->id] = '';
        $this->hydraulic_oil_caps[$asset->id] = '';
        $this->engine_oil_filters[$asset->id] = '';
        $this->hydraulic_filters[$asset->id] = '';
        $this->air_filters[$asset->id] = '';
        $this->maintenance_cycles[$asset->id] = '';

        $this->isAddingAsset = false;
        session()->flash('message', 'Đã thêm thiết bị mới thành công.');
    }

    public function render()
    {
        $assetsQuery = Asset::query();

        if ($this->search) {
            $assetsQuery->where(function($q) {
                $q->where('asset_code', 'like', '%'.$this->search.'%')
                  ->orWhere('name', 'like', '%'.$this->search.'%')
                  ->orWhere('department', 'like', '%'.$this->search.'%');
            });
        }

        $assets = $assetsQuery->orderBy('asset_code', 'asc')->paginate(15);

        // Ensure newly paginated items are populated in form state arrays
        foreach ($assets as $asset) {
            if (!isset($this->engine_oil_caps[$asset->id])) {
                $this->engine_oil_caps[$asset->id] = $asset->engine_oil_cap;
                $this->hydraulic_oil_caps[$asset->id] = $asset->hydraulic_oil_cap;
                $this->engine_oil_filters[$asset->id] = $asset->engine_oil_filter;
                $this->hydraulic_filters[$asset->id] = $asset->hydraulic_filter;
                $this->air_filters[$asset->id] = $asset->air_filter;
                $this->maintenance_cycles[$asset->id] = $asset->maintenance_cycle;
            }
        }

        return view('livewire.warehouse.asset.asset-bom-manager', compact('assets'));
    }
}

<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenancePlan;
use App\Models\Asset;
use Illuminate\Support\Str;

class MaintenancePlanManager extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = ''; // all, pending, doing, completed
    public $isModalOpen = false;

    // Form fields
    public $planId;
    public $plan_code;
    public $asset_id;
    public $category;
    public $expected_date;
    public $current_odo = 0;
    public $maintenance_odo = 0;
    public $status = 'pending';
    public $assigned_to;

    protected $rules = [
        'plan_code' => 'required|string|max:100',
        'asset_id' => 'required|exists:assets,id',
        'category' => 'required|string|max:100',
        'expected_date' => 'nullable|date',
        'current_odo' => 'nullable|numeric|min:0',
        'maintenance_odo' => 'nullable|numeric|min:0',
        'status' => 'required|in:pending,doing,completed',
        'assigned_to' => 'nullable|string|max:100',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->plan_code = 'BD-' . date('YmdHis') . '-' . rand(10,99);
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
        $this->planId = null;
        $this->plan_code = '';
        $this->asset_id = null;
        $this->category = '';
        $this->expected_date = null;
        $this->current_odo = 0;
        $this->maintenance_odo = 0;
        $this->status = 'pending';
        $this->assigned_to = '';
    }

    public function edit($id)
    {
        $this->resetValidation();
        $plan = MaintenancePlan::findOrFail($id);
        
        $this->planId = $plan->id;
        $this->plan_code = $plan->plan_code;
        $this->asset_id = $plan->asset_id;
        $this->category = $plan->category;
        $this->expected_date = $plan->expected_date ? $plan->expected_date->format('Y-m-d') : null;
        $this->current_odo = $plan->current_odo;
        $this->maintenance_odo = $plan->maintenance_odo;
        $this->status = $plan->status;
        $this->assigned_to = $plan->assigned_to;

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();

        // Check plan code unique
        $exists = MaintenancePlan::where('plan_code', $this->plan_code)
                    ->where('id', '!=', $this->planId)
                    ->exists();
        if ($exists) {
            $this->addError('plan_code', 'Mã kế hoạch đã tồn tại.');
            return;
        }

        MaintenancePlan::updateOrCreate(
            ['id' => $this->planId],
            [
                'plan_code' => $this->plan_code,
                'asset_id' => $this->asset_id,
                'category' => $this->category,
                'expected_date' => $this->expected_date,
                'current_odo' => $this->current_odo,
                'maintenance_odo' => $this->maintenance_odo,
                'status' => $this->status,
                'assigned_to' => $this->assigned_to,
            ]
        );

        $this->closeModal();
        session()->flash('message', $this->planId ? 'Cập nhật kế hoạch thành công.' : 'Thêm kế hoạch thành công.');
    }

    public function delete($id)
    {
        MaintenancePlan::findOrFail($id)->delete();
        session()->flash('message', 'Đã xóa kế hoạch bảo dưỡng thành công.');
    }

    public function updateStatus($id, $newStatus)
    {
        $plan = MaintenancePlan::findOrFail($id);
        $plan->status = $newStatus;
        $plan->save();
        session()->flash('message', 'Đã cập nhật trạng thái.');
    }

    public function render()
    {
        $query = MaintenancePlan::with('asset')
            ->where(function($q) {
                $q->where('plan_code', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%')
                  ->orWhereHas('asset', function($aq) {
                      $aq->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('asset_code', 'like', '%' . $this->search . '%');
                  });
            });

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $plans = $query->orderBy('id', 'desc')->paginate(10);
        $assets = Asset::orderBy('name')->get();

        return view('livewire.warehouse.maintenance-plan-manager', [
            'plans' => $plans,
            'assets' => $assets
        ])->layout('components.warehouse-layout', ['title' => 'Kế Hoạch Bảo Dưỡng']);
    }
}

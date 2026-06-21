<?php

namespace App\Livewire\Warehouse;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MaintenanceRule;

class MaintenanceRuleManager extends Component
{
    use WithPagination;

    public $search = '';
    public $isModalOpen = false;

    // Form fields
    public $ruleId;
    public $machine_type;
    public $category;
    public $cycle_km = 0;
    public $cycle_hours = 0;
    public $cycle_months = 0;
    public $content;
    public $material_needed_raw = ''; // text nhập liệu cách nhau bởi dấu phẩy

    protected $rules = [
        'machine_type' => 'required|string|max:100',
        'category' => 'required|string|max:100',
        'cycle_km' => 'required|numeric|min:0',
        'cycle_hours' => 'required|numeric|min:0',
        'cycle_months' => 'required|integer|min:0',
        'content' => 'nullable|string',
    ];

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
        $this->ruleId = null;
        $this->machine_type = '';
        $this->category = '';
        $this->cycle_km = 0;
        $this->cycle_hours = 0;
        $this->cycle_months = 0;
        $this->content = '';
        $this->material_needed_raw = '';
    }

    public function edit($id)
    {
        $this->resetValidation();
        $rule = MaintenanceRule::findOrFail($id);
        
        $this->ruleId = $rule->id;
        $this->machine_type = $rule->machine_type;
        $this->category = $rule->category;
        $this->cycle_km = $rule->cycle_km;
        $this->cycle_hours = $rule->cycle_hours;
        $this->cycle_months = $rule->cycle_months;
        $this->content = $rule->content;
        $this->material_needed_raw = implode(', ', $rule->material_needed ?? []);

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();

        $materials = array_map('trim', explode(',', $this->material_needed_raw));
        $materials = array_filter($materials);

        MaintenanceRule::updateOrCreate(
            ['id' => $this->ruleId],
            [
                'machine_type' => $this->machine_type,
                'category' => $this->category,
                'cycle_km' => $this->cycle_km,
                'cycle_hours' => $this->cycle_hours,
                'cycle_months' => $this->cycle_months,
                'content' => $this->content,
                'material_needed' => array_values($materials),
                'created_by' => auth()->user()->name ?? 'System',
            ]
        );

        $this->closeModal();
        session()->flash('message', $this->ruleId ? 'Cập nhật định mức thành công.' : 'Thêm định mức thành công.');
    }

    public function delete($id)
    {
        MaintenanceRule::findOrFail($id)->delete();
        session()->flash('message', 'Đã xóa định mức thành công.');
    }

    public function render()
    {
        $rules = MaintenanceRule::where('machine_type', 'like', '%' . $this->search . '%')
            ->orWhere('category', 'like', '%' . $this->search . '%')
            ->orderBy('machine_type')
            ->paginate(10);

        return view('livewire.warehouse.maintenance-rule-manager', [
            'rules' => $rules
        ])->layout('components.warehouse-layout', ['title' => 'Định Mức Bảo Dưỡng']);
    }
}

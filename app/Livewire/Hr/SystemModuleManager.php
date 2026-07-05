<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SystemModule;

class SystemModuleManager extends Component
{
    use WithPagination;

    public $search = "";
    public $showModal = false;
    
    public $moduleId;
    public $group_name = "";
    public $route_name = "";
    public $label = "";
    public $is_active = true;

    protected $rules = [
        "group_name" => "required|string|max:255",
        "route_name" => "required|string|max:255",
        "label" => "required|string|max:255",
        "is_active" => "boolean",
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(["moduleId", "group_name", "route_name", "label", "is_active"]);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $module = SystemModule::findOrFail($id);
        $this->moduleId = $module->id;
        $this->group_name = $module->group_name;
        $this->route_name = $module->route_name;
        $this->label = $module->label;
        $this->is_active = $module->is_active;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        SystemModule::updateOrCreate(
            ["id" => $this->moduleId],
            [
                "group_name" => $this->group_name,
                "route_name" => $this->route_name,
                "label" => $this->label,
                "is_active" => $this->is_active,
            ]
        );

        session()->flash("message", "Đã lưu thông tin Module thành công!");
        $this->showModal = false;
    }

    public function toggleActive($id)
    {
        $module = SystemModule::findOrFail($id);
        $module->update(["is_active" => !$module->is_active]);
    }

    public function render()
    {
        $modules = SystemModule::where("group_name", "like", "%" . $this->search . "%")
            ->orWhere("label", "like", "%" . $this->search . "%")
            ->orWhere("route_name", "like", "%" . $this->search . "%")
            ->orderBy("group_name")
            ->orderBy("id")
            ->paginate(15);

        return view("livewire.hr.system-module-manager", compact("modules"))
            ->layout("layouts.app", ["title" => "Cấu hình Module"]);
    }
}

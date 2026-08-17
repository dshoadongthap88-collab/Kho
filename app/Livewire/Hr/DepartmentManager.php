<?php

namespace App\Livewire\Hr;

use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class DepartmentManager extends Component
{
    use WithPagination;

    public $search = '';
    public $isModalOpen = false;

    // Form fields
    public $departmentId;
    public $name;
    public $code;
    public $manager_name;
    public $description;
    public $status = 'active';

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'required|string|max:50|unique:departments,code',
        'manager_name' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'status' => 'required|in:active,inactive',
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
        $this->departmentId = null;
        $this->name = '';
        $this->code = '';
        $this->manager_name = '';
        $this->description = '';
        $this->status = 'active';
    }

    public function edit($id)
    {
        $this->resetValidation();
        $department = Department::findOrFail($id);
        
        $this->departmentId = $department->id;
        $this->name = $department->name;
        $this->code = $department->code;
        $this->manager_name = $department->manager_name;
        $this->description = $department->description;
        $this->status = $department->status;

        $this->isModalOpen = true;
    }

    public function save()
    {
        $rules = $this->rules;
        
        if ($this->departmentId) {
            $rules['code'] = 'required|string|max:50|unique:departments,code,' . $this->departmentId;
        }

        $this->validate($rules);

        Department::updateOrCreate(
            ['id' => $this->departmentId],
            [
                'name' => $this->name,
                'code' => $this->code,
                'manager_name' => $this->manager_name,
                'description' => $this->description,
                'status' => $this->status,
            ]
        );

        $this->closeModal();
        session()->flash('message', $this->departmentId ? 'Cập nhật phòng ban thành công.' : 'Thêm mới phòng ban thành công.');
    }

    public function delete($id)
    {
        $department = Department::findOrFail($id);
        
        // Kiểm tra xem phòng ban có nhân viên nào không, nếu có thì không cho xóa
        if ($department->employees()->count() > 0) {
            session()->flash('error', 'Không thể xóa vì phòng ban này đang có nhân viên.');
            return;
        }

        $department->delete();
        session()->flash('message', 'Đã xóa phòng ban thành công.');
    }

    public function render()
    {
        $departments = Department::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('code', 'like', '%' . $this->search . '%')
            ->withCount('employees')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.hr.department-manager', [
            'departments' => $departments
        ])->layout('components.warehouse-layout', ['title' => 'Cấu Hình Phòng Ban']);
    }
}

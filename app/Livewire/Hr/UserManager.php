<?php

namespace App\Livewire\Hr;

use App\Models\User;
use App\Models\Department;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $filterHouse = ''; // Lọc nhân viên theo dự án
    public $isModalOpen = false;

    // Form fields
    public $userId;
    public $code;
    public $name;
    public $email;
    public $phone;
    public $username;
    public $password;
    public $role = 'staff_kho';
    public $department = '';
    public $status = 'active';
    public $hire_date;
    public $avatar;
    public $newAvatar;
    public $permissions = [];
    public $allowed_houses = [];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterHouse()
    {
        $this->resetPage();
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($this->userId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($this->userId)],
            'role' => 'required|string',
            'department' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'hire_date' => 'nullable|date',
            'newAvatar' => 'nullable|image|max:2048', // max 2MB
            'permissions' => 'nullable|array',
            'allowed_houses' => 'nullable|array',
        ];

        if (!$this->userId) {
            $rules['password'] = 'required|string|min:6';
        }

        return $rules;
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
        $this->userId = null;
        $this->code = '';
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->username = '';
        $this->password = '';
        $this->role = 'staff_kho';
        $this->department = '';
        $this->status = 'active';
        $this->hire_date = null;
        $this->avatar = null;
        $this->newAvatar = null;
        $this->permissions = [];
        $this->allowed_houses = [];
    }

    public function updatedDepartment($value)
    {
        if (str_contains(mb_strtoupper($value), 'KHO')) {
            $this->role = 'staff_kho';
        } elseif (str_contains(mb_strtoupper($value), 'KTSC')) {
            $this->role = 'staff_ktsc';
        }
    }

    public function edit($id)
    {
        $this->resetValidation();
        $user = User::findOrFail($id);
        
        $this->userId = $user->id;
        $this->code = $user->code;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->username = $user->username;
        $this->role = $user->role;
        $this->department = $user->department;
        $this->status = $user->status;
        $this->hire_date = $user->hire_date ? \Carbon\Carbon::parse($user->hire_date)->format('Y-m-d') : null;
        $this->avatar = $user->avatar;
        $this->password = ''; // không hiện pass cũ
        $this->newAvatar = null;
        $this->permissions = is_array($user->permissions) ? $user->permissions : (is_string($user->permissions) ? json_decode($user->permissions, true) : []);
        $this->allowed_houses = is_array($user->allowed_houses) ? $user->allowed_houses : (is_string($user->allowed_houses) ? json_decode($user->allowed_houses, true) : []);
        
        if (!is_array($this->permissions)) $this->permissions = [];
        if (!is_array($this->allowed_houses)) $this->allowed_houses = [];

        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'email' => $this->email === '' ? null : $this->email,
            'phone' => $this->phone === '' ? null : $this->phone,
            'username' => $this->username,
            'role' => $this->role,
            'department' => $this->department,
            'status' => $this->status,
            'hire_date' => $this->hire_date,
            'permissions' => $this->permissions,
            'allowed_houses' => $this->allowed_houses,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->newAvatar) {
            $path = $this->newAvatar->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        User::updateOrCreate(['id' => $this->userId], $data);

        $this->dispatch('user-saved');
        session()->flash('message', $this->userId ? 'Cập nhật nhân viên thành công.' : 'Thêm nhân viên thành công.');
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Bạn không thể tự xóa chính mình!');
            return;
        }

        $user->delete();
        session()->flash('message', 'Đã xóa nhân viên.');
    }

    public function render()
    {
        $query = User::where(function($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('code', 'like', '%' . $this->search . '%')
              ->orWhere('email', 'like', '%' . $this->search . '%')
              ->orWhere('phone', 'like', '%' . $this->search . '%');
        });

        // Lọc nhân viên theo dự án được phân quyền
        if ($this->filterHouse !== '') {
            $houseId = (string)$this->filterHouse;
            $query->where(function($q) use ($houseId) {
                $q->whereJsonContains('allowed_houses', (int)$houseId)
                  ->orWhereJsonContains('allowed_houses', $houseId)
                  ->orWhere('current_house_id', (int)$houseId);
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(15);

        try {
            $departments = Department::where('status', 'active')->orderBy('name')->get();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('UserManager: không tải được danh sách phòng ban — ' . $e->getMessage());
            $departments = collect();
        }

        $projects = \App\Models\Project::where('status', 'active')->orderBy('name')->get();

        return view('livewire.hr.user-manager', [
            'users'       => $users,
            'departments' => $departments,
            'projects'    => $projects,
        ])->layout('components.warehouse-layout', ['title' => 'Quản Lý Nhân Viên']);
    }
}

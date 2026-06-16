<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use App\Models\Project;
use Livewire\WithPagination;

class ProjectManager extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    
    public $projectId;
    public $name = '';
    public $code = '';
    public $description = '';
    public $status = 'active';

    protected $rules = [
        'name' => 'required|string|max:255',
        'code' => 'nullable|string|max:50',
        'status' => 'required|in:active,inactive',
        'description' => 'nullable|string',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->reset(['projectId', 'name', 'code', 'description', 'status']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $this->projectId = $project->id;
        $this->name = $project->name;
        $this->code = $project->code;
        $this->status = $project->status;
        $this->description = $project->description;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $isNew = empty($this->projectId);

        $project = Project::updateOrCreate(
            ['id' => $this->projectId],
            [
                'name' => $this->name,
                'code' => $this->code,
                'status' => $this->status,
                'description' => $this->description,
            ]
        );

        if ($isNew) {
            try {
                // Tạo database mới
                $dbName = 'laravel_' . $project->id;
                \Illuminate\Support\Facades\DB::connection('mysql')->statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                // Lưu lại connection cũ
                $defaultConnection = \Illuminate\Support\Facades\Config::get('database.default');

                // Thiết lập connection tenant tạm thời để migrate
                \Illuminate\Support\Facades\Config::set('database.connections.tenant.database', $dbName);
                \Illuminate\Support\Facades\DB::purge('tenant');
                
                // Chạy lệnh migrate
                \Illuminate\Support\Facades\Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--force' => true,
                ]);

                // Khôi phục connection cũ
                \Illuminate\Support\Facades\Config::set('database.default', $defaultConnection);
                \Illuminate\Support\Facades\DB::purge('tenant');
                
            } catch (\Exception $e) {
                \Log::error("Tạo database thất bại cho Project ID {$project->id}: " . $e->getMessage());
                session()->flash('error', 'Dự án đã được tạo nhưng có lỗi khi khởi tạo Database: ' . $e->getMessage());
                
                $this->showModal = false;
                $this->reset(['projectId', 'name', 'code', 'description', 'status']);
                return;
            }
        }

        session()->flash('success', $this->projectId ? 'Cập nhật Dự án thành công!' : 'Thêm mới Dự án thành công!');
        
        $this->showModal = false;
        $this->reset(['projectId', 'name', 'code', 'description', 'status']);
    }

    public function delete($id)
    {
        Project::findOrFail($id)->delete();
        session()->flash('success', 'Xóa Dự án thành công!');
    }

    public function render()
    {
        $projects = Project::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('code', 'like', '%' . $this->search . '%')
            ->paginate(10);

        return view('livewire.hr.project-manager', compact('projects'))
            ->layout('layouts.app');
    }
}

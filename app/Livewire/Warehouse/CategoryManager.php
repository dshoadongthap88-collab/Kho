<?php

namespace App\Livewire\Warehouse;

use App\Models\Category;
use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryManager extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';
    public $showModal = false;
    public $isEdit = false;
    public $categoryId;

    public $name;
    public $description;
    public $status = 'active';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'status', 'categoryId']);
        
        if ($id) {
            $this->isEdit = true;
            $this->categoryId = $id;
            $category = Category::findOrFail($id);
            $this->name = $category->name;
            $this->description = $category->description;
            $this->status = $category->status;
        } else {
            $this->isEdit = false;
            $this->status = 'active';
        }

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $slug = Str::slug($this->name);

        if ($this->isEdit) {
            $category = Category::findOrFail($this->categoryId);
            $category->update([
                'name' => $this->name,
                'slug' => $slug,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Cập nhật danh mục thành công.');
        } else {
            Category::create([
                'name' => $this->name,
                'slug' => $slug,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Thêm danh mục mới thành công.');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        try {
            $category = Category::findOrFail($id);
            if ($category->products()->count() > 0) {
                session()->flash('error', 'Không thể xóa danh mục này vì đang có vật tư liên kết.');
                return;
            }
            $category->delete();
            session()->flash('message', 'Đã xoá danh mục.');
        } catch (\Exception $e) {
            session()->flash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $categories = Category::query()
            ->withCount('products')
            ->where('name', 'like', '%' . $this->search . '%')
            ->latest()
            ->paginate(15);

        return view('livewire.warehouse.category-manager', [
            'categories' => $categories
        ])->layout('layouts.app');
    }
}

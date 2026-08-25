<div>
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Quản lý Ngôi nhà (Dự án)</h1>
            <p class="text-sm text-slate-500">Thêm, sửa, xóa các dự án trong hệ thống</p>
        </div>
        <button wire:click="create" class="px-4 py-2 bg-indigo-600 text-white rounded-xl shadow hover:bg-indigo-700 font-bold flex items-center gap-2">
            <span>+</span> Thêm Ngôi nhà mới
        </button>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-2 bg-emerald-100 text-emerald-800 rounded-xl font-bold border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-2 border-b border-slate-200 bg-slate-50">
            <input wire:model.live="search" type="text" placeholder="Tìm kiếm tên, mã dự án..." class="w-full max-w-md px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                    <th class="px-3 py-3 text-left">Mã</th>
                    <th class="px-3 py-3 text-left">Tên Ngôi nhà / Dự án</th>
                    <th class="px-3 py-3 text-left">Trạng thái</th>
                    <th class="px-3 py-3 text-left">Mô tả</th>
                    <th class="px-3 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                <tr class="border-b border-slate-100 hover:bg-slate-50">
                    <td class="p-2 text-slate-800 font-medium">{{ $project->code ?? '-' }}</td>
                    <td class="p-2 font-bold text-indigo-700">{{ $project->name }}</td>
                    <td class="p-2">
                        @if($project->status === 'active')
                            <span class="px-1.5 py-1 text-[11px] bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">Hoạt động</span>
                        @else
                            <span class="px-1.5 py-1 text-[11px] bg-slate-200 text-slate-600 text-xs font-bold rounded-full">Ngừng hoạt động</span>
                        @endif
                    </td>
                    <td class="p-2 text-slate-600 text-sm">{{ $project->description ?? '-' }}</td>
                    <td class="p-2 text-right">
                        <button wire:click="edit({{ $project->id }})" class="text-blue-600 hover:text-blue-800 font-bold text-xs mr-3">Sửa</button>
                        <button wire:click="delete({{ $project->id }})" onclick="confirm('Bạn có chắc chắn muốn xóa dự án này?') || event.stopImmediatePropagation()" class="text-red-600 hover:text-red-800 font-bold text-xs">Xóa</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-slate-500">Không tìm thấy dự án nào.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-2 border-t border-slate-200">
            {{ $projects->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-2">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-slate-800">{{ $projectId ? 'Chỉnh sửa Dự án' : 'Thêm mới Dự án' }}</h2>
                <button wire:click="$set('showModal', false)" class="text-slate-400 hover:text-slate-600">&times;</button>
            </div>
            
            <div class="p-2 space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Mã dự án</label>
                    <input wire:model="code" type="text" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Tên Dự án / Ngôi nhà <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Trạng thái <span class="text-red-500">*</span></label>
                    <select wire:model="status" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="active">Hoạt động</option>
                        <option value="inactive">Ngừng hoạt động</option>
                    </select>
                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Mô tả</label>
                    <textarea wire:model="description" rows="3" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <button wire:click="$set('showModal', false)" class="px-4 py-2 text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 font-medium">Hủy</button>
                <button wire:click="save" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-bold shadow-sm">Lưu lại</button>
            </div>
        </div>
    </div>
    @endif
</div>

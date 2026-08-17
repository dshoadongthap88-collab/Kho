<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Quản lý Phòng Ban</h2>
            <p class="text-sm text-gray-500">Quản lý danh sách các phòng ban và tổ chức trong công ty</p>
        </div>
        <button wire:click="openModal" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition flex items-center gap-2 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Thêm phòng ban mới
        </button>
    </div>

    <!-- Thông báo -->
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
            <p>{{ session('message') }}</p>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <div class="relative w-full max-w-md">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm kiếm theo mã, tên phòng ban..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm font-semibold uppercase tracking-wider border-b">
                        <th class="px-6 py-4 w-24">Mã PB</th>
                        <th class="px-6 py-4">Tên Phòng Ban</th>
                        <th class="px-6 py-4">Trưởng phòng</th>
                        <th class="px-6 py-4 text-center">Số lượng NV</th>
                        <th class="px-6 py-4 text-center">Trạng thái</th>
                        <th class="px-6 py-4 text-right w-32">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($departments as $dept)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4 text-sm font-medium text-indigo-600">{{ $dept->code }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800">{{ $dept->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $dept->manager_name ?: '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $dept->employees_count }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($dept->status === 'active')
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Hoạt động</span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Ngừng hoạt động</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="edit({{ $dept->id }})" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 p-1.5 rounded transition" title="Sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <button wire:click="delete({{ $dept->id }})" wire:confirm="Bạn có chắc chắn muốn xóa phòng ban này?" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded transition" title="Xóa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 bg-gray-50/50">Không tìm thấy phòng ban nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $departments->links() }}
        </div>
    </div>

    <!-- Modal Form -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-bold text-gray-900 mb-6" id="modal-title">
                                    {{ $departmentId ? 'Chỉnh sửa phòng ban' : 'Thêm mới phòng ban' }}
                                </h3>
                                
                                <form wire:submit.prevent="save">
                                    <div class="space-y-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Mã phòng ban <span class="text-red-500">*</span></label>
                                                <input type="text" wire:model.defer="code" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="VD: PB01">
                                                @error('code') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                            
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Trạng thái</label>
                                                <select wire:model.defer="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                    <option value="active">Hoạt động</option>
                                                    <option value="inactive">Ngừng hoạt động</option>
                                                </select>
                                                @error('status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Tên phòng ban <span class="text-red-500">*</span></label>
                                            <input type="text" wire:model.defer="name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nhập tên phòng ban">
                                            @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Tên trưởng phòng</label>
                                            <input type="text" wire:model.defer="manager_name" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Nhập tên người quản lý">
                                            @error('manager_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Mô tả thêm</label>
                                            <textarea wire:model.defer="description" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Mô tả về chức năng, nhiệm vụ..."></textarea>
                                            @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    
                                    <!-- Hidden Submit Button to allow enter to submit -->
                                    <button type="submit" class="hidden"></button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-row-reverse gap-2">
                        <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:w-auto sm:text-sm">
                            Lưu lại
                        </button>
                        <button type="button" wire:click="closeModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                            Hủy bỏ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<div>
    <div class="bg-indigo-900 text-white shadow relative z-20 flex justify-between items-center px-6 py-4">
        <div class="flex items-center gap-3">
            <span class="text-2xl">⚙️</span>
            <h1 class="text-xl font-bold tracking-wide">CẤU HÌNH PHÂN QUYỀN MODULE</h1>
        </div>
        <a href="{{ route('hr.dashboard') }}" class="w-10 h-10 rounded-full hover:bg-red-500 bg-indigo-800 flex items-center justify-center transition-colors shadow focus:outline-none" title="Đóng">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </a>
    </div>

    @if (session()->has("message"))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-2 absolute top-20 right-4 z-50 shadow-md transform transition-all duration-300 rounded" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
        <p>{{ session("message") }}</p>
    </div>
    @endif

    <div class="px-6 py-4 flex flex-col h-[calc(100vh-80px)] overflow-hidden gap-2">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 flex flex-col flex-1 overflow-hidden">
            <div class="p-2 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <div class="relative w-1/3">
                    <input type="text" wire:model.live="search" class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" placeholder="Tìm kiếm module, nhóm...">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <button wire:click="create" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                    Thêm Module
                </button>
            </div>
            
            <div class="flex-1 overflow-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase sticky top-0 shadow-sm z-10">
                        <tr>
                            <th class="px-2 py-2 font-semibold border-b">ID</th>
                            <th class="px-2 py-2 font-semibold border-b">Nhóm</th>
                            <th class="px-2 py-2 font-semibold border-b">Route / Mã</th>
                            <th class="px-2 py-2 font-semibold border-b">Tên hiển thị</th>
                            <th class="px-2 py-2 font-semibold border-b">Trạng thái</th>
                            <th class="px-2 py-2 font-semibold border-b text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        @foreach ($modules as $module)
                        <tr class="hover:bg-indigo-50/50 transition-colors">
                            <td class="px-2 py-1.5 text-gray-500">{{ $module->id }}</td>
                            <td class="px-2 py-1.5 font-medium text-indigo-900">{{ $module->group_name }}</td>
                            <td class="px-2 py-1.5 text-gray-600 font-mono text-xs">{{ $module->route_name }}</td>
                            <td class="px-2 py-1.5 text-gray-800">{{ $module->label }}</td>
                            <td class="px-2 py-1.5">
                                <button wire:click="toggleActive({{ $module->id }})" class="px-1.5 py-1 text-[11px] rounded text-xs font-bold {{ $module->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $module->is_active ? "Đang bật" : "Đã tắt" }}
                                </button>
                            </td>
                            <td class="px-2 py-1.5 text-center">
                                <button wire:click="edit({{ $module->id }})" class="text-blue-500 hover:text-blue-700 p-1 rounded hover:bg-blue-50 transition-colors" title="Sửa">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="p-3 border-t border-gray-100 bg-gray-50">
                {{ $modules->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Form -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ $moduleId ? "Sửa Module" : "Thêm Module Mới" }}
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tên nhóm (VD: 1. Kho)</label>
                                    <input type="text" wire:model="group_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error("group_name") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tên Route / Mã (VD: warehouse.stock-in)</label>
                                    <input type="text" wire:model="route_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error("route_name") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tên hiển thị (VD: Nhập kho)</label>
                                    <input type="text" wire:model="label" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error("label") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" wire:model="is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">Kích hoạt (Hiển thị trong form phân quyền)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Lưu thay đổi
                    </button>
                    <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
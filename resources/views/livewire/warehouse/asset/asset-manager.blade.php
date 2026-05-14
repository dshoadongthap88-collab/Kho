<div>
    @if (session()->has('message'))
        <div class="mb-4 bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    @if($isFormOpen)
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 mb-6 animate-in slide-in-from-top-4 duration-300">
            <div class="p-4 border-b border-slate-200 bg-indigo-50 rounded-t-xl">
                <h2 class="text-lg font-black text-indigo-900 uppercase tracking-tight">
                    {{ $isEditing ? 'Sửa thông tin tài sản' : 'Thêm mới tài sản' }}
                </h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Mã tài sản <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="asset_code" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold uppercase" placeholder="Nhập mã...">
                        @error('asset_code') <span class="text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1 md:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Tên thiết bị / Tài sản <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold" placeholder="Tên thiết bị...">
                        @error('name') <span class="text-xs text-red-500 font-semibold">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Bộ phận sử dụng</label>
                        <input type="text" wire:model="department" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="VD: Xưởng cơ khí...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Loại máy</label>
                        <input type="text" wire:model="machine_type" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="VD: Máy CNC, Máy tiện...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Hãng sản xuất</label>
                        <input type="text" wire:model="manufacturer" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Nhập hãng sản xuất...">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Model</label>
                        <input type="text" wire:model="model" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Mã model...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Số Serial</label>
                        <input type="text" wire:model="serial_number" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono uppercase" placeholder="S/N...">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Ngày lắp đặt</label>
                        <input type="date" wire:model="installation_date" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold">
                    </div>
                    
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-700 uppercase">Trạng thái <span class="text-red-500">*</span></label>
                        <select wire:model="status" class="w-full rounded-lg border-slate-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold">
                            <option value="active">🟢 Đang hoạt động</option>
                            <option value="maintenance">🟠 Đang bảo trì</option>
                            <option value="inactive">🔴 Ngừng hoạt động</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3 rounded-b-xl">
                <button wire:click="closeForm" class="px-6 py-2 rounded-lg font-bold text-slate-600 hover:bg-slate-200 transition">Hủy bỏ</button>
                <button wire:click="save" class="px-6 py-2 rounded-lg font-black text-white bg-indigo-600 hover:bg-indigo-700 shadow-md transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    LƯU LẠI
                </button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-4 border-b border-slate-200 bg-slate-50 flex flex-wrap justify-between items-center gap-4">
            <h2 class="text-lg font-black text-slate-800 uppercase tracking-tight flex items-center gap-2">
                <span class="p-1.5 bg-indigo-100 text-indigo-700 rounded-lg">⚙️</span>
                DANH MỤC THIẾT BỊ
            </h2>
            <div class="flex items-center gap-3">
                @if(count($selectedIds) > 0)
                    <div class="flex items-center gap-2 pr-3 border-r border-slate-300 animate-in slide-in-from-right-4">
                        <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-1 rounded-md">Đã chọn: {{ count($selectedIds) }}</span>
                        <button class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-bold hover:bg-black transition shadow-sm">
                            🖨️ In mã QR
                        </button>
                        <button wire:click="deleteSelected" wire:confirm="Xóa {{ count($selectedIds) }} tài sản đã chọn?" class="px-3 py-1.5 bg-rose-500 text-white rounded-lg text-xs font-bold hover:bg-rose-600 transition shadow-sm">
                            🗑️ Xóa
                        </button>
                    </div>
                @endif
                <div class="relative">
                    <input wire:model.live="search" type="text" placeholder="Tìm mã, tên..." class="pl-9 pr-4 py-2 w-64 text-sm font-bold border-slate-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 shadow-inner">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                @if(!$isFormOpen)
                    <button wire:click="openForm" class="bg-indigo-600 text-white px-5 py-2 rounded-xl text-sm font-black hover:bg-indigo-700 transition shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        THÊM MỚI
                    </button>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-800 text-xs uppercase font-black text-white tracking-widest">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll" class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500">
                        </th>
                        <th class="px-4 py-3">Mã TS</th>
                        <th class="px-4 py-3">Tên Thiết Bị / Máy</th>
                        <th class="px-4 py-3">Bộ phận</th>
                        <th class="px-4 py-3">Model/Serial</th>
                        <th class="px-4 py-3 text-center">Trạng thái</th>
                        <th class="px-4 py-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($assets as $asset)
                    <tr class="hover:bg-indigo-50/30 transition group {{ in_array($asset->id, $selectedIds) ? 'bg-indigo-50' : '' }}">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $asset->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3 font-black text-indigo-700">{{ $asset->asset_code }}</td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-800">{{ $asset->name }}</div>
                            <div class="text-[10px] uppercase text-slate-400 font-bold">{{ $asset->machine_type }} {{ $asset->manufacturer ? '('.$asset->manufacturer.')' : '' }}</div>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $asset->department ?: '-' }}</td>
                        <td class="px-4 py-3 text-xs">
                            <div class="font-bold">{{ $asset->model ?: '-' }}</div>
                            <div class="font-mono text-slate-400">{{ $asset->serial_number }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($asset->status == 'active')
                                <span class="px-2.5 py-1 text-[10px] uppercase font-black rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">Hoạt động</span>
                            @elseif($asset->status == 'maintenance')
                                <span class="px-2.5 py-1 text-[10px] uppercase font-black rounded-lg bg-amber-50 text-amber-700 border border-amber-100">Bảo trì</span>
                            @else
                                <span class="px-2.5 py-1 text-[10px] uppercase font-black rounded-lg bg-rose-50 text-rose-700 border border-rose-100">Ngừng HĐ</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <button wire:click="edit({{ $asset->id }})" class="p-1.5 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Sửa">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                <button wire:click="delete({{ $asset->id }})" wire:confirm="Xóa thiết bị này?" class="p-1.5 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Xóa">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                            <div class="text-4xl mb-2">🏭</div>
                            <div class="font-bold">Chưa có thiết bị nào trong danh mục</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-slate-50 border-t border-slate-100">
            {{ $assets->links() }}
        </div>
    </div>
</div>

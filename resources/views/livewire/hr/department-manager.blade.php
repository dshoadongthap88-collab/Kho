<div>
    {{-- Flash --}}
    @if(session('message'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)"
             class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('message') }}
        </div>
    @endif
    @if(session('error'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Toolbar --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="relative w-72">
                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 pointer-events-none">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Mã, tên phòng ban..."
                       class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
            </div>

            <button wire:click="openModal"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Thêm phòng ban
            </button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="px-5 py-3 w-24">Mã PB</th>
                        <th class="px-5 py-3">Tên phòng ban</th>
                        <th class="px-5 py-3">Trưởng phòng</th>
                        <th class="px-5 py-3 text-center w-28">Số NV</th>
                        <th class="px-5 py-3 text-center w-32">Trạng thái</th>
                        <th class="px-5 py-3 text-center w-24">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($departments as $dept)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-2.5 font-mono text-xs font-bold text-indigo-600">{{ $dept->code }}</td>
                            <td class="px-5 py-2.5 font-semibold text-slate-800">{{ $dept->name }}</td>
                            <td class="px-5 py-2.5 text-slate-600 text-sm">{{ $dept->manager_name ?: '—' }}</td>
                            <td class="px-5 py-2.5 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 ring-1 ring-blue-200">
                                    {{ $dept->employees_count }}
                                </span>
                            </td>
                            <td class="px-5 py-2.5 text-center">
                                @if($dept->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 ring-1 ring-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Ngừng
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="edit({{ $dept->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $dept->id }})"
                                            wire:confirm="Xác nhận xóa phòng ban {{ $dept->name }}?"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Xóa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <p class="font-medium text-slate-600">Chưa có phòng ban nào</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200">
            {{ $departments->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">
                            {{ $departmentId ? 'Chỉnh sửa phòng ban' : 'Thêm phòng ban mới' }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Điền thông tin phòng ban</p>
                    </div>
                    <button wire:click="closeModal" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Mã phòng ban <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model.defer="code" placeholder="VD: PB01">
                            @error('code') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Trạng thái</label>
                            <select wire:model.defer="status">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Ngừng hoạt động</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Tên phòng ban <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model.defer="name" placeholder="Nhập tên phòng ban">
                        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Trưởng phòng</label>
                        <input type="text" wire:model.defer="manager_name" placeholder="Tên người quản lý">
                    </div>
                    <div>
                        <label class="form-label">Mô tả</label>
                        <textarea wire:model.defer="description" rows="3" placeholder="Chức năng, nhiệm vụ..."></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button wire:click="closeModal"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                        Hủy
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                            class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">Lưu lại</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

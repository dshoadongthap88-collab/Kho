<div x-data="{ showFilters: false }">

    {{-- ===== TOOLBAR ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4">
        <div class="flex flex-wrap items-center justify-between gap-3">

            {{-- Left: Search + Filters --}}
            <div class="flex flex-wrap items-center gap-2 flex-1">
                {{-- Search --}}
                <div class="relative w-72">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Tên, SĐT, người liên hệ..."
                           class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 bg-slate-50 transition">
                </div>

                {{-- Type Filter --}}
                <select wire:model.live="filterType"
                        class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                    <option value="">Tất cả loại</option>
                    <option value="customer">Khách hàng</option>
                    <option value="supplier">Nhà cung cấp</option>
                    <option value="both">Cả hai</option>
                    <option value="internal">Nội bộ</option>
                </select>

                {{-- Bulk actions --}}
                @if(count($selectedContacts) > 0)
                    <div class="flex items-center gap-2 pl-2 border-l border-slate-200 animate-in slide-in-from-left-2 duration-200">
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">
                            {{ count($selectedContacts) }} đã chọn
                        </span>
                        <button wire:click="printContacts"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            In
                        </button>
                    </div>
                @endif
            </div>

            {{-- Right: Export + Add --}}
            <div class="flex items-center gap-2">
                <button wire:click="exportExcel"
                        class="flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white border border-emerald-200 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </button>
                <button wire:click="openModal()"
                        class="flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Thêm đối tác
                </button>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('message') }}
        </div>
    @endif
    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ===== TABLE ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" wire:model.live="selectAll"
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-3 w-28">Loại</th>
                        <th class="px-4 py-3">Tên đối tác</th>
                        <th class="px-4 py-3">Người liên hệ</th>
                        <th class="px-4 py-3">Số điện thoại</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Bộ phận</th>
                        <th class="px-4 py-3 w-24 text-center">Trạng thái</th>
                        <th class="px-4 py-3 w-24 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($contacts as $contact)
                        @php
                            $typeMap = [
                                'customer' => ['label' => 'Khách hàng', 'class' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'],
                                'supplier' => ['label' => 'Nhà CC', 'class' => 'bg-violet-50 text-violet-700 ring-1 ring-violet-200'],
                                'both'     => ['label' => 'Cả hai', 'class' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'],
                                'internal' => ['label' => 'Nội bộ', 'class' => 'bg-teal-50 text-teal-700 ring-1 ring-teal-200'],
                            ];
                            $t = $typeMap[$contact->type] ?? ['label' => $contact->type, 'class' => 'bg-slate-100 text-slate-600'];
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ in_array((string)$contact->id, $selectedContacts) ? 'bg-indigo-50/40' : '' }}">
                            <td class="px-4 py-2.5 text-center">
                                <input type="checkbox" wire:model.live="selectedContacts" value="{{ $contact->id }}"
                                       class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold {{ $t['class'] }}">
                                    {{ $t['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="font-semibold text-slate-800">{{ $contact->name }}</div>
                                @if($contact->address)
                                    <div class="text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $contact->address }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 font-medium">{{ $contact->contact_person ?? '-' }}</td>
                            <td class="px-4 py-2.5">
                                @if($contact->phone)
                                    <a href="tel:{{ $contact->phone }}" class="font-mono text-slate-600 hover:text-indigo-600 transition-colors text-xs">
                                        {{ $contact->phone }}
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5">
                                @if($contact->email)
                                    <a href="mailto:{{ $contact->email }}" class="text-xs text-indigo-500 hover:text-indigo-700 hover:underline transition-colors">
                                        {{ $contact->email }}
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-xs text-slate-500">{{ $contact->department ?? '-' }}</td>
                            <td class="px-4 py-2.5 text-center">
                                @if($contact->status === 'active')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 ring-1 ring-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Ngừng
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openModal({{ $contact->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Chỉnh sửa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="delete({{ $contact->id }})"
                                            wire:confirm="Xác nhận xoá đối tác {{ $contact->name }}?"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Xoá">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <div>
                                        <p class="font-semibold text-slate-600">Chưa có đối tác nào</p>
                                        <p class="text-sm mt-1">Nhấn <span class="font-bold text-indigo-600">+ Thêm đối tác</span> để bắt đầu</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200">
            {{ $contacts->links() }}
        </div>
    </div>

    {{-- ===== MODAL THÊM / SỬA ===== --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">
                            {{ $isEdit ? 'Chỉnh sửa đối tác' : 'Thêm đối tác mới' }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            {{ $isEdit ? 'Cập nhật thông tin liên hệ' : 'Điền thông tin để tạo đối tác' }}
                        </p>
                    </div>
                    <button wire:click="$set('showModal', false)"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-4 max-h-[65vh] overflow-y-auto">
                    @if($errors->any())
                        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-2.5 rounded-xl text-xs font-medium">
                            Vui lòng kiểm tra lại thông tin bắt buộc.
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">
                            Tên đối tác <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" wire:model="name"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition"
                               placeholder="Tên công ty hoặc cá nhân">
                        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Người liên hệ</label>
                            <input type="text" wire:model="contact_person"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                            @error('contact_person') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Số điện thoại</label>
                            <input type="text" wire:model="phone"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                            @error('phone') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Email</label>
                            <input type="email" wire:model="email"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                            @error('email') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Bộ phận</label>
                            <input type="text" wire:model="department" placeholder="(Nội bộ)"
                                   class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                            @error('department') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Địa chỉ</label>
                        <textarea wire:model="address" rows="2"
                                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition resize-none"></textarea>
                        @error('address') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">
                                Phân loại <span class="text-rose-500">*</span>
                            </label>
                            <select wire:model="type"
                                    class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                                <option value="customer">Khách hàng</option>
                                <option value="supplier">Nhà cung cấp</option>
                                <option value="both">Cả hai</option>
                                <option value="internal">Nội bộ</option>
                            </select>
                            @error('type') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Trạng thái</label>
                            <select wire:model="status"
                                    class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                                <option value="active">Hoạt động</option>
                                <option value="inactive">Ngừng hoạt động</option>
                            </select>
                            @error('status') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button wire:click="$set('showModal', false)"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                        Hủy
                    </button>
                    <button wire:click="save" wire:loading.attr="disabled"
                            class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm shadow-indigo-200 transition flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">
                            {{ $isEdit ? 'Lưu thay đổi' : 'Tạo đối tác' }}
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Đang lưu...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('open-print-url', (event) => {
                window.open(event.url, '_blank');
            });
        });
    </script>
</div>

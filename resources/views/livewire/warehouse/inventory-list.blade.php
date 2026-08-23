<div>

    {{-- Flash --}}
    @if(session('success'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)"
             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium no-print">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,5000)"
             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium no-print">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ===== TOOLBAR ===== --}}
    <div class="filter-bar no-print">
        <div class="filter-grid">

            <div class="filter-field">
                <label class="form-label" for="inv-search">Tìm kiếm</label>
                <div class="input-group">
                    <span class="input-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input id="inv-search" wire:model.live.debounce.300ms="search" type="text"
                           class="input-sm" placeholder="Tên / Mã vật tư...">
                </div>
            </div>

            <div class="filter-field">
                <label class="form-label" for="inv-brand">Hãng sản xuất</label>
                <select id="inv-brand" wire:model.live="filterBrand" class="input-sm">
                    <option value="">Tất cả hãng</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}">{{ $brand }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label class="form-label" for="inv-location">Vị trí</label>
                <input id="inv-location" wire:model.live.debounce.300ms="filterLocation" type="text"
                       list="locations_list" class="input-sm" placeholder="Tất cả vị trí">
                <datalist id="locations_list">
                    @foreach($locations as $loc)<option value="{{ $loc }}">@endforeach
                </datalist>
            </div>

            <div class="filter-field">
                <label class="form-label" for="inv-status">Trạng thái</label>
                <select id="inv-status" wire:model.live="filterStatus" class="input-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="sufficient">🟢 Đủ hàng</option>
                    <option value="warning">🟡 Cảnh báo</option>
                    <option value="critical">🔴 Thiếu hàng</option>
                </select>
            </div>

            {{-- Hàng nút hành động, dồn phải; thao tác theo lựa chọn nằm bên trái --}}
            <div class="filter-actions">
                @if(count($selectedItems) > 0)
                    <div class="filter-actions-note flex items-center gap-2">
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">
                            {{ count($selectedItems) }} đã chọn
                        </span>
                        @if(count($selectedItems) === 1)
                            <button wire:click="openEditModal"
                                    class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-amber-600 bg-amber-50 hover:bg-amber-600 hover:text-white rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                Sửa
                            </button>
                        @endif
                        <button wire:click="deleteSelected"
                                wire:confirm="Xác nhận xóa dữ liệu tồn kho các vật tư đã chọn?"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Xóa ({{ count($selectedItems) }})
                        </button>
                    </div>
                @endif

                <button wire:click="exportExcel"
                        class="flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white border border-emerald-200 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </button>
                <button wire:click="$set('showImportModal', true)"
                        class="flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-600 hover:text-white border border-blue-200 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import
                </button>
                <a href="{{ route('warehouse.inventory.print', array_filter([
                        'search'   => $search,
                        'brand'    => $filterBrand,
                        'location' => $filterLocation,
                        'status'   => $filterStatus,
                        'ids'      => count($selectedItems) ? implode(',', $selectedItems) : null,
                    ])) }}" target="_blank"
                   class="flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-700 hover:text-white border border-slate-200 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    {{ count($selectedItems) ? 'In ('.count($selectedItems).')' : 'In tất cả' }}
                </a>
            </div>
        </div>
    </div>

    {{-- ===== TABLE ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    @php
                        $pageIds = $inventories->pluck('id')->map(fn($id) => (int)$id)->all();
                        $pageAllSelected = count($pageIds) > 0
                            && count(array_intersect($pageIds, array_map('intval', $selectedItems))) === count($pageIds);
                    @endphp
                    <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                        <th class="px-3 py-3 w-10 text-center no-print">
                            <input type="checkbox"
                                   wire:click="toggleSelectAll([{{ implode(',', $pageIds) }}])"
                                   {{ $pageAllSelected ? 'checked' : '' }}
                                   class="rounded border-slate-600 bg-slate-700 text-indigo-400 focus:ring-indigo-500 cursor-pointer"
                                   title="Chọn tất cả trang này">
                        </th>
                        <th wire:click="sortBy('products.code')"
                            class="px-3 py-3 cursor-pointer hover:bg-slate-700 transition select-none">
                            Mã VT
                            @if($sortField === 'products.code')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th wire:click="sortBy('products.name')"
                            class="px-3 py-3 cursor-pointer hover:bg-slate-700 transition select-none">
                            Tên Vật Tư
                            @if($sortField === 'products.name')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-3 py-3 text-center">Hãng SX</th>
                        <th class="px-3 py-3 text-center">Mã Code NCC</th>
                        <th class="px-3 py-3 text-center">Hạn dùng</th>
                        <th class="px-3 py-3 text-center">ĐVT</th>
                        <th wire:click="sortBy('inventories.quantity')"
                            class="px-3 py-3 text-center cursor-pointer hover:bg-slate-700 transition select-none">
                            Tồn kho
                            @if($sortField === 'inventories.quantity')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-3 py-3 text-center">Vị trí</th>
                        <th class="px-3 py-3 text-center">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($inventories as $inv)
                        @php
                            $available  = $inv->quantity - $inv->reserved_quantity;
                            $isSelected = in_array($inv->id, $selectedItems);
                            if ($available < $inv->min_stock) {
                                $badgeClass = 'bg-rose-50 text-rose-700 ring-1 ring-rose-200';
                                $badgeText  = 'Thiếu hàng';
                                $rowExtra   = 'bg-rose-50/20';
                                $qtyClass   = 'text-rose-600 font-bold';
                            } elseif ($available < $inv->min_stock * 1.5) {
                                $badgeClass = 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
                                $badgeText  = 'Cảnh báo';
                                $rowExtra   = 'bg-amber-50/10';
                                $qtyClass   = 'text-amber-600 font-bold';
                            } else {
                                $badgeClass = 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
                                $badgeText  = 'Đủ hàng';
                                $rowExtra   = '';
                                $qtyClass   = 'text-indigo-700 font-bold';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition-colors {{ $isSelected ? 'bg-indigo-50/40' : $rowExtra }}">
                            <td class="px-3 py-2 text-center no-print">
                                <input type="checkbox" wire:model.live="selectedItems" value="{{ $inv->id }}"
                                       class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500 cursor-pointer">
                            </td>
                            <td class="px-3 py-2 font-mono text-xs text-indigo-600 font-bold">{{ $inv->product_code }}</td>
                            <td class="px-3 py-2 font-semibold text-slate-800 text-sm">{{ $inv->product_name }}</td>
                            <td class="px-3 py-2 text-center text-xs text-slate-500">{{ $inv->brand ?? '—' }}</td>
                            <td class="px-3 py-2 text-center font-mono text-xs text-slate-500">{{ $inv->batch_number ?? '—' }}</td>
                            <td class="px-3 py-2 text-center text-xs text-slate-400 italic">
                                {{ $inv->expiry_date ? \Carbon\Carbon::parse($inv->expiry_date)->format('d/m/y') : '—' }}
                            </td>
                            <td class="px-3 py-2 text-center text-xs text-slate-500">{{ $inv->unit }}</td>
                            <td class="px-3 py-2 text-center text-sm {{ $qtyClass }}">{{ number_format($inv->quantity) }}</td>
                            <td class="px-3 py-2 text-center text-xs text-slate-500">{{ $inv->warehouse_location ?? '—' }}</td>
                            <td class="px-3 py-2 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold {{ $badgeClass }}">
                                    {{ $badgeText }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <p class="font-semibold text-slate-600">Chưa có dữ liệu tồn kho</p>
                                    <p class="text-sm">Thử thay đổi bộ lọc hoặc nhập hàng trước</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 no-print">
            <div class="flex flex-wrap items-center justify-between gap-3">
                {{-- 1050 vật tư mà mỗi trang 10 dòng là 105 trang — cho chọn số dòng --}}
                <div class="flex items-center gap-2 text-xs font-bold text-slate-600 shrink-0">
                    <span>Hiển thị</span>
                    <select wire:model.live="perPage" class="input-sm" style="width:auto">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                    <span>/ {{ number_format($inventories->total()) }} vật tư</span>
                </div>
                <div class="flex-1 min-w-0">{{ $inventories->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ===== MODAL SỬA ===== --}}
    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800">Chỉnh sửa thông tin vật tư</h3>
                    <button wire:click="$set('showEditModal', false)" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-3 max-h-[65vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Mã Vật Tư <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="editingProductCode" class="font-mono">
                            @error('editingProductCode') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Hãng SX</label>
                            <input type="text" wire:model="editingBrand">
                            @error('editingBrand') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Tên Vật Tư <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="editingProductName">
                        @error('editingProductName') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Mã Code NCC</label>
                            <input type="text" wire:model="editingBatchNumber" class="font-mono">
                        </div>
                        <div>
                            <label class="form-label">Hạn dùng</label>
                            <input type="date" wire:model="editingExpiryDate">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label">Đơn vị tính <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="editingUnit">
                            @error('editingUnit') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Tồn tối thiểu</label>
                            <input type="number" step="0.01" wire:model="editingMinStock">
                        </div>
                    </div>
                    <div class="border-t border-slate-100 pt-3 grid grid-cols-2 gap-3">
                        <div>
                            <label class="form-label" style="color:#4338ca;">Số lượng tồn kho <span class="text-rose-500">*</span></label>
                            <input type="number" step="0.01" wire:model="editingQuantity" style="border-color:#a5b4fc; background:#eef2ff; color:#4338ca; font-weight:700;">
                            @error('editingQuantity') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label" style="color:#4338ca;">Vị trí lưu kho</label>
                            <input type="text" wire:model="editingLocation" style="border-color:#a5b4fc; background:#eef2ff;">
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button wire:click="$set('showEditModal', false)"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                        Hủy
                    </button>
                    <button wire:click="saveEdit" wire:loading.attr="disabled"
                            class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveEdit">Lưu thay đổi</span>
                        <span wire:loading wire:target="saveEdit" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL IMPORT ===== --}}
    @if($showImportModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showImportModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800">Nhập tồn kho từ Excel</h3>
                        <p class="text-xs text-slate-400 mt-0.5">File sẽ ghi đè tồn kho hiện tại theo mã vật tư</p>
                    </div>
                    <button wire:click="$set('showImportModal', false)" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-3">
                        <p class="text-xs font-bold text-blue-700 mb-2">Các cột được nhận (bắt buộc có Mã VT):</p>
                        <div class="grid grid-cols-2 gap-x-4 text-xs font-mono text-blue-600">
                            <div>ma_sp · ten_sp · hang_sx</div>
                            <div>dvt · so_luong · vi_tri</div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">Chọn file</label>
                        <input type="file" wire:model="excelFile" accept=".xlsx,.xls,.csv"
                               class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('excelFile') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div wire:loading wire:target="excelFile" class="text-xs text-indigo-500 flex items-center gap-1">
                        <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Đang tải file...
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button wire:click="$set('showImportModal', false)"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                        Đóng
                    </button>
                    <button wire:click="importExcel" wire:loading.attr="disabled"
                            class="px-5 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="importExcel">Xác nhận nhập</span>
                        <span wire:loading wire:target="importExcel" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Đang xử lý...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>

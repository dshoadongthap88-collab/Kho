<div>
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; margin: 0; padding: 0; }
            .bg-white { box-shadow: none !important; border: none !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #ddd !important; padding: 8px !important; }
            .noprint-row { display: none !important; }
            .status-badge { border: 1px solid #ccc !important; }
        }
        .print-only { display: none; }
    </style>

    <div class="mb-4 space-y-4 no-print">
        @if(session('success') || session('error'))
            <div class="w-full">
                @if(session('success'))
                    <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg flex items-center gap-2">
                        <span>✅</span> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded-lg flex items-center gap-2">
                        <span>❌</span> {{ session('error') }}
                    </div>
                @endif
            </div>
        @endif
        
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap gap-3 items-center">
                <!-- Tìm kiếm -->
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tên/Mã sản phẩm..."
                       class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-64">
                
                <!-- Bộ lọc Hãng SX -->
                <select wire:model.live="filterBrand" class="rounded-lg border-gray-300 shadow-sm">
                    <option value="">Tất cả hãng</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}">{{ $brand }}</option>
                    @endforeach
                </select>

                <!-- Bộ lọc Vị trí -->
                <input type="text" wire:model.live.debounce.300ms="filterLocation" placeholder="Vị trí..."
                       class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-32" list="locations_list">
                <datalist id="locations_list">
                    @foreach($locations as $loc)
                        <option value="{{ $loc }}">
                    @endforeach
                </datalist>

                <!-- Bộ lọc Trạng thái -->
                <select wire:model.live="filterStatus" class="rounded-lg border-gray-300 shadow-sm">
                    <option value="">Tất cả trạng thái</option>
                    <option value="sufficient">🟢 Đủ hàng</option>
                    <option value="warning">🟡 Cảnh báo</option>
                    <option value="critical">🔴 Thiếu hàng</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
            @if(count($selectedItems) > 0)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="bg-indigo-100 text-indigo-700 px-4 py-2 rounded-lg hover:bg-indigo-200 transition flex items-center gap-2 font-bold shadow-sm">
                        <span>⚙️ THAO TÁC</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl border border-gray-100 z-50 overflow-hidden" x-transition style="display: none;">
                        @if(count($selectedItems) === 1)
                            <button wire:click="openEditModal" @click="open = false" class="w-full text-left px-4 py-3 text-sm hover:bg-gray-50 flex items-center gap-2 text-indigo-600 font-medium">
                                ✏️ Sửa số lượng / Vị trí
                            </button>
                        @endif
                        <button wire:click="deleteSelected" wire:confirm="Xác nhận xóa dữ liệu tồn kho các sản phẩm đã chọn?" @click="open = false" class="w-full text-left px-4 py-3 text-sm hover:bg-rose-50 flex items-center gap-2 text-rose-600 font-medium border-t border-gray-100">
                            🗑️ Xóa đã chọn ({{ count($selectedItems) }})
                        </button>
                    </div>
                </div>

                <button onclick="window.print()" class="bg-slate-800 text-white px-6 py-2 rounded-lg hover:bg-black transition flex items-center gap-2 shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    In danh sách ({{ count($selectedItems) }})
                </button>
            @else
                <button wire:click="syncInventory" wire:loading.attr="disabled" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-2 shadow-sm font-bold mr-2">
                    <span wire:loading.remove wire:target="syncInventory">🔄</span>
                    <span wire:loading wire:target="syncInventory" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    Đồng bộ tồn kho
                </button>
                <button wire:click="$set('showImportModal', true)" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition flex items-center gap-2 shadow-sm font-bold mr-2">
                    📥 Nhập từ Excel
                </button>
                <button disabled title="Vui lòng chọn ít nhất 1 sản phẩm để in" class="bg-gray-300 text-gray-500 cursor-not-allowed px-6 py-2 rounded-lg flex items-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    In danh sách
                </button>
            @endif
        </div>
    </div>

    <div class="print-only report-layout text-center mb-6">
        <h1 class="text-2xl font-bold uppercase">Báo cáo tồn kho chi tiết</h1>
        <p class="text-sm text-gray-600">Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden report-layout">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll([{{ $inventories->pluck('id')->implode(',') }}])" 
                               {{ count($selectedItems) > 0 && count($selectedItems) === count($inventories->pluck('id')) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    </th>
                    <th wire:click="sortBy('products.code')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100 italic">Mã SP</th>
                    <th wire:click="sortBy('products.name')" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Tên sản phẩm</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hãng SX</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Mã Code NCC</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Hạn dùng</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">ĐVT</th>
                    <th wire:click="sortBy('inventories.quantity')" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase cursor-pointer hover:bg-gray-100">Tồn kho</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vị trí</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($inventories as $inv)
                    @php
                        $available = $inv->quantity - $inv->reserved_quantity;
                        $isSelected = in_array($inv->id, $selectedItems);
                        if ($available < $inv->min_stock) {
                            $statusColor = 'bg-red-100 text-red-800';
                            $statusText = 'Thiếu hàng';
                            $statusIcon = '🔴';
                        } elseif ($available < $inv->min_stock * 1.5) {
                            $statusColor = 'bg-yellow-100 text-yellow-800';
                            $statusText = 'Cảnh báo';
                            $statusIcon = '🟡';
                        } else {
                            $statusColor = 'bg-green-100 text-green-800';
                            $statusText = 'Đủ hàng';
                            $statusIcon = '🟢';
                        }
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $isSelected ? 'bg-indigo-50' : 'noprint-row' }}">
                        <td class="px-4 py-3 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedItems" value="{{ $inv->id }}"
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-3 text-sm font-mono text-indigo-600">{{ $inv->product_code }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $inv->product_name }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->brand ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center font-mono text-gray-600">{{ $inv->batch_number ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500 italic">{{ $inv->expiry_date ? \Carbon\Carbon::parse($inv->expiry_date)->format('d/m/y') : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->unit }}</td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-indigo-700">{{ number_format($inv->quantity) }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-500">{{ $inv->warehouse_location ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-medium status-badge {{ $statusColor }}">
                                {{ $statusIcon }} {{ $statusText }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-4 py-8 text-center text-gray-400">Chưa có dữ liệu tồn kho</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4 no-print">{{ $inventories->links() }}</div>

    <!-- Edit Modal -->
    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showEditModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4 border-b pb-2">✏️ Chỉnh sửa thông tin chi tiết</h3>
                        <div class="space-y-4 mt-4 max-h-[60vh] overflow-y-auto px-1 custom-scrollbar">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Mã SP <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="editingProductCode" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm">
                                    @error('editingProductCode') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Hãng SX</label>
                                    <input type="text" wire:model="editingBrand" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    @error('editingBrand') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tên sản phẩm <span class="text-rose-500">*</span></label>
                                <input type="text" wire:model="editingProductName" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                @error('editingProductName') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Mã Code NCC</label>
                                    <input type="text" wire:model="editingBatchNumber" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-mono">
                                    @error('editingBatchNumber') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Hạn dùng</label>
                                    <input type="date" wire:model="editingExpiryDate" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    @error('editingExpiryDate') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Đơn vị tính <span class="text-rose-500">*</span></label>
                                    <input type="text" wire:model="editingUnit" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    @error('editingUnit') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Tồn tối thiểu <span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.01" wire:model="editingMinStock" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    @error('editingMinStock') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="pt-2 border-t border-gray-100"></div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-indigo-700 mb-1">Số lượng tồn kho <span class="text-rose-500">*</span></label>
                                    <input type="number" step="0.01" wire:model="editingQuantity" class="w-full border-indigo-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm font-bold text-indigo-700 bg-indigo-50">
                                    @error('editingQuantity') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-indigo-700 mb-1">Vị trí lưu kho</label>
                                    <input type="text" wire:model="editingLocation" class="w-full border-indigo-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm bg-indigo-50">
                                    @error('editingLocation') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="saveEdit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                            Lưu thay đổi
                        </button>
                        <button type="button" wire:click="$set('showEditModal', false)" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                            Hủy bỏ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Import Modal -->
    @if($showImportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showImportModal', false)"></div>
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">📥 Nhập tồn kho từ Excel</h3>
                        
                        <div class="mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100">
                            <p class="text-[13px] text-blue-800 font-medium mb-1">Hệ thống sẽ cập nhật tự động dựa trên các cột có trong file (bắt buộc phải có cột Mã sản phẩm):</p>
                            <div class="grid grid-cols-2 gap-x-2 gap-y-1 mt-2">
                                <ul class="list-disc list-inside text-xs text-blue-700 font-mono ml-1">
                                    <li>ma_sp <span class="text-gray-500 font-sans italic">(Mã SP - Bắt buộc)</span></li>
                                    <li>ten_sp <span class="text-gray-500 font-sans italic">(Tên sản phẩm)</span></li>
                                    <li>hang_sx <span class="text-gray-500 font-sans italic">(Hãng SX)</span></li>
                                    <li>so_lo <span class="text-gray-500 font-sans italic">(Mã Code NCC)</span></li>
                                    <li>han_dung <span class="text-gray-500 font-sans italic">(Hạn dùng)</span></li>
                                </ul>
                                <ul class="list-disc list-inside text-xs text-blue-700 font-mono ml-1">
                                    <li>dvt <span class="text-gray-500 font-sans italic">(Đơn vị tính)</span></li>
                                    <li>ton_toi_thieu <span class="text-gray-500 font-sans italic">(Tồn tối thiểu)</span></li>
                                    <li>so_luong <span class="text-gray-500 font-sans italic">(Số lượng tồn)</span></li>
                                    <li>vi_tri <span class="text-gray-500 font-sans italic">(Vị trí kho)</span></li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tải file lên</label>
                            <input type="file" wire:model="excelFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-200 rounded-xl p-1">
                            @error('excelFile') <span class="text-rose-500 text-xs font-medium">{{ $message }}</span> @enderror
                        </div>

                        <div wire:loading wire:target="excelFile" class="mt-2 text-sm text-indigo-600 font-medium flex items-center gap-2">
                            <span class="w-4 h-4 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></span>
                            Đang tải file...
                        </div>
                        <div wire:loading wire:target="importExcel" class="mt-2 text-sm text-emerald-600 font-medium flex items-center gap-2">
                            <span class="w-4 h-4 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></span>
                            Đang xử lý dữ liệu...
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                        <button type="button" wire:click="importExcel" wire:loading.attr="disabled" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-bold text-white hover:bg-emerald-700 sm:ml-3 sm:w-auto sm:text-sm transition-all disabled:opacity-50">
                            Xác nhận Nhập
                        </button>
                        <button type="button" wire:click="$set('showImportModal', false)" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                            Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

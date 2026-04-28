<div style="font-family: 'Times New Roman', Times, serif;">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4 no-print relative z-10 bg-white p-3 rounded-xl shadow-sm border border-slate-200">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Search Standard -->
            <div class="relative w-64">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm tên, mã, hãng..." class="w-full pl-9 pr-3 py-2 text-[12px] font-bold rounded-lg border-slate-200 focus:ring-indigo-500 shadow-sm transition-all bg-slate-50 focus:bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex gap-1 bg-slate-100 p-1 rounded-lg">
                <button wire:click="$set('filterMode', 'all')" class="px-3 py-1 text-[10px] font-black uppercase rounded transition-all {{ $filterMode === 'all' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Tất cả</button>
                <button wire:click="$set('filterMode', 'expiring')" class="px-3 py-1 text-[10px] font-black uppercase rounded transition-all {{ $filterMode === 'expiring' ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Sắp hết hạn</button>
                <button wire:click="$set('filterMode', 'low_stock')" class="px-3 py-1 text-[10px] font-black uppercase rounded transition-all {{ $filterMode === 'low_stock' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">Sắp hết tồn</button>
            </div>
            
            <div class="flex gap-2">
                <button wire:click="selectExpiring" class="text-[10px] font-black text-rose-600 hover:underline uppercase tracking-tight">Chọn hết hạn</button>
                <button wire:click="selectLowStock" class="text-[10px] font-black text-amber-600 hover:underline uppercase tracking-tight">Chọn hết tồn</button>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if(count($selectedProducts) > 0)
                <div wire:key="bulk-actions-container" class="flex items-center gap-1.5 pr-2 border-r border-slate-300 mr-1 py-1">
                    <span class="text-[10px] font-black text-indigo-700 bg-indigo-50 px-2 py-1 rounded border border-indigo-100">CHỌN: {{ count($selectedProducts) }}</span>
                    
                    <button wire:key="btn-delete-selected" type="button" 
                            onclick="confirm('Xác nhận xóa các nguyên vật liệu đã chọn?') || event.stopImmediatePropagation()"
                            wire:click="deleteSelected" 
                            wire:loading.attr="disabled"
                            class="flex items-center gap-1 px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-[11px] font-black transition-all hover:scale-105 active:scale-95 shadow-sm cursor-pointer">
                        <span wire:loading.remove wire:target="deleteSelected">🗑️</span>
                        <span wire:loading wire:target="deleteSelected" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        XÓA
                    </button>

                    <button wire:key="btn-print-selected" type="button" 
                            onclick="window.print()" 
                            class="flex items-center gap-1 px-3 py-1.5 bg-slate-800 hover:bg-black text-white rounded-lg text-[11px] font-black transition-all hover:scale-105 active:scale-95 shadow-sm cursor-pointer">
                        <span>🖨️</span>
                        IN
                    </button>
                </div>
            @endif

            <button wire:click="openModal" class="bg-gradient-to-r from-indigo-600 to-indigo-700 font-black hover:from-indigo-700 hover:to-indigo-800 text-white px-4 py-1.5 rounded-lg text-[11px] flex items-center gap-1 transition-all shadow-sm hover:shadow-md active:scale-95">
                <span>➕</span> THÊM NVL MỚI
            </button>
            <button wire:click="$set('showImportModal', true)" class="bg-gradient-to-r from-emerald-600 to-emerald-700 font-black hover:from-emerald-700 hover:to-emerald-800 text-white px-4 py-1.5 rounded-lg text-[11px] transition-all shadow-sm hover:shadow-md active:scale-95">
                📥 IMPORT EXCEL
            </button>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms 
                 class="bg-emerald-50 text-emerald-600 px-6 py-3 rounded-xl shadow-lg border border-emerald-200 font-black text-[13px] flex items-center gap-2 pointer-events-auto">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('message') }}
            </div>
        @endif
        
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.opacity.duration.500ms 
                 class="bg-rose-50 text-rose-600 px-6 py-3 rounded-xl shadow-lg border border-rose-200 font-black text-[13px] flex items-center gap-2 pointer-events-auto">
                <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                {{ session('error') }}
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-800 border-b border-slate-700 text-white uppercase text-[11px] font-black tracking-widest">
                    <th class="px-6 py-4 w-10 no-print text-center">
                        <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $allProductIdsOnPage) }}])" 
                               {{ count($selectedProducts) === count($allProductIdsOnPage) && count($allProductIdsOnPage) > 0 ? 'checked' : '' }}
                               class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500">
                    </th>
                    <th class="px-4 py-4">MÃ NVL</th>
                    <th class="px-4 py-4 w-16 text-center">ẢNH</th>
                    <th class="px-4 py-4">TÊN NGUYÊN VẬT LIỆU</th>
                    <th class="px-4 py-4">HÃNG SX</th>
                    <th class="px-4 py-4">QUY CÁCH</th>
                    <th class="px-4 py-4">ĐVT</th>
                    <th class="px-4 py-4 text-center">SỐ LÔ</th>
                    <th class="px-4 py-4 text-center">TỒN KHO</th>
                    <th class="px-4 py-4">TRẠNG THÁI</th>
                    <th class="px-4 py-4 text-right no-print">THAO TÁC</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr class="hover:bg-indigo-50/30 transition-all border-b border-slate-100 
                        {{ $product->is_expiring_soon ? 'bg-rose-50/50' : ($product->is_low_stock ? 'bg-amber-50/50' : '') }}
                        {{ in_array($product->id, $selectedProducts) ? 'bg-indigo-50' : '' }}">
                        <td class="px-6 py-4 no-print text-center">
                            <input type="checkbox" wire:model.live="selectedProducts" value="{{ $product->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-4 py-4 font-black text-[13px] text-indigo-600 tracking-tight">{{ $product->code }}</td>
                        <td class="px-4 py-4 text-center">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" alt="Img" class="w-12 h-12 object-cover rounded-xl shadow-md border-2 border-white ring-1 ring-slate-200">
                            @else
                                <div class="w-12 h-12 bg-slate-50 flex items-center justify-center rounded-xl border border-slate-200 text-slate-300 text-[10px] font-black uppercase">NO IMG</div>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <div class="font-black text-slate-900 text-[13px] uppercase tracking-tight">{{ $product->name }}</div>
                            <div class="text-[10px] text-slate-400 font-bold mt-0.5">VỊ TRÍ: {{ $product->inventory?->warehouse_location ?? ($product->location ?: 'CHƯA XÁC ĐỊNH') }}</div>
                        </td>
                        <td class="px-4 py-4 text-[12px] font-bold text-slate-600">{{ $product->brand }}</td>
                        <td class="px-4 py-4 text-[11px] font-black text-slate-500 italic bg-slate-50/50 rounded">{{ $product->box_spec }}</td>
                        <td class="px-4 py-4 text-[12px] font-bold text-slate-700 uppercase">{{ $product->unit }}</td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-2.5 py-1 bg-purple-50 text-purple-700 rounded-lg text-[11px] font-black border border-purple-100 shadow-sm">{{ $product->batch_number }}</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span class="px-3 py-1.5 rounded-xl text-[13px] font-black shadow-sm border
                                {{ $product->is_low_stock ? 'bg-rose-600 text-white border-rose-700' : (($product->inventory?->quantity ?? 0) > 0 ? 'bg-indigo-50 text-indigo-700 border-indigo-100' : 'bg-slate-100 text-slate-400 border-slate-200') }}">
                                {{ number_format($product->inventory?->quantity ?? 0) }}
                                @if($product->is_low_stock) ⚠️ @endif
                            </span>
                        </td>
                        <td class="px-4 py-4">
                            @if($product->status === 'active')
                                <span class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border border-emerald-100">ĐANG HOẠT ĐỘNG</span>
                            @else
                                <span class="bg-rose-50 text-rose-700 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase border border-rose-100">NGỪNG SỬ DỤNG</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right no-print">
                            <div class="flex items-center justify-end gap-1">
                                <button wire:click="openModal({{ $product->id }})" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="Sửa">📝</button>
                                <button wire:confirm="Xoá nguyên vật liệu này?" wire:click="delete({{ $product->id }})" class="p-2 text-rose-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Xoá">🗑️</button>
                            </div>
                        </td>
                    </tr>
                @empty
                     <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">Không tìm thấy sản phẩm nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ $isEdit ? 'Chỉnh sửa NVL' : 'Thêm NVL mới' }}</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã NVL</label>
                                <input type="text" wire:model="code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tên NVL</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2 hidden">
                                <input type="hidden" wire:model="type" value="material">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Hình ảnh</label>
                                <input type="file" wire:model="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @error('image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hãng sản xuất</label>
                                <input type="text" wire:model="brand" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('brand') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Số lượng</label>
                                <input type="number" wire:model="quantity" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">QC Hộp (loại chai/hộp...)</label>
                                <input type="text" wire:model="box_spec" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="VD: Chai 500ml">
                                @error('box_spec') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">ĐV tính (g/kg/ml/L)</label>
                                <input type="text" wire:model="unit" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="VD: kg, g, ml, L...">
                                @error('unit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                             <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Vị trí</label>
                                <input type="text" wire:model="location" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Số lô <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="batch_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('batch_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hạn sử dụng</label>
                                <input type="date" wire:model="expiry_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('expiry_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tồn tối thiểu</label>
                                <input type="number" wire:model="min_stock" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('min_stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="save" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Lưu lại
                        </button>
                        <button type="button" wire:click="$set('showModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Huỷ
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
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Nhập dữ liệu từ Excel</h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-2">Tải tệp Excel (.xlsx, .xls) hoặc CSV để nhập hàng loạt sản phẩm.</p>
                            <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium underline">Tải tệp mẫu (.xlsx) tại đây</a>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Chọn tệp của bạn</label>
                            <input type="file" wire:model="excelFile" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('excelFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div wire:loading wire:target="excelFile" class="mt-2 text-sm text-blue-600">
                            Đang tải tệp lên...
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="importExcel" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Bắt đầu nhập
                        </button>
                        <button type="button" wire:click="$set('showImportModal', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        @media print {
            .no-print, 
            header, 
            nav, 
            aside, 
            .sidebar, 
            .flex.justify-between.items-center.mb-4,
            .flex.items-center.gap-4.mb-4,
            .px-4.py-3.bg-gray-50.border-t {
                display: none !important;
            }
            .bg-white { background-color: transparent !important; }
            .shadow-sm { box-shadow: none !important; }
            .border { border: 1px solid #ddd !important; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #ddd !important; padding: 8px !important; }
            tr:not(.ring-2) { display: none !important; }
            tr.ring-2 { display: table-row !important; }
            @page { size: landscape; margin: 1cm; }
        }
    </style>
</div>

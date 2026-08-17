<div style="font-family: 'Times New Roman', Times, serif;" 
     x-data="{ showLightbox: false, lightboxUrl: '' }"
     @confirm-duplicate.window="if(confirm('Bạn có thông tin nhập giống nhau. Bạn chắc nhập thêm không?')) { $wire.confirmSave() }">
    <div>
        <!-- Lightbox Modal -->
        <div x-show="showLightbox"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[100] flex items-center justify-center bg-black/90 p-2 no-print"
             style="display: none;"
             @click="showLightbox = false"
             @keydown.escape.window="showLightbox = false">
            <div class="relative max-w-5xl w-full flex flex-col items-center">
                <button @click="showLightbox = false" class="absolute -top-12 right-0 text-white hover:text-gray-300 text-4xl font-black transition-all">✕</button>
                <img :src="lightboxUrl" class="max-h-[85vh] max-w-full rounded-lg shadow-2xl border-4 border-white object-contain bg-white/10">
                <div class="mt-4 text-white font-black text-sm uppercase tracking-widest bg-black/50 px-4 py-2 rounded-full">Bấm bên ngoài hoặc phím ESC để thoát</div>
            </div>
        </div>


    <div class="flex flex-wrap items-center justify-between gap-2 mb-4 no-print relative z-10 bg-white p-3 rounded-xl shadow-sm border border-slate-200">
        <!-- Tabs -->
        <div class="flex bg-slate-100 p-1 rounded-lg w-full mb-2">
            <button wire:click="switchTab('materials')" class="flex-1 py-2 text-sm font-bold uppercase rounded-md transition-all {{ $activeTab === 'materials' ? 'bg-white text-indigo-600 shadow' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                Danh Mục Vật Tư
            </button>
            <button wire:click="switchTab('equipments')" class="flex-1 py-2 text-sm font-bold uppercase rounded-md transition-all {{ $activeTab === 'equipments' ? 'bg-white text-indigo-600 shadow' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                Danh Mục Thiết Bị
            </button>
        </div>
        
        <div class="flex items-center gap-2 w-full overflow-x-auto pb-1 hide-scrollbar">


            <!-- Search Standard -->
            <div class="relative w-64 h-[38px] shrink-0">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm tên, mã, hãng..." class="w-full h-full pl-9 pr-3 py-2 text-[12px] font-bold rounded-lg border border-slate-200 focus:ring-indigo-500 shadow-sm transition-all bg-slate-50 focus:bg-white">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div class="w-px h-6 bg-slate-200 mx-1 shrink-0"></div>

            @if(count($selectedIds) > 0)
                <div wire:key="bulk-actions-container" class="flex items-center gap-1.5 pr-2 border-r border-slate-300 mr-1 h-[38px] shrink-0">
                    <span class="text-[10px] font-black text-indigo-700 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 flex items-center h-full">CHỌN: {{ count($selectedIds) }}</span>
                    
                    @if(count($selectedIds) === 1)
                        <button wire:key="btn-edit-selected" type="button" 
                                wire:click="openModal({{ $selectedIds[0] }})" 
                                class="flex items-center justify-center gap-1 px-3 h-full bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-[11px] font-black transition-all hover:scale-105 active:scale-95 shadow-sm cursor-pointer">
                            <span>✏️</span> SỬA
                        </button>
                    @endif

                    <button wire:key="btn-delete-selected" type="button" 
                            onclick="confirm('Xác nhận xóa các vật tư đã chọn?') || event.stopImmediatePropagation()"
                            wire:click="deleteSelected" 
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center gap-1 px-3 h-full bg-rose-500 hover:bg-rose-600 text-white rounded-lg text-[11px] font-black transition-all hover:scale-105 active:scale-95 shadow-sm cursor-pointer">
                        <span wire:loading.remove wire:target="deleteSelected">🗑️</span>
                        <span wire:loading wire:target="deleteSelected" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        XÓA
                    </button>

                    <button wire:key="btn-print-selected" type="button" 
                            wire:click="printLabels" 
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center gap-1 px-3 h-full bg-slate-800 hover:bg-black text-white rounded-lg text-[11px] font-black transition-all hover:scale-105 active:scale-95 shadow-sm cursor-pointer">
                        <span wire:loading.remove wire:target="printLabels">📄</span>
                        <span wire:loading wire:target="printLabels" class="w-3 h-3 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        IN
                    </button>
                </div>
            @endif

            @if($activeTab === 'materials')
            <div class="flex gap-1 bg-slate-100 p-1 rounded-lg border border-slate-300 h-[38px] shrink-0">
                <button type="button"
                        wire:click="setFilterMode('all')"
                        class="px-3 h-full text-[11px] font-black uppercase rounded {{ $filterMode === 'all' ? 'bg-blue-600 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
                    Tất cả
                </button>
                <button type="button"
                        wire:click="setFilterMode('low_stock')"
                        class="px-3 h-full text-[11px] font-black uppercase rounded {{ $filterMode === 'low_stock' ? 'bg-orange-600 text-white shadow' : 'bg-white text-slate-600 hover:bg-slate-50' }}">
                    Sắp hết tồn
                </button>
            </div>
            @endif

            <button type="button" wire:click="openModal" class="h-[38px] shrink-0 bg-gradient-to-r from-blue-600 to-blue-700 font-black hover:from-blue-700 hover:to-blue-800 text-white px-3 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md active:scale-95 uppercase">
                <span>➕</span> THÊM MỚI
            </button>
            <button type="button" wire:click="$set('showImportModal', true)" wire:loading.attr="disabled" class="h-[38px] shrink-0 bg-gradient-to-r from-emerald-600 to-emerald-700 font-black hover:from-emerald-700 hover:to-emerald-800 text-white px-3 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md active:scale-95 uppercase">
                <span>📥</span> IMPORT EXCEL
            </button>
            <button type="button" wire:click="printAll" wire:loading.attr="disabled" class="h-[38px] shrink-0 bg-gradient-to-r from-slate-800 to-slate-900 font-black hover:from-indigo-600 hover:to-indigo-700 text-white px-3 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md active:scale-95 uppercase">
                <span>🖨️</span> IN FILE PDF
            </button>
            @if($activeTab === 'materials')
            <button type="button" wire:click="saveMinStocks" wire:loading.attr="disabled" 
                    x-data="{ saved: false }" 
                    @min-stocks-saved.window="saved = true; setTimeout(() => saved = false, 3000)"
                    class="h-[38px] shrink-0 bg-gradient-to-r from-amber-500 to-amber-600 font-black hover:from-amber-600 hover:to-amber-700 text-white px-3 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md active:scale-95 uppercase">
                <span x-show="!saved">💾</span> 
                <span x-show="!saved">LƯU TỒN TỐI THIỂU</span>
                <span x-cloak x-show="saved" class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    ĐÃ LƯU
                </span>
            </button>
            <button type="button" wire:click="saveMaxStocks" wire:loading.attr="disabled" 
                    x-data="{ saved: false }" 
                    @max-stocks-saved.window="saved = true; setTimeout(() => saved = false, 3000)"
                    class="h-[38px] shrink-0 bg-gradient-to-r from-rose-500 to-rose-600 font-black hover:from-rose-600 hover:to-rose-700 text-white px-3 rounded-lg text-[11px] flex items-center justify-center gap-1.5 transition-all shadow-sm hover:shadow-md active:scale-95 uppercase">
                <span x-show="!saved">💾</span> 
                <span x-show="!saved">LƯU TỒN TỐI ĐA</span>
                <span x-cloak x-show="saved" class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    ĐÃ LƯU
                </span>
            </button>
            @endif
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="fixed top-2 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.opacity.duration.500ms
                 class="bg-emerald-50 text-emerald-600 px-6 py-3 rounded-xl shadow-lg border border-emerald-200 font-black text-[13px] flex items-center gap-2 pointer-events-auto">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('warning'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition.opacity.duration.500ms
                 class="bg-amber-50 text-amber-600 px-6 py-3 rounded-xl shadow-lg border border-amber-200 font-black text-[13px] flex items-center gap-2 pointer-events-auto">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                {{ session('warning') }}
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

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border">
        @if($activeTab === 'materials')
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-black tracking-widest">
                    <th class="px-2 py-2 w-10 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll" 
                               {{ count($selectedIds) === ($activeTab === 'materials' ? $products->total() : $equipments->total()) && ($activeTab === 'materials' ? $products->total() : $equipments->total()) > 0 ? 'checked' : '' }}
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-3 py-4 text-[11px] font-black uppercase tracking-tighter text-slate-500">Mã vật tư</th>
                    <th class="px-2 py-2 w-16 text-center">Hình ảnh</th>
                    <th class="px-2 py-2">TÊN VẬT TƯ</th>
                    <th class="px-2 py-2">Phân loại</th>
                    <th class="px-2 py-2">Hãng sản xuất</th>
                    <th class="px-2 py-2">Mã Code NCC</th>
                    <th class="px-2 py-2">Hạn dùng</th>
                    <th class="px-2 py-2 text-center">Số lượng</th>
                    <th class="px-2 py-2">Vị trí</th>
                    <th class="px-2 py-2">Tình trạng</th>
                    <th class="px-2 py-2">Tồn tối thiểu</th>
                    <th class="px-2 py-2 text-rose-600">Tồn tối đa</th>
                    <th class="px-2 py-2">Ghi chú</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr wire:key="product-{{ $product->id }}" class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors {{ in_array((string)$product->id, $selectedIds) ? 'bg-indigo-50/50' : '' }}">
                        <td class="px-2 py-1.5 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $product->id }}" class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </td>
                        <td class="px-2 py-1.5 font-mono text-sm text-blue-600">{{ $product->code }}</td>
                        <td class="px-2 py-1.5 text-center">
                            @if($product->image)
                                <div class="relative group inline-block">
                                    <img src="{{ asset('storage/' . $product->image) }}" 
                                         alt="Img" 
                                         class="w-12 h-12 object-cover rounded shadow-sm border cursor-zoom-in transition-transform hover:scale-110" 
                                         @click="lightboxUrl = '{{ asset('storage/' . $product->image) }}'; showLightbox = true">
                                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 flex items-center justify-center rounded transition-opacity pointer-events-none">
                                        <span class="text-white text-[10px]">🔍</span>
                                    </div>
                                </div>
                            @else
                                <button wire:click="openModal({{ $product->id }})" class="text-[10px] font-black text-indigo-600 hover:text-indigo-800 uppercase underline decoration-2 underline-offset-4">
                                    Tải ảnh
                                </button>
                            @endif
                        </td>
                        <td class="px-2 py-1.5 text-[11px] font-black text-gray-800 uppercase tracking-tight">
                            {{ $product->name }}
                            @if($product->category)
                                <div class="text-[9px] text-gray-500 mt-1 uppercase">{{ $product->category->name }}</div>
                            @endif
                        </td>
                        <td class="px-2 py-1.5">
                            @if($product->type === 'product_produced')
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs">SX</span>
                            @elseif($product->type === 'product_purchased')
                                <span class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-xs">Mua</span>
                            @endif
                        </td>
                        <td class="px-2 py-1.5 text-gray-600">{{ $product->brand }}</td>
                        <td class="px-2 py-1.5 text-sm font-semibold text-purple-700">{{ $product->batch_number }}</td>
                        <td class="px-2 py-1.5 text-sm {{ $product->is_expiring_soon ? 'text-red-600 font-bold' : 'text-gray-600' }}">
                            {{ $product->expiry_date ? $product->expiry_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-2 py-1.5 text-center">
                            <span class="px-1.5 py-1 text-[11px] rounded-full text-xs font-bold 
                                {{ $product->is_low_stock ? 'bg-orange-600 text-white' : (($product->inventory?->quantity ?? 0) > 0 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                                {{ number_format($product->inventory?->quantity ?? 0) }}
                                @if($product->is_low_stock) ⚠️ @endif
                            </span>
                        </td>
                        <td class="px-2 py-1.5 text-gray-600 font-bold">{{ $product->inventory?->warehouse_location ?? $product->location }}</td>
                        <td class="px-2 py-1.5">
                            @if($product->status === 'active')
                                <span class="bg-green-100 text-green-700 px-1.5 py-1 text-[11px] rounded text-xs">Đang kinh doanh</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-1.5 py-1 text-[11px] rounded text-xs">Ngừng kinh doanh</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-center">
                            <input type="number" 
                                   wire:model.defer="minStocks.{{ $product->id }}"
                                   class="w-20 text-xs font-black text-slate-800 bg-slate-50 border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 rounded px-1.5 py-1 text-[11px] transition-all text-center shadow-inner placeholder-slate-400"
                                   placeholder="0">
                        </td>
                        <td class="px-4 py-2 text-center">
                            <input type="number" 
                                   wire:model.defer="maxStocks.{{ $product->id }}"
                                   class="w-20 text-xs font-black text-slate-800 bg-slate-50 border border-slate-200 focus:border-rose-500 focus:ring-1 focus:ring-rose-500 rounded px-1.5 py-1 text-[11px] transition-all text-center shadow-inner placeholder-slate-400"
                                   placeholder="0">
                        </td>
                        <td class="px-2 py-1.5 text-xs text-gray-600 truncate max-w-[150px]" title="{{ $product->description }}">{{ $product->description }}</td>
                    </tr>
                @empty
                     <tr>
                        <td colspan="11" class="px-4 py-8 text-center text-gray-500">Không tìm thấy vật tư nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $products->links() }}
        </div>
        @else
        <!-- Bảng thiết bị -->
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-black tracking-widest">
                    <th class="px-2 py-2 w-10 text-center no-print">
                        <input type="checkbox" wire:click="toggleSelectAll" 
                               {{ count($selectedIds) === ($activeTab === 'materials' ? $products->total() : $equipments->total()) && ($activeTab === 'materials' ? $products->total() : $equipments->total()) > 0 ? 'checked' : '' }}
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    </th>
                    <th class="px-2 py-2">MÃ THIẾT BỊ</th>
                    <th class="px-2 py-2">TÊN THIẾT BỊ</th>
                    <th class="px-2 py-2">LOẠI THIẾT BỊ</th>
                    <th class="px-2 py-2">MÃ TÀI SẢN</th>
                    <th class="px-2 py-2">NGƯỜI QUẢN LÝ</th>
                    <th class="px-2 py-2">TÌNH TRẠNG</th>
                    <th class="px-2 py-2 text-center w-24">THAO TÁC</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($equipments as $index => $equipment)
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors {{ in_array($equipment->id, $selectedIds) ? 'bg-indigo-50/30' : '' }}">
                        <td class="px-2 py-1.5 text-center no-print">
                            <input type="checkbox" value="{{ $equipment->id }}" wire:model="selectedIds" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </td>
                        <td class="px-2 py-1.5 text-sm font-mono text-indigo-600">{{ $equipment->equipment_code ?: '-' }}</td>
                        <td class="px-2 py-1.5 text-sm font-black text-gray-800 uppercase">{{ $equipment->name }}</td>
                        <td class="px-2 py-1.5 text-sm font-bold text-gray-700 uppercase">{{ $equipment->machine_type ?: '-' }}</td>
                        <td class="px-2 py-1.5 text-sm font-mono text-indigo-600">{{ $equipment->asset_code ?: '-' }}</td>
                        <td class="px-2 py-1.5 text-sm text-gray-600 font-bold uppercase">{{ $equipment->manager ?: '-' }}</td>
                        <td class="px-2 py-1.5 text-sm text-gray-600 font-bold uppercase">
                            @if($equipment->warranty_status === 'Còn bảo hành')
                                <span class="bg-green-100 text-green-800 px-1.5 py-1 text-[11px] rounded text-[10px]">{{ $equipment->warranty_status }}</span>
                            @else
                                <span class="bg-red-100 text-red-800 px-1.5 py-1 text-[11px] rounded text-[10px]">{{ $equipment->warranty_status ?: 'Hết bảo hành' }}</span>
                            @endif
                        </td>
                        <td class="px-2 py-1.5 text-center">
                            <button wire:click="openModal({{ $equipment->id }})" class="text-indigo-600 hover:text-indigo-800 font-black uppercase text-[10px] bg-indigo-50 px-1.5 py-1 text-[11px].5 rounded hover:bg-indigo-100 transition-colors">
                                Sửa
                            </button>
                            <button wire:click="deleteEquipment({{ $equipment->id }})" wire:confirm="Bạn có chắc chắn muốn xóa thiết bị này?" class="text-rose-600 hover:text-rose-800 font-black uppercase text-[10px] bg-rose-50 px-1.5 py-1 text-[11px].5 rounded hover:bg-rose-100 transition-colors ml-1 mt-1 sm:mt-0">
                                Xóa
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Không tìm thấy thiết bị nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $equipments->links() }}
        </div>
        @endif
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-y-auto max-h-[85vh] shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ $activeTab === 'materials' ? ($isEdit ? 'Chỉnh sửa vật tư' : 'Thêm vật tư mới') : ($isEdit ? 'Chỉnh sửa thiết bị' : 'Thêm thiết bị mới') }}</h3>
                        <div class="grid grid-cols-2 gap-2">
                            @if($activeTab === 'materials')
                            <div class="col-span-2 bg-indigo-50/50 p-2 rounded-xl border border-indigo-100 mb-2">
                                <label class="block text-[11px] font-black text-indigo-900 uppercase tracking-widest mb-3">📸 Hình ảnh vật tư</label>
                                <div class="flex items-center gap-2">
                                    <div class="relative group">
                                        @if ($image)
                                            <img src="{{ $image->temporaryUrl() }}" class="h-24 w-24 object-cover rounded-xl border-2 border-white shadow-md ring-2 ring-indigo-200">
                                            <button wire:click="$set('image', null)" class="absolute -top-2 -right-2 bg-rose-500 text-white rounded-full p-1 shadow-lg hover:bg-rose-600 transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        @elseif ($productId && ($currentProduct = \App\Models\Product::find($productId)) && $currentProduct->image)
                                            <img src="{{ asset('storage/' . $currentProduct->image) }}" class="h-24 w-24 object-cover rounded-xl border-2 border-white shadow-md ring-2 ring-slate-200">
                                        @else
                                            <div class="h-24 w-24 bg-white border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center text-slate-400 group-hover:border-indigo-400 group-hover:text-indigo-400 transition-all">
                                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <span class="text-[9px] font-black mt-1 uppercase">Chưa có ảnh</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="relative">
                                            <input type="file" wire:model="image" id="image-upload" class="hidden">
                                            <label for="image-upload" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-indigo-200 rounded-lg shadow-sm text-xs font-black text-indigo-700 hover:bg-indigo-50 cursor-pointer transition-all active:scale-95">
                                                📂 CHỌN ẢNH TỪ MÁY
                                            </label>
                                        </div>
                                        <p class="mt-2 text-[10px] text-slate-500 font-bold leading-tight uppercase">
                                            Hỗ trợ: JPG, PNG, WEBP<br>Dung lượng tối đa: 2MB
                                        </p>
                                        <div wire:loading wire:target="image" class="mt-2 text-[10px] font-black text-indigo-600 animate-pulse">
                                            ĐANG TẢI...
                                        </div>
                                    </div>
                                </div>
                                @error('image') <span class="text-rose-500 text-[10px] font-bold mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã vật tư</label>
                                <input type="text" wire:model="code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tên vật tư</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Danh mục</label>
                                <select wire:model="category_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                    <option value="">-- Chọn danh mục --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2 flex gap-2 my-2">
                                <label class="block text-sm font-medium text-gray-700 items-center flex">Phân loại gốc:</label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="type" value="product_produced" class="text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Thành phẩm (SX)</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" wire:model="type" value="product_purchased" class="text-blue-600">
                                    <span class="ml-2 text-sm text-gray-700">Thành phẩm (Mua)</span>
                                </label>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hãng sản xuất</label>
                                <input type="text" wire:model="brand" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('brand') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Số lượng</label>
                                <input type="text" inputmode="numeric" wire:model.lazy="quantity" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="0">
                                @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Vị trí</label>
                                <input type="text" wire:model="location" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('location') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã Code NCC (Không bắt buộc)</label>
                                <input type="text" wire:model="batch_number" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('batch_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Hạn sử dụng</label>
                                <input type="date" wire:model="expiry_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('expiry_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                                <textarea wire:model="description" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" rows="2" placeholder="Thêm ghi chú..."></textarea>
                                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tồn tối thiểu</label>
                                <input type="text" inputmode="numeric" wire:model.lazy="min_stock" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="0">
                                @error('min_stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-rose-700">Tồn tối đa</label>
                                <input type="text" inputmode="numeric" wire:model.lazy="max_stock" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 border-rose-200" placeholder="0">
                                @error('max_stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            @else
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã tài sản</label>
                                <input type="text" wire:model="asset_code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('asset_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tên thiết bị</label>
                                <input type="text" wire:model="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Mã thiết bị</label>
                                <input type="text" wire:model="equipment_code" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('equipment_code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Loại thiết bị</label>
                                <input type="text" wire:model="machine_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('machine_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Người quản lý</label>
                                <input type="text" wire:model="manager" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                @error('manager') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700">Tình trạng</label>
                                <select wire:model="warranty_status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-white">
                                    <option value="Còn bảo hành">Còn bảo hành</option>
                                    <option value="Hết bảo hành">Hết bảo hành</option>
                                </select>
                                @error('warranty_status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            @endif

                            @if ($errors->any())
                                <div class="col-span-2 bg-rose-50 p-3 rounded-lg border border-rose-200 mt-2">
                                    <ul class="list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li class="text-rose-600 text-[11px] font-black uppercase">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row-reverse gap-2">
                        <button type="button" wire:click="save" wire:loading.attr="disabled" 
                                class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-sm font-black text-white hover:bg-indigo-700 focus:outline-none transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">💾 LƯU LẠI</span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                ĐANG LƯU...
                            </span>
                        </button>
                        <button type="button" wire:click="$set('showModal', false)" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-2.5 bg-white text-sm font-black text-gray-700 hover:bg-gray-50 focus:outline-none transition-all">
                            HỦY BỎ
                        </button>
                        
                        <div wire:loading wire:target="save" class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center z-50 rounded-lg">
                            <div class="bg-white p-2 rounded-2xl shadow-xl border border-indigo-100 flex flex-col items-center gap-3">
                                <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                                <span class="text-sm font-black text-indigo-900 uppercase">Đang xử lý dữ liệu...</span>
                            </div>
                        </div>
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
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Nhập dữ liệu từ Excel</h3>
                        
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-2">Tải tệp Excel (.xlsx, .xls) hoặc CSV để nhập hàng loạt vật tư.</p>
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
            .no-print, header, nav, aside, .sidebar, .sidebar-toolbar,
            .bg-white.p-2.rounded-2xl, .bg-white.rounded-xl.shadow-sm,
            .px-4.py-3.bg-gray-50.border-t, button, input, select {
                display: none !important;
            }
            body {
                background: white !important;
                font-family: 'Times New Roman', Times, serif !important;
                padding: 0 !important;
                margin: 0 !important;
                color: black !important;
            }
            main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            .print-only { display: block !important; }
            .print-container { width: 100%; padding: 15mm; }
            table.print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 15px;
            }
            table.print-table th, table.print-table td {
                border: 1.5px solid black !important;
                padding: 6px 8px !important;
                font-size: 13px !important;
                color: black !important;
            }
            table.print-table th {
                background-color: #e5e7eb !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 12px !important;
            }
            @page { size: A4 portrait; margin: 0; }
            body { padding: 15mm; }
        }
        .print-only { display: none; }
    </style>

    <!-- PHIẾU IN DANH MỤC VẬT TƯ -->
    <div class="print-only print-container" style="font-family: 'Times New Roman', Times, serif;">
        <!-- Header công ty -->
        <div style="margin-bottom: 20px;">
            <h1 style="font-size: 20px; font-weight: bold; text-transform: uppercase; margin: 0;">KHO KỸ THUẬT SỮA CHỮA VINALPHA</h1>
            <p style="font-size: 16px; font-weight: bold; margin: 4px 0;">DỰ ÁN: {{ \App\Models\Project::find(auth()->user()->current_house_id)->name ?? '...' }}</p>
        </div>

        <div style="border-bottom: 2px solid black; margin-bottom: 20px;"></div>

        <!-- Tiêu đề phiếu -->
        <div style="text-align: center; margin-bottom: 25px;">
            <h2 style="font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 3px; margin: 0;">{{ $activeTab === 'materials' ? 'BẢNG DANH MỤC VẬT TƯ' : 'BẢNG DANH MỤC THIẾT BỊ' }}</h2>
            <p style="font-size: 13px; font-style: italic; margin-top: 6px;">
                Ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}
            </p>
        </div>

        <!-- Bảng sản phẩm / thiết bị -->
        <table class="print-table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">STT</th>
                    @if($activeTab === 'materials')
                    <th style="width: 110px;">Mã Vật Tư</th>
                    <th>TÊN VẬT TƯ</th>
                    <th style="width: 130px;">Hãng SX</th>
                    <th style="width: 80px; text-align: center;">Số lượng</th>
                    <th style="width: 100px; text-align: center;">Vị trí</th>
                    @else
                    <th>TÊN THIẾT BỊ</th>
                    <th style="width: 110px;">Mã Tài Sản</th>
                    <th style="width: 100px;">Biển Số Xe</th>
                    <th style="width: 130px;">Tên Tài Xế</th>
                    <th style="width: 100px;">SĐT</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($printItems as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    @if($activeTab === 'materials')
                    <td style="font-family: monospace; text-transform: uppercase;">{{ $item->code }}</td>
                    <td style="font-weight: bold;">{{ $item->name }}</td>
                    <td>{{ $item->brand ?? '-' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ number_format($item->inventory?->quantity ?? 0) }}</td>
                    <td style="text-align: center;">{{ $item->location ?? '-' }}</td>
                    @else
                    <td style="font-weight: bold; text-transform: uppercase;">{{ $item->name }}</td>
                    <td style="font-family: monospace; text-transform: uppercase;">{{ $item->asset_code }}</td>
                    <td style="text-transform: uppercase;">{{ $item->license_plate ?? '-' }}</td>
                    <td style="text-transform: uppercase;">{{ $item->driver_name ?? '-' }}</td>
                    <td style="font-family: monospace;">{{ $item->phone_number ?? '-' }}</td>
                    @endif
                </tr>
                @endforeach
                {{-- Thêm hàng trống nếu ít hơn 8 dòng --}}
                @for($i = count($printItems); $i < max(8, count($printItems)); $i++)
                <tr>
                    <td style="text-align: center; color: transparent;">_</td>
                    <td></td><td></td><td></td><td></td>
                    @if($activeTab === 'materials')<td></td>@endif
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Chữ ký -->
        <div style="display: flex; justify-content: space-between; margin-top: 50px; text-align: center; font-size: 14px;">
            <div style="width: 45%;">
                <p style="font-weight: bold; text-transform: uppercase;">Người lập phiếu</p>
                <p style="font-size: 11px; font-style: italic;">(Ký, ghi rõ họ tên)</p>
                <div style="height: 80px;"></div>
                <p style="font-weight: bold; text-transform: uppercase;">{{ auth()->user()->name ?? '........................' }}</p>
            </div>
            <div style="width: 45%;">
                <p style="font-weight: bold; text-transform: uppercase;">Quản lý kho</p>
                <p style="font-size: 11px; font-style: italic;">(Ký, ghi rõ họ tên)</p>
                <div style="height: 80px;"></div>
                <p style="font-weight: bold;">.................................</p>
            </div>
        </div>

        <div style="text-align: right; margin-top: 30px; font-size: 10px; font-style: italic; color: #666;">
            In lúc: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    @script
    <script>
        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
    @endscript
</div>
</div>

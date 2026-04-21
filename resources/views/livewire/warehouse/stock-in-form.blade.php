<div class="h-full flex flex-col space-y-4">
    <!-- Tab Navigation -->
    <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-2 w-fit no-print">
        <button wire:click="$set('activeTab', 'form')" class="px-6 py-2.5 rounded-xl text-sm font-black transition-all flex items-center gap-2 {{ $activeTab === 'form' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50' }}">
            <span>📥</span> LẬP PHIẾU NHẬP
        </button>
        <button wire:click="$set('activeTab', 'list')" class="px-6 py-2.5 rounded-xl text-sm font-black transition-all flex items-center gap-2 {{ $activeTab === 'list' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50' }}">
            <span>📋</span> DANH SÁCH PHIẾU
        </button>
    </div>

    <div class="flex-1 main-content">
        @if($activeTab === 'form')
            @if(session('success'))
                <div class="mb-4 p-4 bg-emerald-100 text-emerald-800 rounded-2xl font-bold flex items-center gap-2 border border-emerald-200 animate-in fade-in slide-in-from-top-2">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                        <span class="p-2 bg-indigo-100 text-indigo-600 rounded-lg shadow-sm">📥</span>
                        Phiếu nhập kho mới
                    </h2>
                </div>
                
                <div class="p-6">

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nhà cung cấp</label>
                <input type="text" wire:model="supplier_name" list="suppliers_list" class="w-full rounded-lg border-gray-300 shadow-sm" placeholder="Chọn hoặc nhập tên...">
                <datalist id="suppliers_list">
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->name }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hãng sản xuất</label>
                <input type="text" wire:model="manufacturer" list="brands_list" class="w-full rounded-lg border-gray-300 shadow-sm" placeholder="Nhập hãng SX...">
                <datalist id="brands_list">
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Loại nhập</label>
                <select wire:model.live="type" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="purchase_produced">Nhập mua hàng TP</option>
                    <option value="return_produced">Nhập trả hàng TP</option>
                    <option value="production">Nhập từ sản xuất</option>
                    <option value="import_material">Nhập nguyên vật liệu</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto mb-4">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 min-w-[200px]">Sản phẩm</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-24">Số lô</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-32">Hạn dùng</th>
                                    <th class="px-2 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-24">Vị trí</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-20">SL</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-16">ĐVT</th>
                                    <th class="px-2 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-24">Đơn giá</th>
                                    <th class="px-2 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-14">VAT</th>
                                    <th class="px-2 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-200 w-32">Thành tiền</th>
                                    <th class="px-2 py-3 border-b border-gray-200 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model.live.debounce.250ms="items.{{ $index }}.product_search" list="product_list_{{ $index }}" 
                                               class="w-full rounded-lg border-gray-300 text-xs font-semibold focus:ring-indigo-500 focus:border-indigo-500 transition"
                                               placeholder="Mã hoặc tên SP...">
                                        <datalist id="product_list_{{ $index }}">
                                            @foreach($products as $product)
                                                <option value="{{ $product->code }} - {{ $product->name }}"></option>
                                            @endforeach
                                        </datalist>
                                        @error("items.{$index}.product_id") <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model.live="items.{{ $index }}.batch_number" 
                                               class="w-full rounded-lg border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Số lô...">
                                        @error("items.{$index}.batch_number") <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="date" wire:model="items.{{ $index }}.expiry_date" 
                                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model="items.{{ $index }}.warehouse_location" 
                                               class="w-full text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="Vị trí...">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="number" wire:model.live="items.{{ $index }}.quantity" step="0.0001" min="0"
                                               class="w-full text-center text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                        @error("items.{$index}.quantity") <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="text" wire:model="items.{{ $index }}.unit" 
                                               class="w-full text-center text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="...">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="number" wire:model.live="items.{{ $index }}.unit_price" step="0.01" min="0"
                                               class="w-full text-right text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-4">
                                        <input type="number" wire:model.live="items.{{ $index }}.vat_rate" step="0.1" min="0"
                                               class="w-full text-center text-xs rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 transition">
                                    </td>
                                    <td class="px-2 py-4 text-right font-bold text-indigo-700">
                                        {{ number_format($items[$index]['total_amount'] ?? 0) }} đ
                                    </td>
                                    <td class="px-2 py-4 text-center">
                                        @if(count($items) > 1)
                                            <button wire:click="removeItem({{ $index }})" class="text-gray-400 hover:text-red-500 transition p-1 rounded-full hover:bg-red-50">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if(count($items) > 0)
                            <tfoot>
                                <tr class="bg-indigo-50/30">
                                    <td colspan="8" class="px-2 py-3 text-right font-bold text-gray-700 uppercase tracking-wider text-xs">Tổng cộng:</td>
                                    <td class="px-2 py-3 text-right font-extrabold text-indigo-800 text-sm">
                                        {{ number_format(collect($items)->sum('total_amount')) }} đ
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

        <div class="flex items-center gap-4 mb-4">
            @if($this->canAddItem())
                <button wire:click="addItem" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm flex items-center gap-1 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="12 4v16m8-8H4"></path></svg>
                    Thêm dòng
                </button>
            @else
                <button disabled class="text-gray-400 cursor-not-allowed text-sm flex items-center gap-1" title="Vui lòng nhập đủ thông tin dòng hiện tại trước khi thêm dòng mới">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="12 4v16m8-8H4"></path></svg>
                    Thêm dòng (Chưa hoàn tất dòng trên)
                </button>
            @endif

            <button wire:click="openProductModal" class="text-green-600 hover:text-green-800 font-medium text-sm flex items-center gap-1 transition ml-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                ➕ Thêm sản phẩm mới
            </button>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Ghi chú</label>
            <textarea wire:model="note" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
        </div>

                </div>
            </div>
        @elseif($activeTab === 'list')
            @if(session('success'))
                <div class="mb-4 p-4 bg-emerald-100 text-emerald-800 rounded-2xl font-bold flex items-center gap-2 border border-emerald-200 animate-in fade-in">
                    <span>✅</span> {{ session('success') }}
                </div>
            @endif

            <!-- Standard List Toolbar -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden min-h-[600px] main-content">
                <!-- Print Title -->
                <div class="hidden print:block text-center mb-8">
                    <h1 class="text-2xl font-black uppercase">Danh sách phiếu nhập kho</h1>
                    <p class="text-sm font-bold mt-1">Từ ngày: {{ \Carbon\Carbon::parse($listDateFrom)->format('d/m/Y') }} - Đến ngày: {{ \Carbon\Carbon::parse($listDateTo)->format('d/m/Y') }}</p>
                </div>

                <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 no-print">
                    <h2 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                        <span class="p-2 bg-indigo-100 text-indigo-600 rounded-lg">📋</span>
                        Lịch sử nhập kho
                    </h2>
                    
                    <div class="flex flex-wrap items-center gap-3 no-print">
                        <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:ring-2 focus-within:ring-indigo-100">
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Từ ngày</label>
                                <input type="date" wire:model.live="listDateFrom" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                            </div>
                            <div class="w-px h-4 bg-slate-200 mx-1"></div>
                            <div class="flex items-center gap-2">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Đến ngày</label>
                                <input type="date" wire:model.live="listDateTo" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                            </div>
                        </div>

                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="listSearch" placeholder="Tìm mã, NCC..." class="pl-9 pr-4 py-2 w-56 text-xs font-bold rounded-xl border-slate-200 focus:ring-indigo-500 shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 ml-2">
                            @if(count($selectedIds) > 0)
                                <div class="flex items-center gap-2 pr-2 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4">
                                    <span class="text-[10px] font-black text-indigo-600 bg-indigo-50 px-2 py-1 rounded">Đã chọn: {{ count($selectedIds) }}</span>
                                    <button wire:click="deleteSelected" wire:confirm="Xóa {{ count($selectedIds) }} phiếu nhập và GIẢM TRỪ tồn kho?" class="flex items-center gap-1 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-black transition">
                                        <span>🗑️</span> XÓA
                                    </button>
                                </div>
                            @endif
                                    <button wire:click="printSelected" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white rounded-xl text-xs font-black transition shadow-sm">
                                        <span class="text-sm">🖨️</span> IN GHÉP
                                    </button>
                                    <button wire:click="exportExcel" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition shadow-md">
                                        <span class="text-sm">📊</span> EXCEL
                                    </button>
                                    <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-black transition shadow-md">
                                        <span class="text-sm">📄</span> IN PDF
                                    </button>
                                </div>
                            </div>
                        </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[11px] font-black text-slate-500 uppercase tracking-widest bg-slate-50/50 border-b border-slate-100">
                            @php
                                $allOnPage = \App\Models\StockIn::whereBetween('created_at', [$listDateFrom . ' 00:00:00', $listDateTo . ' 23:59:59'])
                                    ->where(function($q) {
                                        $q->where('code', 'like', '%' . $this->listSearch . '%')
                                          ->orWhere('supplier_name', 'like', '%' . $this->listSearch . '%');
                                    })
                                    ->latest()
                                    ->paginate(15);
                                $idsOnPage = $allOnPage->pluck('id')->toArray();
                            @endphp
                            <tr>
                                <th class="px-6 py-4 w-10 no-print">
                                    <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $idsOnPage) }}])" {{ count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                                </th>
                                <th class="px-2 py-4">Mã phiếu</th>
                                <th class="px-6 py-4">Ngày tạo</th>
                                <th class="px-6 py-4">Nhà cung cấp / Đối tác</th>
                                <th class="px-6 py-4">Loại nhập</th>
                                <th class="px-6 py-4 text-right">Tổng tiền</th>
                                <th class="px-6 py-4">Ghi chú</th>
                                <th class="px-6 py-4 text-center no-print">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($allOnPage as $si)
                                <tr class="hover:bg-slate-50/80 transition group {{ in_array($si->id, $selectedIds) ? 'bg-indigo-50/30' : '' }}">
                                    <td class="px-6 py-4 no-print">
                                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $si->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-2 py-4 font-black text-indigo-700">{{ $si->code }}</td>
                                    <td class="px-6 py-4 text-slate-500 text-xs">{{ $si->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 font-black text-slate-800 text-xs">{{ $si->supplier_name ?: ($si->manufacturer ?: '-') }}</td>
                                    <td class="px-6 py-4">
                                        @switch($si->type)
                                            @case('purchase_produced') <span class="px-2 py-1 bg-emerald-50 text-emerald-700 rounded text-[10px] font-black uppercase">🛒 Mua hàng</span> @break
                                            @case('production') <span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-[10px] font-black uppercase">🏭 Sản xuất</span> @break
                                            @case('import_material') <span class="px-2 py-1 bg-amber-50 text-amber-700 rounded text-[10px] font-black uppercase">📦 Nguyên liệu</span> @break
                                            @default <span class="px-2 py-1 bg-slate-50 text-slate-600 rounded text-[10px] font-black uppercase">{{ $si->type }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-slate-900">
                                        {{ number_format($si->items->sum('total_amount')) }} đ
                                    </td>
                                    <td class="px-6 py-4 text-slate-400 text-xs italic truncate max-w-[150px]">{{ $si->note ?: '-' }}</td>
                                    <td class="px-6 py-4 text-center no-print">
                                        <div class="flex items-center justify-center gap-1">
                                            <button wire:click="toggleSelectAll([{{ $si->id }}])" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Chọn in">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            </button>
                                            <button class="p-2 text-slate-300 hover:text-indigo-600 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-slate-400">Không tìm thấy phiếu nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-slate-50 no-print">
                    {{ $allOnPage->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Product Modal -->
    @if($showProductModal)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-bold mb-4">Tạo nhanh sản phẩm mới</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mã sản phẩm</label>
                        <input type="text" wire:model="newPCode" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('newPCode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tên sản phẩm</label>
                        <input type="text" wire:model="newPName" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('newPName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Đơn vị tính</label>
                        <input type="text" wire:model="newPUnit" class="w-full rounded-lg border-gray-300 focus:ring-blue-500">
                        @error('newPUnit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hãng sản xuất (Kế thừa từ header)</label>
                        <input type="text" disabled value="{{ $manufacturer }}" class="w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showProductModal', false)" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Hủy</button>
                    <button wire:click="createProduct" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Lưu & Thêm dòng</button>
                </div>
            </div>
        </div>
    @endif

    <!-- PHẦN IN CHI TIẾT HÀNG LOẠT (Nhập kho) -->
    @if(count($printItems) > 0)
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        @foreach($printItems as $pItem)
        <div class="print-page p-8 bg-white" style="font-family: 'Times New Roman', serif; min-height: 297mm; page-break-after: always;">
            <div class="flex justify-between items-start mb-6 border-b-2 border-slate-900 pb-4">
                <div>
                    <h1 class="text-xl font-black uppercase">CÔNG TY TNHH SANE</h1>
                    <p class="text-[11px] font-bold">Khu công nghiệp Đức Hòa 1, Long An</p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-black text-slate-900 uppercase">PHIẾU NHẬP KHO</h2>
                    <p class="text-sm font-bold mt-1">Số: <span class="text-indigo-700">{{ $pItem->code }}</span></p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-8 mb-8">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Đơn vị giao hàng</p>
                    <p class="font-black text-slate-800 text-lg uppercase">{{ $pItem->supplier_name ?: ($pItem->manufacturer ?: 'N/A') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Ngày nhập kho</p>
                    <p class="font-black text-slate-800">{{ $pItem->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <table class="w-full border-collapse border-2 border-slate-900 mb-6">
                <thead>
                    <tr class="bg-slate-100 uppercase text-[10px] font-black">
                        <th class="border border-slate-900 px-2 py-2 text-center w-10">STT</th>
                        <th class="border border-slate-900 px-2 py-2 text-left">Tên sản phẩm / Quy cách</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-16">Số lô</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-16">ĐVT</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-20">Số lượng</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-24">Đơn giá</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-24">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="text-xs">
                    @foreach($pItem->items as $idx => $ii)
                    <tr>
                        <td class="border border-slate-900 px-2 py-2 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-slate-900 px-2 py-2 font-bold">{{ $ii->product->name }} ({{ $ii->product->code }})</td>
                        <td class="border border-slate-900 px-2 py-2 text-center">{{ $ii->batch_number }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-center">{{ $ii->product->unit }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-right font-bold">{{ number_format($ii->quantity, 2) }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-right">{{ number_format($ii->unit_price) }}</td>
                        <td class="border border-slate-900 px-2 py-2 text-right font-black">{{ number_format($ii->total_amount) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-black">
                        <td colspan="6" class="border border-slate-900 px-2 py-2 text-right uppercase">Tổng cộng:</td>
                        <td class="border border-slate-900 px-2 py-2 text-right text-sm font-black">{{ number_format($pItem->items->sum('total_amount')) }} đ</td>
                    </tr>
                </tfoot>
            </table>

            <div class="grid grid-cols-4 gap-4 text-center mt-12">
                <div>
                    <p class="font-bold text-xs uppercase">Người giao hàng</p>
                    <p class="text-[9px] italic">(Ký, ghi rõ họ tên)</p>
                </div>
                <div>
                    <p class="font-bold text-xs uppercase">Người nhận</p>
                    <p class="text-[9px] italic">(Ký, ghi rõ họ tên)</p>
                </div>
                <div>
                    <p class="font-bold text-xs uppercase">Thủ kho</p>
                    <p class="text-[9px] italic">(Ký, ghi rõ họ tên)</p>
                </div>
                <div>
                    <p class="font-bold text-xs uppercase">Giám đốc</p>
                    <p class="text-[9px] italic">(Ký, ghi rõ họ tên)</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @script
    <script>
        $wire.on('trigger-print', () => {
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
    @endscript
</div>

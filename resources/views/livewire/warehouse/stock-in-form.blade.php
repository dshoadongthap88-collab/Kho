<div class="h-full flex flex-col space-y-4" style="font-family: 'Times New Roman', Times, serif;">
    <!-- Tab Navigation -->
    <div class="bg-white p-2 rounded-2xl shadow-md border border-slate-200 flex items-center gap-3 w-fit no-print">
        <button wire:click="$set('activeTab', 'form')" class="px-8 py-3 rounded-xl text-[13px] font-black transition-all flex items-center gap-2 {{ $activeTab === 'form' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50' }}">
            <span>📥</span> LẬP PHIẾU NHẬP
        </button>
        <button wire:click="$set('activeTab', 'list')" class="px-8 py-3 rounded-xl text-[13px] font-black transition-all flex items-center gap-2 {{ $activeTab === 'list' ? 'bg-indigo-600 text-white shadow-xl shadow-indigo-100' : 'text-slate-500 hover:bg-slate-50' }}">
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

            @if($errors->any())
                <div class="mb-4 p-4 bg-rose-50 text-rose-700 rounded-2xl font-bold border border-rose-200 animate-in fade-in slide-in-from-top-2">
                    <div class="flex items-center gap-2 mb-2 text-rose-800">
                        <span>❌</span> <span>Có lỗi xảy ra:</span>
                    </div>
                    <ul class="list-disc list-inside text-[13px] ml-6">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                    <h2 class="text-[15px] font-black text-slate-900 flex items-center gap-2 uppercase tracking-tight">
                        <span class="p-2 bg-indigo-600 text-white rounded-xl shadow-lg">📥</span>
                        PHIẾU NHẬP KHO MỚI
                    </h2>
                </div>
                
                <div class="p-6">

        <div class="grid grid-cols-3 gap-6 mb-8">
            <div class="space-y-1.5">
                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest px-1">Nhà cung cấp</label>
                <input type="text" wire:model="supplier_name" list="suppliers_list" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2.5 px-4 text-[13px] font-bold text-slate-800" placeholder="Chọn hoặc nhập tên...">
                <datalist id="suppliers_list">
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->name }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="space-y-1.5">
                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest px-1">Hãng sản xuất</label>
                <input type="text" wire:model="manufacturer" list="brands_list" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2.5 px-4 text-[13px] font-bold text-slate-800" placeholder="Nhập hãng SX...">
                <datalist id="brands_list">
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="space-y-1.5">
                <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest px-1">Loại nhập</label>
                <select wire:model.live="type" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all py-2.5 px-4 text-[13px] font-black text-slate-800 appearance-none">
                    <option value="purchase_produced">🛒 NHẬP MUA HÀNG TP</option>
                    <option value="return_produced">↩️ NHẬP TRẢ HÀNG TP</option>
                    <option value="production">🏭 NHẬP TỪ SẢN XUẤT</option>
                    <option value="import_material">📦 NHẬP NGUYÊN VẬT LIỆU</option>
                </select>
            </div>
        </div>

        <div class="overflow-hidden border border-slate-200 rounded-2xl shadow-sm mb-6 bg-slate-50/30">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-slate-800">
                                    <th class="px-4 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 min-w-[200px]">Sản phẩm</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Số lô</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-32">Hạn dùng</th>
                                    <th class="px-2 py-3 text-left text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Vị trí</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-20">SL</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-16">ĐVT</th>
                                    <th class="px-2 py-3 text-right text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-24">Đơn giá</th>
                                    <th class="px-2 py-3 text-center text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-14">VAT</th>
                                    <th class="px-2 py-3 text-right text-[11px] font-black text-white uppercase tracking-widest border-b border-slate-700 w-32">Thành tiền</th>
                                    <th class="px-2 py-3 border-b border-slate-700 w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @foreach($items as $index => $item)
                                <tr class="hover:bg-indigo-50/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <input type="text" wire:model.live.debounce.250ms="items.{{ $index }}.product_search" list="product_list_{{ $index }}" 
                                               class="w-full rounded-lg border-slate-200 text-[13px] font-bold focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-3 bg-slate-50 focus:bg-white"
                                               placeholder="Mã hoặc tên SP...">
                                        <datalist id="product_list_{{ $index }}">
                                            @foreach($products as $product)
                                                <option value="{{ $product->code }} - {{ $product->name }}"></option>
                                            @endforeach
                                        </datalist>
                                        @error("items.{$index}.product_id") <p class="text-rose-500 text-[10px] mt-1 font-bold">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="text" wire:model.live="items.{{ $index }}.batch_number" 
                                               class="w-full rounded-lg border-slate-200 text-[12px] font-black focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-2 bg-slate-50 focus:bg-white text-indigo-700" placeholder="Số lô...">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="date" wire:model="items.{{ $index }}.expiry_date" 
                                               class="w-full rounded-lg border-slate-200 text-[12px] focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-2 bg-slate-50 focus:bg-white font-bold text-slate-700">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="text" wire:model="items.{{ $index }}.warehouse_location" 
                                               class="w-full text-[12px] font-bold rounded-lg border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-2 bg-slate-50 focus:bg-white" placeholder="Vị trí...">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="text" inputmode="numeric" wire:model.lazy="items.{{ $index }}.quantity"
                                               class="w-full text-center text-[13px] font-black rounded-lg border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-1 bg-slate-50 focus:bg-white text-slate-900"
                                               placeholder="0">
                                    </td>
                                    <td class="px-2 py-3 text-center">
                                        <span class="text-[11px] font-black text-slate-500 bg-slate-100 px-2 py-1 rounded-md border border-slate-200 uppercase">{{ $items[$index]['unit'] ?? '-' }}</span>
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="number" wire:model.live="items.{{ $index }}.unit_price" step="1" min="0"
                                               class="w-full text-right text-[12px] font-bold rounded-lg border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-1 bg-slate-50 focus:bg-white">
                                    </td>
                                    <td class="px-2 py-3">
                                        <input type="number" wire:model.live="items.{{ $index }}.vat_rate" step="0.1" min="0"
                                               class="w-full text-center text-[12px] rounded-lg border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 transition-all py-1.5 px-1 bg-slate-50 focus:bg-white">
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-indigo-700 text-[14px]">
                                        {{ number_format($items[$index]['total_amount'] ?? 0) }} đ
                                    </td>
                                    <td class="px-2 py-3 text-center">
                                        @if(count($items) > 1)
                                            <button wire:click="removeItem({{ $index }})" class="text-slate-300 hover:text-rose-600 transition-all p-1.5 rounded-full hover:bg-rose-50">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if(count($items) > 0)
                            <tfoot class="border-t-2 border-slate-100">
                                <tr class="bg-indigo-50/50">
                                    <td colspan="8" class="px-6 py-4 text-right font-black text-slate-500 uppercase tracking-widest text-[11px]">Tổng cộng giá trị nhập:</td>
                                    <td class="px-4 py-4 text-right font-black text-indigo-900 text-[16px] underline decoration-double">
                                        {{ number_format(collect($items)->sum('total_amount')) }} đ
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>

        <div class="flex items-center gap-4 mb-8">
            @if($this->canAddItem())
                <button wire:click="addItem" class="bg-slate-800 text-white px-6 py-2.5 rounded-xl text-[12px] font-black flex items-center gap-2 hover:bg-indigo-600 transition-all shadow-md active:scale-95">
                    <span>➕</span> THÊM DÒNG MỚI
                </button>
            @endif

            <button wire:click="openProductModal" class="bg-white border-2 border-emerald-600 text-emerald-700 px-6 py-2.5 rounded-xl text-[12px] font-black flex items-center gap-2 hover:bg-emerald-50 transition-all shadow-sm active:scale-95">
                <span>📦</span> TẠO NHANH SẢN PHẨM
            </button>
        </div>

        <div class="mb-8">
            <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2 px-1">Ghi chú phiếu nhập</label>
            <textarea wire:model="note" rows="3" class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:bg-white focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all p-4 text-[13px] font-bold text-slate-800" placeholder="Nhập lý do nhập kho, thông tin thêm..."></textarea>
        </div>

        <div class="flex justify-end pt-4">
            <button wire:click="save" class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white px-12 py-3.5 rounded-2xl text-[14px] font-black shadow-xl shadow-indigo-100 hover:scale-105 active:scale-95 transition-all flex items-center gap-3">
                <span>📥</span> XÁC NHẬN NHẬP KHO
            </button>
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
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden min-h-[600px] main-content">
                <!-- Print Title -->
                <div class="hidden print:block text-center mb-8">
                    <h1 class="text-2xl font-black uppercase underline decoration-double">DANH SÁCH PHIẾU NHẬP KHO</h1>
                    <p class="text-sm font-bold mt-1">TỪ NGÀY: {{ \Carbon\Carbon::parse($listDateFrom)->format('d/m/Y') }} - ĐẾN NGÀY: {{ \Carbon\Carbon::parse($listDateTo)->format('d/m/Y') }}</p>
                </div>

                <div class="bg-slate-50 px-6 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4 no-print">
                    <h2 class="text-[15px] font-black text-slate-900 flex items-center gap-2 uppercase tracking-tight">
                        <span class="p-2 bg-indigo-600 text-white rounded-xl shadow-lg">📋</span>
                        LỊCH SỬ NHẬP KHO
                    </h2>
                    
                    <div class="flex flex-wrap items-center gap-3 no-print">
                        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-2xl border border-slate-200 shadow-inner transition-all focus-within:ring-4 focus-within:ring-indigo-100">
                            <div class="flex items-center gap-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Từ ngày</label>
                                <input type="date" wire:model.live="listDateFrom" class="text-[12px] border-none focus:ring-0 p-0 font-black text-slate-700 bg-transparent">
                            </div>
                            <div class="w-px h-4 bg-slate-200 mx-2"></div>
                            <div class="flex items-center gap-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Đến ngày</label>
                                <input type="date" wire:model.live="listDateTo" class="text-[12px] border-none focus:ring-0 p-0 font-black text-slate-700 bg-transparent">
                            </div>
                        </div>

                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="listSearch" placeholder="TÌM MÃ, NCC..." class="pl-11 pr-4 py-2.5 w-64 text-[12px] font-black rounded-2xl border-slate-200 focus:ring-4 focus:ring-indigo-100 focus:border-indigo-500 shadow-inner transition-all bg-white placeholder:text-slate-300">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 ml-2">
                            @if(count($selectedIds) > 0)
                                <div class="flex items-center gap-2 pr-3 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4">
                                    <span class="text-[11px] font-black text-indigo-700 bg-indigo-50 px-2.5 py-1.5 rounded-lg border border-indigo-100">CHỌN: {{ count($selectedIds) }}</span>
                                    <button wire:click="deleteSelected" wire:confirm="Xác nhận xóa {{ count($selectedIds) }} phiếu nhập?" class="flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-rose-500 to-rose-600 text-white rounded-xl text-[12px] font-black transition-all hover:scale-105 shadow-md">
                                        <span>🗑️</span> XÓA
                                    </button>
                                </div>
                            @endif
                                    <button wire:click="printSelected" class="flex items-center gap-2 px-4 py-2.5 bg-white border-2 border-indigo-600 text-indigo-700 hover:bg-indigo-50 rounded-xl text-[12px] font-black transition-all shadow-sm">
                                        <span class="text-sm">🖨️</span> IN GHÉP
                                    </button>
                                    <button wire:click="exportExcel" class="flex items-center gap-2 px-4 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 rounded-xl text-[12px] font-black transition-all shadow-lg shadow-emerald-100">
                                        <span class="text-sm">📊</span> EXCEL
                                    </button>
                                    <button onclick="window.print()" class="flex items-center gap-2 px-4 py-2.5 bg-slate-800 text-white hover:bg-black rounded-xl text-[12px] font-black transition-all shadow-lg">
                                        <span class="text-sm">📄</span> IN PDF
                                    </button>
                                </div>
                            </div>
                        </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-[11px] font-black text-white uppercase tracking-widest bg-slate-800 border-b border-slate-700">
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
                                <th class="px-6 py-4 w-10 no-print text-center">
                                    <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $idsOnPage) }}])" {{ count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500">
                                </th>
                                <th class="px-2 py-4">MÃ PHIẾU</th>
                                <th class="px-6 py-4">NGÀY TẠO</th>
                                <th class="px-6 py-4">NHÀ CUNG CẤP / ĐỐI TÁC</th>
                                <th class="px-6 py-4">LOẠI NHẬP</th>
                                <th class="px-6 py-4 text-right">TỔNG TIỀN</th>
                                <th class="px-6 py-4">GHI CHÚ</th>
                                <th class="px-6 py-4 text-center no-print">THAO TÁC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse($allOnPage as $si)
                                <tr class="hover:bg-indigo-50/30 transition-all group {{ in_array($si->id, $selectedIds) ? 'bg-indigo-50' : '' }}">
                                    <td class="px-6 py-4 no-print text-center">
                                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $si->id }}" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-2 py-4 font-black text-indigo-700 tracking-tight">{{ $si->code }}</td>
                                    <td class="px-6 py-4 text-slate-500 text-[12px] font-bold">{{ $si->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 font-black text-slate-800 text-[13px] uppercase tracking-tighter">{{ $si->supplier_name ?: ($si->manufacturer ?: '-') }}</td>
                                    <td class="px-6 py-4">
                                        @switch($si->type)
                                            @case('purchase_produced') <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-[10px] font-black uppercase border border-emerald-100">🛒 MUA HÀNG</span> @break
                                            @case('production') <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-lg text-[10px] font-black uppercase border border-indigo-100">🏭 SẢN XUẤT</span> @break
                                            @case('import_material') <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg text-[10px] font-black uppercase border border-amber-100">📦 NGUYÊN LIỆU</span> @break
                                            @default <span class="px-2.5 py-1 bg-slate-50 text-slate-600 rounded-lg text-[10px] font-black uppercase border border-slate-100">{{ $si->type }}</span>
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 text-right font-black text-slate-900 text-[14px]">
                                        {{ number_format($si->items->sum('total_amount')) }} đ
                                    </td>
                                    <td class="px-6 py-4 text-slate-400 text-[11px] font-bold italic truncate max-w-[150px]">{{ $si->note ?: '-' }}</td>
                                    <td class="px-6 py-4 text-center no-print">
                                        <div class="flex items-center justify-center gap-1">
                                            <button wire:click="printSingle({{ $si->id }})" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-all" title="In phiếu này">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            </button>
                                            <button wire:confirm="Xác nhận xóa phiếu nhập {{ $si->code }}? Tồn kho sẽ được giảm trừ tương ứng." wire:click="delete({{ $si->id }})" class="p-2 text-rose-300 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition-all" title="Xóa phiếu">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
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
                        <td class="border border-slate-900 px-2 py-2 text-right font-bold">{{ number_format($ii->quantity) }}</td>
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

        $wire.on('show-success-effect', () => {
            const effect = document.createElement('div');
            effect.innerHTML = `
                <div class="fixed inset-0 flex items-center justify-center z-[9999] pointer-events-none transition-all duration-500 opacity-0" id="success-effect-container">
                    <div class="bg-white/90 backdrop-blur-md border-4 border-emerald-500 text-emerald-600 rounded-[3rem] p-12 flex flex-col items-center justify-center shadow-[0_0_100px_rgba(16,185,129,0.4)] transform scale-50 transition-transform duration-500" id="success-effect-box">
                        <div class="bg-emerald-500 text-white p-4 rounded-full mb-6 shadow-xl animate-[bounce_1s_ease-in-out]">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h2 class="text-4xl font-black uppercase tracking-widest text-slate-800">Đã Nhập Kho</h2>
                        <p class="text-slate-500 font-bold mt-2 text-lg">Hệ thống đã lưu phiếu thành công!</p>
                    </div>
                </div>
            `;
            document.body.appendChild(effect);
            
            setTimeout(() => {
                const container = document.getElementById('success-effect-container');
                const box = document.getElementById('success-effect-box');
                if (container && box) {
                    container.classList.remove('opacity-0');
                    container.classList.add('opacity-100');
                    box.classList.remove('scale-50');
                    box.classList.add('scale-100');
                }
            }, 50);

            setTimeout(() => {
                const container = document.getElementById('success-effect-container');
                const box = document.getElementById('success-effect-box');
                if (container && box) {
                    container.classList.remove('opacity-100');
                    container.classList.add('opacity-0');
                    box.classList.remove('scale-100');
                    box.classList.add('scale-50');
                    
                    setTimeout(() => {
                        if (effect.parentNode) effect.parentNode.removeChild(effect);
                    }, 500);
                }
            }, 2500);
        });
    </script>
    @endscript
</div>

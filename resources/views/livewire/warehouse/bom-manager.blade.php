<div>
    <style>
        @media print {
            .no-print, header, nav, aside { display: none !important; }
            body { font-size: 11pt; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ccc; padding: 6px 10px; }
        }
    </style>

    {{-- Flash --}}
    @if(session('success'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)"
             class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl mb-4 text-sm font-medium no-print">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ===== TOOLBAR ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4 no-print">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex-1 min-w-0 max-w-lg">
                <label class="form-label mb-1.5">Chọn sản phẩm / Mã tài sản</label>
                <select wire:model.live="selectedProductId" class="w-full">
                    <option value="">-- Chọn sản phẩm / Mã tài sản --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->code }} — {{ $product->name }}</option>
                    @endforeach
                </select>
            </div>

            @if($selectedProductId)
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()"
                            class="flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-700 hover:text-white border border-slate-200 rounded-xl transition no-print">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        In BOM
                    </button>
                    <button type="button" wire:click="saveBom" wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition disabled:opacity-60 no-print">
                        <span wire:loading.remove wire:target="saveBom">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        </span>
                        <span wire:loading wire:target="saveBom">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                        Lưu cấu hình
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- ===== NỘI DUNG BOM ===== --}}
    @if($selectedProductId)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Danh sách định mức vật tư</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Mỗi lần xuất kho theo BOM sẽ trừ đúng theo số lượng định mức này</p>
                </div>
                @if($availability)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold
                        {{ $availability['is_sufficient'] ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-1 ring-rose-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $availability['is_sufficient'] ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                        {{ $availability['is_sufficient'] ? 'Đủ vật tư' : 'Thiếu vật tư' }}
                    </span>
                @endif
            </div>

            {{-- Table --}}
            @if(count($bomItems) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                                <th class="px-4 py-3 text-left">Vật tư định mức</th>
                                <th class="px-4 py-3 text-center w-32">Định mức / 1 TS</th>
                                <th class="px-4 py-3 text-center w-20">ĐVT</th>
                                @if($availability)
                                    <th class="px-4 py-3 text-center w-28">Tồn kho</th>
                                    <th class="px-4 py-3 text-center w-24">Trạng thái</th>
                                @endif
                                <th class="px-4 py-3 text-center w-20 no-print"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($bomItems as $index => $item)
                                @php
                                    $detail = $availability['details'][$index] ?? null;
                                    $sufficient = $detail ? $detail['is_sufficient'] : true;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors {{ !$sufficient ? 'bg-rose-50/30' : '' }}">
                                    <td class="px-4 py-2.5 font-medium text-slate-800">{{ $item['material_name'] }}</td>
                                    <td class="px-4 py-2.5 text-center font-bold text-slate-700 tabular-nums">{{ floatval($item['quantity']) }}</td>
                                    <td class="px-4 py-2.5 text-center text-xs text-slate-500">{{ $item['unit'] }}</td>
                                    @if($availability && $detail)
                                        <td class="px-4 py-2.5 text-center tabular-nums font-medium
                                            {{ $sufficient ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ number_format($detail['available']) }}
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            @if($sufficient)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Đủ
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-200">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>Thiếu
                                                </span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-4 py-2.5 text-center no-print">
                                        <button wire:click="removeMaterial({{ $item['id'] }})"
                                                wire:confirm="Xác nhận xóa {{ $item['material_name'] }} khỏi định mức?"
                                                class="p-1.5 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition" title="Xóa">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="font-medium text-slate-600">Chưa có vật tư định mức</p>
                    <p class="text-sm mt-1">Thêm vật tư bên dưới để khai báo định mức</p>
                </div>
            @endif

            {{-- Form thêm vật tư --}}
            <div class="px-5 py-4 border-t border-slate-200 bg-slate-50/50 no-print">
                <p class="text-xs font-bold text-slate-600 uppercase tracking-wide mb-3">Thêm định mức vật tư</p>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="form-label mb-1">Vật tư</label>
                        <select wire:model="newMaterialId" class="w-full">
                            <option value="">-- Chọn vật tư --</option>
                            @foreach($materials as $mat)
                                <option value="{{ $mat->id }}">{{ $mat->code }} — {{ $mat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-28">
                        <label class="form-label mb-1">Số lượng</label>
                        <input type="number" inputmode="numeric" wire:model.lazy="newQuantity" placeholder="1" min="0" step="0.01">
                    </div>
                    <div class="w-24">
                        <label class="form-label mb-1">ĐVT</label>
                        <input type="text" wire:model="newUnit" placeholder="tự động">
                    </div>
                    <button wire:click="addMaterial"
                            class="flex items-center gap-1.5 px-4 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Thêm
                    </button>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm py-16 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <p class="font-semibold text-slate-600">Chọn sản phẩm / mã tài sản ở trên</p>
            <p class="text-sm mt-1">để xem và quản lý định mức BOM</p>
        </div>
    @endif
</div>

<div>
    <style>
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            nav, .sidebar-toolbar, button, a, .no-print,
            input, select, .print-hide { display: none !important; }
            body { background: white !important; font-size: 10pt; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #bbb !important; padding: 6px 10px !important; }
            .print-show { display: block !important; }
            h2 { font-size: 16pt !important; text-align: center !important; }
        }
        .print-show { display: none; }
    </style>

    {{-- ===== TOOLBAR ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4 print-hide">
        <div class="flex flex-wrap items-center justify-between gap-3">

            {{-- Left: Filters --}}
            <div class="filter-grid flex-1 min-w-0">
                {{-- Date range --}}
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <input type="date" wire:model.live="dateFrom"
                           class="text-xs font-semibold border-none bg-transparent focus:ring-0 p-0 text-slate-700">
                    <span class="text-slate-300 text-xs">→</span>
                    <input type="date" wire:model.live="dateTo"
                           class="text-xs font-semibold border-none bg-transparent focus:ring-0 p-0 text-slate-700">
                </div>

                {{-- Search --}}
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Tên khách, mã phiếu..."
                           class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                </div>

                {{-- Status filter --}}
                <select wire:model.live="filterStatus"
                        class="text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending">🚨 Chờ giao</option>
                    <option value="delivered">✅ Đã giao</option>
                </select>
            </div>

            {{-- Right: Bulk + Export --}}
            <div class="flex items-center gap-2">
                @if(count($selectedIds) > 0)
                    <div class="flex items-center gap-2 pr-3 border-r border-slate-200 animate-in slide-in-from-right-2 duration-200">
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">
                            {{ count($selectedIds) }} đã chọn
                        </span>
                        <button wire:click="printSelected" wire:loading.attr="disabled"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            In ghép
                        </button>
                        <button wire:click="deleteSelected"
                                wire:confirm="Xóa {{ count($selectedIds) }} báo cáo đã chọn?"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Xóa
                        </button>
                    </div>
                @endif
                <button wire:click="exportExcel"
                        class="flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white border border-emerald-200 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </button>
                <button onclick="window.print()"
                        class="flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-700 hover:text-white border border-slate-200 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    PDF
                </button>
            </div>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('message'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,3000)"
             class="flex items-center gap-2 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium print-hide">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('message') }}
        </div>
    @endif

    {{-- ===== TABLE ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                        @php $idsOnPage = $reports->pluck('id')->toArray(); @endphp
                        <th class="px-4 py-3 w-10 text-center print-hide">
                            <input type="checkbox"
                                   wire:click="toggleSelectAll([{{ implode(',', $idsOnPage) }}])"
                                   {{ count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-3">Mã phiếu</th>
                        <th class="px-4 py-3">Khách hàng</th>
                        <th class="px-4 py-3 text-center">Tình trạng giao</th>
                        <th class="px-4 py-3 text-center">Thanh toán</th>
                        <th class="px-4 py-3">Ghi chú</th>
                        <th class="px-4 py-3 text-center print-hide">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $report)
                        <tr class="hover:bg-slate-50/70 transition-colors
                            {{ $report->status === 'pending' ? 'bg-rose-50/20' : '' }}
                            {{ in_array($report->id, $selectedIds) ? 'bg-indigo-50/30' : '' }}">

                            <td class="px-4 py-2.5 text-center print-hide">
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $report->id }}"
                                       class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>

                            {{-- Mã phiếu --}}
                            <td class="px-4 py-2.5">
                                <div class="font-bold text-indigo-600 text-xs font-mono">{{ $report->stockOut->code ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ optional($report->stockOut->created_at)->format('d/m/Y H:i') }}
                                </div>
                            </td>

                            {{-- Khách hàng --}}
                            <td class="px-4 py-2.5 font-semibold text-slate-800">
                                {{ $report->customer_name ?: 'Khách lẻ' }}
                            </td>

                            {{-- Tình trạng giao --}}
                            <td class="px-4 py-2.5 text-center">
                                @if($report->status === 'delivered')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Đã giao
                                    </span>
                                    @if($report->delivered_at)
                                        <div class="text-[10px] text-slate-400 mt-1">{{ \Carbon\Carbon::parse($report->delivered_at)->format('d/m/Y') }}</div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-600 ring-1 ring-rose-200 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Chờ giao
                                    </span>
                                @endif
                            </td>

                            {{-- Thanh toán --}}
                            <td class="px-4 py-2.5 text-center">
                                @php
                                    $payMap = [
                                        'paid'          => ['label' => 'Đã TT', 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
                                        'bank_transfer' => ['label' => 'CK', 'class' => 'bg-blue-50 text-blue-700 ring-blue-200'],
                                        'debt'          => ['label' => 'Ghi nợ', 'class' => 'bg-amber-50 text-amber-700 ring-amber-200'],
                                        'unpaid'        => ['label' => 'Chưa TT', 'class' => 'bg-slate-100 text-slate-600 ring-slate-200'],
                                    ];
                                    $pay = $payMap[$report->payment_status] ?? $payMap['unpaid'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold ring-1 {{ $pay['class'] }}">
                                    {{ $pay['label'] }}
                                </span>
                            </td>

                            {{-- Ghi chú --}}
                            <td class="px-4 py-2.5 text-xs text-slate-500 max-w-[180px] truncate" title="{{ $report->notes }}">
                                {{ $report->notes ?: '—' }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-2.5 text-center print-hide">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="printSingle({{ $report->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="In phiếu">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </button>

                                    @if($report->status !== 'delivered')
                                        <button wire:click="openConfirmModal({{ $report->id }})"
                                                class="flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            Xác nhận
                                        </button>
                                    @else
                                        @if($report->photo_path)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($report->photo_path) }}" target="_blank"
                                               class="flex items-center gap-1 px-2 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                Ảnh
                                            </a>
                                        @endif
                                    @endif

                                    <button wire:click="delete({{ $report->id }})"
                                            wire:confirm="Xác nhận xóa báo cáo giao hàng này?"
                                            class="p-1.5 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 transition" title="Xóa">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <div>
                                        <p class="font-semibold text-slate-600">Không tìm thấy báo cáo giao hàng</p>
                                        <p class="text-sm">Thử thay đổi bộ lọc ngày hoặc từ khóa tìm kiếm</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 print-hide">
            {{ $reports->links() }}
        </div>
    </div>

    {{-- ===== MODAL XÁC NHẬN GIAO HÀNG ===== --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 print-hide" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showConfirmModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg z-10 overflow-hidden">

                {{-- Header --}}
                <div class="flex items-center gap-3 px-6 py-4 bg-emerald-50 border-b border-emerald-100">
                    <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-emerald-900">Xác nhận giao hàng thành công</h3>
                        <p class="text-xs text-emerald-600">Điền thông tin để hoàn tất</p>
                    </div>
                    <button wire:click="$set('showConfirmModal', false)" class="ml-auto p-1.5 rounded-lg text-emerald-400 hover:bg-emerald-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-5 space-y-4">

                    {{-- Ảnh minh chứng --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">
                            Ảnh minh chứng giao hàng
                        </label>
                        <div class="border-2 border-dashed border-slate-200 rounded-xl p-4 text-center hover:border-indigo-300 transition">
                            <input type="file" wire:model="photo" accept="image/*" class="hidden" id="photo-upload">
                            <label for="photo-upload" class="cursor-pointer">
                                @if($photo)
                                    <img src="{{ $photo->temporaryUrl() }}" class="w-24 h-24 object-cover rounded-xl mx-auto mb-2">
                                    <p class="text-xs text-indigo-600 font-medium">Nhấn để đổi ảnh</p>
                                @else
                                    <svg class="w-8 h-8 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <p class="text-xs text-slate-500 font-medium">Nhấn để tải ảnh lên</p>
                                    <p class="text-[11px] text-slate-400">PNG, JPG tối đa 5MB</p>
                                @endif
                            </label>
                        </div>
                        <div wire:loading wire:target="photo" class="text-xs text-indigo-500 mt-1 flex items-center gap-1">
                            <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Đang tải ảnh...
                        </div>
                        @error('photo') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Trạng thái thanh toán --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">
                            Trạng thái thanh toán <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach([
                                ['value' => 'paid',          'icon' => '💵', 'label' => 'Tiền mặt', 'sub' => 'Đã thu đủ'],
                                ['value' => 'bank_transfer', 'icon' => '🏦', 'label' => 'Chuyển khoản', 'sub' => 'CK công ty'],
                                ['value' => 'debt',          'icon' => '📋', 'label' => 'Ghi nợ', 'sub' => 'Thanh toán sau'],
                                ['value' => 'unpaid',        'icon' => '⏳', 'label' => 'Chưa TT', 'sub' => 'Chờ xử lý'],
                            ] as $opt)
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border-2 cursor-pointer transition
                                    {{ $paymentStatus === $opt['value'] ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 hover:border-slate-300 bg-white' }}">
                                    <input type="radio" wire:model="paymentStatus" value="{{ $opt['value'] }}" class="sr-only">
                                    <span class="text-lg">{{ $opt['icon'] }}</span>
                                    <div>
                                        <div class="text-xs font-bold text-slate-700">{{ $opt['label'] }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $opt['sub'] }}</div>
                                    </div>
                                    @if($paymentStatus === $opt['value'])
                                        <svg class="w-4 h-4 text-indigo-600 ml-auto flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                        @error('paymentStatus') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Ghi chú --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Ghi chú</label>
                        <textarea wire:model="notes" rows="2"
                                  class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition resize-none"
                                  placeholder="Người thân nhận hộ, thời gian, v.v..."></textarea>
                        @error('notes') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button wire:click="$set('showConfirmModal', false)"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                        Hủy
                    </button>
                    <button wire:click="saveCompletion" wire:loading.attr="disabled"
                            class="px-5 py-2 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-sm transition flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="saveCompletion">Xác nhận hoàn tất</span>
                        <span wire:loading wire:target="saveCompletion" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Đang lưu...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== PRINT LAYOUT ===== --}}
    @if(count($printItems) > 0)
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        @foreach($printItems as $pItem)
        <div style="font-family: 'Times New Roman', serif; min-height: 297mm; padding: 20mm; page-break-after: always;">
            <div style="text-align:center; font-size:16px; font-weight:bold; text-transform:uppercase; margin-bottom:4px;">
                CÔNG TY CPĐT VÀ THI CÔNG HẠ TẦNG VINALPHA
            </div>
            <div style="text-align:center; font-size:20px; font-weight:bold; text-transform:uppercase; margin:16px 0 8px;">
                BIÊN BẢN GIAO NHẬN HÀNG HÓA
            </div>

            <table style="width:100%; margin-bottom:16px;">
                <tr>
                    <td style="width:50%;">
                        <div style="font-size:12px;"><b>Mã phiếu xuất:</b> {{ $pItem->stockOut->code ?? 'N/A' }}</div>
                        <div style="font-size:12px;"><b>Khách hàng:</b> {{ $pItem->customer_name }}</div>
                    </td>
                    <td style="width:50%; text-align:right;">
                        <div style="font-size:12px;"><b>Ngày giao:</b> {{ $pItem->delivered_at ? $pItem->delivered_at->format('d/m/Y H:i') : 'Chưa giao' }}</div>
                        <div style="font-size:12px;"><b>Thanh toán:</b>
                            {{ $pItem->payment_status === 'paid' ? 'Tiền mặt' : ($pItem->payment_status === 'debt' ? 'Ghi nợ' : ($pItem->payment_status === 'bank_transfer' ? 'Chuyển khoản' : 'Chưa thanh toán')) }}
                        </div>
                    </td>
                </tr>
            </table>

            <table border="1" style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:20px;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th style="padding:6px 10px; text-align:left;">STT</th>
                        <th style="padding:6px 10px; text-align:left;">Mã VT</th>
                        <th style="padding:6px 10px; text-align:left;">Tên vật tư</th>
                        <th style="padding:6px 10px; text-align:center;">ĐVT</th>
                        <th style="padding:6px 10px; text-align:center;">Số lượng</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pItem->stockOut->items ?? [] as $i => $item)
                    <tr>
                        <td style="padding:5px 10px; text-align:center;">{{ $i + 1 }}</td>
                        <td style="padding:5px 10px; font-family:monospace;">{{ $item->product->code ?? '' }}</td>
                        <td style="padding:5px 10px;">{{ $item->product->name ?? '' }}</td>
                        <td style="padding:5px 10px; text-align:center;">{{ $item->product->unit ?? '' }}</td>
                        <td style="padding:5px 10px; text-align:center; font-weight:bold;">{{ number_format($item->quantity) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($pItem->notes)
                <div style="font-size:11px; margin-bottom:20px;"><b>Ghi chú:</b> {{ $pItem->notes }}</div>
            @endif

            <table style="width:100%; margin-top:40px; text-align:center; font-size:12px;">
                <tr>
                    <td style="width:33%;"><b>Người giao hàng</b><br><i style="font-size:10px;">(Ký, ghi rõ họ tên)</i><div style="height:60px;"></div></td>
                    <td style="width:33%;"><b>Người nhận hàng</b><br><i style="font-size:10px;">(Ký, ghi rõ họ tên)</i><div style="height:60px;"></div></td>
                    <td style="width:33%;"><b>Xác nhận quản lý</b><br><i style="font-size:10px;">(Ký, ghi rõ họ tên)</i><div style="height:60px;"></div></td>
                </tr>
            </table>
        </div>
        @endforeach
    </div>
    @endif
</div>

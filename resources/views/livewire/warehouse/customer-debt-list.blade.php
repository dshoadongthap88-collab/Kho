<div>
    <style>
        @media print {
            @page { size: A4; margin: 12mm; }
            nav, .no-print, button, input, select, .print-hide { display: none !important; }
            body { background: white !important; font-size: 10pt; }
            table { width: 100% !important; border-collapse: collapse !important; }
            th, td { border: 1px solid #bbb !important; padding: 6px 10px !important; }
            .print-show { display: block !important; }
        }
        .print-show { display: none; }
    </style>

    {{-- ===== TOOLBAR ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 mb-4 print-hide">
        <div class="flex flex-wrap items-center justify-between gap-3">

            {{-- Left: Filters --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Date range --}}
                <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2">
                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <input type="date" wire:model.live="dateFrom" class="text-xs font-semibold border-none bg-transparent focus:ring-0 p-0 text-slate-700">
                    <span class="text-slate-300 text-xs">→</span>
                    <input type="date" wire:model.live="dateTo" class="text-xs font-semibold border-none bg-transparent focus:ring-0 p-0 text-slate-700">
                </div>

                {{-- Search --}}
                <div class="relative w-64">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="text"
                           placeholder="Tên khách, số phiếu..."
                           class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-xl bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                </div>

                {{-- Payment filter --}}
                <select wire:model.live="filterPayment"
                        class="text-xs font-semibold border border-slate-200 rounded-xl px-3 py-2 bg-slate-50 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                    <option value="">Tất cả hóa đơn</option>
                    <option value="unpaid_or_debt">🔴 Đang nợ</option>
                    <option value="paid">🟢 Đã thanh toán</option>
                </select>
            </div>

            {{-- Right: Bulk + Export --}}
            <div class="flex items-center gap-2">
                @if(count($selectedIds) > 0)
                    <div class="flex items-center gap-2 pr-3 border-r border-slate-200 animate-in slide-in-from-right-2 duration-200">
                        <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">
                            {{ count($selectedIds) }} đã chọn
                        </span>
                        <button wire:click="printSelected"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-lg transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            In ghép
                        </button>
                        <button wire:click="deleteSelected"
                                wire:confirm="Xóa {{ count($selectedIds) }} bản ghi đã chọn?"
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
    @if(session('error'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-2.5 rounded-xl mb-3 text-sm font-medium print-hide">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Print header --}}
    <div class="print-show" style="text-align:center; margin-bottom:10px;">
        <div style="font-size:16px; font-weight:bold; text-transform:uppercase;">CÔNG TY CPĐT VÀ THI CÔNG HẠ TẦNG VINALPHA</div>
        <div style="font-size:11px; color:#666;">Ngày in: {{ now()->format('d/m/Y H:i') }}</div>
        <hr style="margin:8px 0; border-top:1px solid #333;">
        <div style="font-size:15px; font-weight:bold;">BẢNG THEO DÕI CÔNG NỢ KHÁCH HÀNG</div>
    </div>

    {{-- ===== TABLE ===== --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-800 text-white text-xs font-bold uppercase tracking-wider">
                        @php $idsOnPage = $debts->pluck('id')->toArray(); @endphp
                        <th class="px-4 py-3 w-10 text-center print-hide">
                            <input type="checkbox"
                                   wire:click="toggleSelectAll([{{ implode(',', $idsOnPage) }}])"
                                   {{ count(array_intersect(array_map('strval', $idsOnPage), $selectedIds)) === count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-indigo-400 focus:ring-indigo-500 cursor-pointer">
                        </th>
                        <th class="px-4 py-3">Số phiếu</th>
                        <th class="px-4 py-3">Khách hàng</th>
                        <th class="px-4 py-3 text-right">Tổng tiền</th>
                        <th class="px-4 py-3 text-right">Đã thanh toán</th>
                        <th class="px-4 py-3 text-right">Còn lại</th>
                        <th class="px-4 py-3 text-center">Hạn TT</th>
                        <th class="px-4 py-3 text-center print-hide">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($debts as $report)
                        @php
                            $remaining = $report->total_amount - $report->paid_amount;
                            if ($report->due_date) {
                                $dueDate = \Carbon\Carbon::parse($report->due_date);
                            } elseif ($report->delivered_at) {
                                $dueDate = \Carbon\Carbon::parse($report->delivered_at)->addDays(30);
                            } else {
                                $dueDate = null;
                            }
                            $isOverdue  = $remaining > 0 && $dueDate && $dueDate->lt(now());
                            $daysOver   = $isOverdue ? (int)$dueDate->diffInDays(now()) : 0;
                            $isPaid     = $remaining <= 0 && $report->total_amount > 0;
                        @endphp
                        <tr wire:key="debt-{{ $report->id }}"
                            class="hover:bg-slate-50/70 transition-colors
                            {{ $isOverdue ? 'bg-rose-50/30' : '' }}
                            {{ $isPaid ? 'bg-emerald-50/20' : '' }}
                            {{ in_array((string)$report->id, $selectedIds) ? 'bg-indigo-50/30' : '' }}">

                            <td class="px-4 py-2.5 text-center print-hide">
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $report->id }}"
                                       class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            </td>

                            {{-- Số phiếu --}}
                            <td class="px-4 py-2.5">
                                <button wire:click="viewStockOutDetails({{ $report->stock_out_id }})"
                                        class="font-bold text-xs font-mono text-indigo-600 hover:text-indigo-800 hover:underline transition-colors text-left">
                                    {{ $report->stockOut->code ?? 'N/A' }}
                                </button>
                                @if($report->delivered_at)
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($report->delivered_at)->format('d/m/Y') }}
                                    </div>
                                @endif
                            </td>

                            {{-- Khách hàng --}}
                            <td class="px-4 py-2.5 font-semibold text-slate-800">
                                {{ explode(' (', $report->customer_name)[0] }}
                            </td>

                            {{-- Tổng tiền --}}
                            <td class="px-4 py-2.5 text-right font-semibold text-slate-700 tabular-nums">
                                {{ number_format($report->total_amount) }}
                            </td>

                            {{-- Đã TT --}}
                            <td class="px-4 py-2.5 text-right font-semibold text-emerald-600 tabular-nums">
                                {{ number_format($report->paid_amount) }}
                            </td>

                            {{-- Còn lại --}}
                            <td class="px-4 py-2.5 text-right tabular-nums">
                                @if($isPaid)
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-600">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        Xong
                                    </span>
                                @else
                                    <span class="font-bold text-rose-600">{{ number_format($remaining) }}</span>
                                    @if($isOverdue)
                                        <div class="text-[10px] text-rose-500 font-bold animate-pulse">Quá {{ $daysOver }}d</div>
                                    @endif
                                @endif
                            </td>

                            {{-- Hạn TT --}}
                            <td class="px-4 py-2.5 text-center text-xs">
                                @if($isPaid)
                                    <span class="text-slate-400">—</span>
                                @elseif($dueDate)
                                    <span class="{{ $isOverdue ? 'font-bold text-rose-600' : 'text-slate-600' }}">
                                        {{ $dueDate->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">Chưa đặt</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-2.5 text-center print-hide">
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="printSingle({{ $report->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="In phiếu">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                    </button>

                                    @if(!$isPaid)
                                        <button wire:click="openPayModal({{ $report->id }})"
                                                class="flex items-center gap-1 px-2 py-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-lg transition">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Thu
                                        </button>
                                        <button wire:click="markAsFullyPaid({{ $report->id }})"
                                                wire:confirm="Xác nhận khách hàng đã trả đủ nợ?"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 transition" title="Xong nợ">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    @endif

                                    <button wire:click="openEditModal({{ $report->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Sửa">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>

                                    <button wire:click="delete({{ $report->id }})"
                                            wire:confirm="Xác nhận xóa bản ghi công nợ này?"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Xóa">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                    <p class="font-semibold text-slate-600">Không có công nợ nào</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                {{-- Summary row --}}
                @if($debts->count() > 0)
                    @php
                        $sumTotal     = $debts->sum('total_amount');
                        $sumPaid      = $debts->sum('paid_amount');
                        $sumRemaining = $sumTotal - $sumPaid;
                    @endphp
                    <tfoot>
                        <tr class="bg-slate-50 border-t-2 border-slate-300 font-bold text-sm">
                            <td colspan="3" class="px-4 py-3 text-slate-600 print-hide"></td>
                            <td colspan="2" class="px-4 py-3 text-slate-600 hidden print:table-cell text-right font-bold text-xs uppercase tracking-wide">Tổng trang này</td>
                            <td class="px-4 py-3 text-right text-slate-800 tabular-nums">{{ number_format($sumTotal) }}</td>
                            <td class="px-4 py-3 text-right text-emerald-700 tabular-nums">{{ number_format($sumPaid) }}</td>
                            <td class="px-4 py-3 text-right {{ $sumRemaining > 0 ? 'text-rose-600' : 'text-slate-400' }} tabular-nums">
                                {{ number_format($sumRemaining) }}
                            </td>
                            <td colspan="2" class="print-hide"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        @if($debts->hasPages())
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 print-hide">
                {{ $debts->links() }}
            </div>
        @endif
    </div>

    {{-- Print footer --}}
    <div class="print-show" style="margin-top:30px;">
        <table style="width:100%; text-align:center; font-size:12px;">
            <tr>
                <td style="width:33%; padding-top:8px;"><b>Người lập</b><br><i style="font-size:10px;">(Ký, ghi rõ họ tên)</i><div style="height:60px;"></div></td>
                <td style="width:33%; padding-top:8px;"><b>Quản lý</b><br><i style="font-size:10px;">(Ký, ghi rõ họ tên)</i><div style="height:60px;"></div></td>
                <td style="width:33%; padding-top:8px;"><b>Khách hàng xác nhận</b><br><i style="font-size:10px;">(Ký, ghi rõ họ tên)</i><div style="height:60px;"></div></td>
            </tr>
        </table>
    </div>

    {{-- ===== MODAL THU TIỀN / SỬA ===== --}}
    @if($showPayModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 print-hide" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showPayModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md z-10 overflow-hidden">

                <div class="flex items-center gap-3 px-6 py-4 {{ $isEditMode ? 'bg-amber-50 border-b border-amber-100' : 'bg-emerald-50 border-b border-emerald-100' }}">
                    <div class="w-9 h-9 rounded-xl {{ $isEditMode ? 'bg-amber-100' : 'bg-emerald-100' }} flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $isEditMode ? 'text-amber-600' : 'text-emerald-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($isEditMode)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            @endif
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold {{ $isEditMode ? 'text-amber-900' : 'text-emerald-900' }}">
                            {{ $isEditMode ? 'Chỉnh sửa số tiền' : 'Ghi nhận thanh toán' }}
                        </h3>
                        <p class="text-xs {{ $isEditMode ? 'text-amber-600' : 'text-emerald-600' }}">
                            {{ $isEditMode ? 'Cập nhật số đã thanh toán' : 'Thu thêm tiền nợ' }}
                        </p>
                    </div>
                    <button wire:click="$set('showPayModal', false)" class="ml-auto p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">
                    {{-- Amount summary --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <div class="text-xs text-slate-500 mb-1">Tổng hóa đơn</div>
                            <div class="font-bold text-slate-800 tabular-nums">{{ number_format($maxPayAmount) }}đ</div>
                        </div>
                        <div class="bg-emerald-50 rounded-xl p-3 text-center">
                            <div class="text-xs text-emerald-600 mb-1">Đã thanh toán</div>
                            <div class="font-bold text-emerald-700 tabular-nums">
                                {{ $isEditMode ? number_format($maxPayAmount - $payAmount) : number_format($maxPayAmount - ($maxPayAmount - $payAmount)) }}đ
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">
                            {{ $isEditMode ? 'Số đã thanh toán (tổng)' : 'Số tiền thu thêm' }} <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input wire:model="payAmount" type="number" min="0" max="{{ $maxPayAmount }}"
                                   class="w-full border border-slate-200 rounded-xl px-4 py-3 pr-10 text-lg font-bold tabular-nums text-slate-800 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">đ</span>
                        </div>
                        @error('payAmount') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase tracking-wide">Hạn thanh toán</label>
                        <input wire:model="editDueDate" type="date"
                               class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-100 focus:border-indigo-400 transition">
                        <p class="text-[11px] text-slate-400 mt-1">Để trống → mặc định 30 ngày sau khi giao</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button wire:click="$set('showPayModal', false)"
                            class="px-4 py-2 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-100 transition">
                        Hủy
                    </button>
                    <button wire:click="receivePayment" wire:loading.attr="disabled"
                            class="px-5 py-2 text-sm font-bold text-white {{ $isEditMode ? 'bg-amber-500 hover:bg-amber-600' : 'bg-emerald-600 hover:bg-emerald-700' }} rounded-xl shadow-sm transition flex items-center gap-2 disabled:opacity-60">
                        <span wire:loading.remove wire:target="receivePayment">
                            {{ $isEditMode ? 'Cập nhật' : 'Xác nhận thu' }}
                        </span>
                        <span wire:loading wire:target="receivePayment" class="flex items-center gap-1">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL CHI TIẾT PHIẾU XUẤT ===== --}}
    @if($showStockOutModal && $selectedStockOut)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 print-hide" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showStockOutModal', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl z-10 overflow-hidden">

                <div class="flex items-center justify-between px-6 py-4 bg-indigo-50 border-b border-indigo-100">
                    <div>
                        <h3 class="text-sm font-bold text-indigo-900">Chi tiết phiếu xuất</h3>
                        <p class="text-xs text-indigo-600 font-mono">{{ $selectedStockOut->code }}</p>
                    </div>
                    <button wire:click="$set('showStockOutModal', false)"
                            class="p-1.5 rounded-lg text-indigo-400 hover:bg-indigo-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5">
                    <div class="grid grid-cols-3 gap-3 mb-5 bg-slate-50 rounded-xl p-3 border border-slate-100">
                        <div>
                            <p class="text-xs text-slate-400 uppercase font-bold tracking-wide mb-1">Khách hàng</p>
                            <p class="font-semibold text-slate-800 text-sm">{{ $selectedStockOut->customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase font-bold tracking-wide mb-1">Ngày tạo</p>
                            <p class="font-semibold text-slate-800 text-sm">{{ $selectedStockOut->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase font-bold tracking-wide mb-1">Người lập</p>
                            <p class="font-semibold text-slate-800 text-sm">{{ $selectedStockOut->creator->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50">
                                <tr class="text-xs font-bold text-slate-500 uppercase border-b border-slate-200">
                                    <th class="px-4 py-2.5 text-left">Vật tư</th>
                                    <th class="px-4 py-2.5 text-center">Số lượng</th>
                                    <th class="px-4 py-2.5 text-center">ĐVT</th>
                                    <th class="px-4 py-2.5 text-right">Đơn giá</th>
                                    <th class="px-4 py-2.5 text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($selectedStockOut->items as $item)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="px-4 py-2.5">
                                            <div class="font-medium text-slate-800">{{ $item->product->name ?? 'N/A' }}</div>
                                            <div class="text-xs text-slate-400 font-mono">{{ $item->product->code ?? '' }}</div>
                                        </td>
                                        <td class="px-4 py-2.5 text-center font-semibold tabular-nums">{{ number_format($item->quantity) }}</td>
                                        <td class="px-4 py-2.5 text-center text-xs text-slate-500">{{ $item->product->unit ?? '' }}</td>
                                        <td class="px-4 py-2.5 text-right text-slate-600 tabular-nums">
                                            {{ $item->unit_price ? number_format($item->unit_price) : '—' }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-semibold text-slate-800 tabular-nums">
                                            {{ $item->total_amount ? number_format($item->total_amount) : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            @if($selectedStockOut->items->sum('total_amount') > 0)
                                <tfoot>
                                    <tr class="bg-slate-50 border-t-2 border-slate-200">
                                        <td colspan="4" class="px-4 py-2.5 text-right text-xs font-bold text-slate-600 uppercase">Tổng cộng</td>
                                        <td class="px-4 py-2.5 text-right font-bold text-slate-800 tabular-nums">
                                            {{ number_format($selectedStockOut->items->sum('total_amount')) }}đ
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    @if($selectedStockOut->note)
                        <div class="mt-4 bg-amber-50 border border-amber-100 rounded-xl px-4 py-2.5">
                            <p class="text-xs text-amber-700 font-semibold mb-0.5">Ghi chú:</p>
                            <p class="text-sm text-slate-600 italic">{{ $selectedStockOut->note }}</p>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">
                    <button wire:click="$set('showStockOutModal', false)"
                            class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-sm transition">
                        Đóng
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== PRINT LAYOUT BIÊN BẢN ĐỐI SOÁT ===== --}}
    @if(count($printItems) > 0)
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        @foreach($printItems as $pItem)
        <div style="font-family: 'Times New Roman', serif; min-height: 297mm; padding: 20mm; page-break-after: always;">
            <div style="text-align:center; font-size:15px; font-weight:bold; text-transform:uppercase; margin-bottom:4px;">
                CÔNG TY CPĐT VÀ THI CÔNG HẠ TẦNG VINALPHA
            </div>
            <div style="text-align:center; font-size:20px; font-weight:bold; text-transform:uppercase; margin:14px 0 8px;">
                BIÊN BẢN ĐỐI SOÁT CÔNG NỢ
            </div>

            <table style="width:100%; margin-bottom:14px; font-size:12px;">
                <tr>
                    <td style="width:50%;">
                        <div><b>Phiếu xuất:</b> {{ $pItem->stockOut->code ?? 'N/A' }}</div>
                        <div><b>Khách hàng:</b> {{ $pItem->customer_name }}</div>
                    </td>
                    <td style="width:50%; text-align:right;">
                        @php
                            $pd = $pItem->due_date ?? ($pItem->delivered_at ? \Carbon\Carbon::parse($pItem->delivered_at)->addDays(30)->toDateString() : null);
                        @endphp
                        <div><b>Ngày giao:</b> {{ $pItem->delivered_at ? $pItem->delivered_at->format('d/m/Y') : 'N/A' }}</div>
                        <div><b>Hạn thanh toán:</b> {{ $pd ? \Carbon\Carbon::parse($pd)->format('d/m/Y') : 'Chưa đặt' }}</div>
                    </td>
                </tr>
            </table>

            <table border="1" style="width:100%; border-collapse:collapse; font-size:11px; margin-bottom:16px;">
                <thead style="background:#f1f5f9;">
                    <tr>
                        <th style="padding:5px 8px; text-align:left;">STT</th>
                        <th style="padding:5px 8px; text-align:left;">Tên vật tư (Mã)</th>
                        <th style="padding:5px 8px; text-align:center;">ĐVT</th>
                        <th style="padding:5px 8px; text-align:center;">SL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pItem->stockOut->items ?? [] as $idx => $ii)
                    <tr>
                        <td style="padding:4px 8px; text-align:center;">{{ $idx + 1 }}</td>
                        <td style="padding:4px 8px; font-weight:bold;">{{ $ii->product->name ?? '' }} <span style="font-weight:normal; font-family:monospace;">({{ $ii->product->code ?? '' }})</span></td>
                        <td style="padding:4px 8px; text-align:center;">{{ $ii->product->unit ?? '' }}</td>
                        <td style="padding:4px 8px; text-align:center; font-weight:bold;">{{ number_format($ii->quantity) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <table style="width:100%; margin-top:40px; text-align:center; font-size:12px;">
                <tr>
                    <td style="width:50%; padding-top:8px;"><b>Đại diện khách hàng</b><br><i style="font-size:10px;">(Ký, ghi rõ họ tên)</i><div style="height:80px;"></div><b>{{ $pItem->customer_name }}</b></td>
                    <td style="width:50%; padding-top:8px;"><b>Kế toán công ty</b><br><i style="font-size:10px;">(Ký, ghi rõ họ tên)</i><div style="height:80px;"></div></td>
                </tr>
            </table>
        </div>
        @endforeach
    </div>
    @endif
</div>

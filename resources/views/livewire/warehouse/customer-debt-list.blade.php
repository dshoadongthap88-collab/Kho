<div class="space-y-4 w-full" x-data="{ selectedIds: [] }">
    <!-- Thanh công cụ (Header nằm ngang) -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white p-4 rounded-xl shadow-sm border border-slate-200 print:hidden">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-bold text-slate-800">Công nợ hóa đơn</h2>
        </div>
        
        <div class="flex items-center gap-3 flex-1 justify-end">
            <!-- Tìm kiếm -->
            <div class="relative w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm tên khách, số phiếu..." class="w-full pl-9 pr-4 py-2 text-sm border border-slate-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Lọc -->
            <select wire:model.live="filterPayment" class="border border-slate-300 rounded-lg text-sm px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Tất cả hóa đơn</option>
                <option value="unpaid_or_debt">Đang nợ</option>
                <option value="paid">Đã thanh toán</option>
            </select>

            <!-- In báo cáo -->
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center gap-2 shadow-sm transition text-sm font-semibold whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>In báo cáo</span>
            </button>
        </div>
    </div>

    @if (session('message'))
        <div class="bg-green-100 border border-green-200 text-green-800 p-3 rounded-lg flex gap-3 shadow-sm items-center text-sm print:hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('message') }}
        </div>
    @endif

    {{-- ===== PHẦN IN: Header Công ty (chỉ hiện khi in) ===== --}}
    <div class="hidden print:block print-header" style="margin-bottom: 8px;">
        <div style="font-size: 16px; font-weight: bold; text-transform: uppercase; text-align: left;">CÔNG TY TNHH A</div>
        <div style="font-size: 12px; text-align: left;">Địa chỉ: 123 Tỉnh Lộ 10, Long An</div>
        <div style="font-size: 12px; text-align: left;">SĐT: 0708091050</div>
        <div style="margin-top: 16px; font-size: 18px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; text-align: center;">DANH SÁCH CÔNG NỢ</div>
        <div style="font-size: 11px; color: #666; margin-top: 4px; text-align: center;">Ngày in: {{ now()->format('d/m/Y H:i') }}</div>
        <hr style="margin-top: 10px; border-top: 1px solid #333;">
    </div>

    <!-- Bảng dữ liệu hướng ngang -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 w-full overflow-x-auto print:shadow-none print:border-none print:rounded-none">
        <table class="w-full text-left whitespace-nowrap table-auto border-collapse print-table">
            <thead class="bg-slate-100 text-slate-700 text-sm font-bold border-b border-slate-200 print:bg-white">
                <tr>
                    <th class="px-3 py-3 text-center print:hidden" style="width: 40px;">
                        <input type="checkbox" class="rounded" x-on:change="
                            if ($event.target.checked) {
                                selectedIds = @js($debts->pluck('id'));
                            } else {
                                selectedIds = [];
                            }
                        ">
                    </th>
                    <th class="px-4 py-3">Số phiếu</th>
                    <th class="px-4 py-3">Tên khách hàng</th>
                    <th class="px-4 py-3 text-right">Số tiền nợ (Tổng)</th>
                    <th class="px-4 py-3 text-right">Đã thanh toán</th>
                    <th class="px-4 py-3 text-right">Số tiền còn lại</th>
                    <th class="px-4 py-3 text-center">Hạn thanh toán</th>
                    <th class="px-4 py-3 text-center print:hidden">Tùy chỉnh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
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
                        $isOverdue = $remaining > 0 && $dueDate && $dueDate->lt(now());
                        $daysOverdueCount = $isOverdue ? $dueDate->diffInDays(now()) : 0;
                    @endphp
                    <tr class="hover:bg-blue-50/50 transition-colors {{ $isOverdue ? 'bg-red-50/50' : '' }} print-row"
                        x-show="selectedIds.length === 0 || selectedIds.includes({{ $report->id }})"
                        x-bind:class="{ 'print-row-hidden': selectedIds.length > 0 && !selectedIds.includes({{ $report->id }}) }">
                        <!-- Checkbox chọn in -->
                        <td class="px-3 py-3 text-center print:hidden">
                            <input type="checkbox" class="rounded" :value="{{ $report->id }}"
                                x-model.number="selectedIds">
                        </td>
                        <!-- Số phiếu -->
                        <td class="px-4 py-3">
                            <button wire:click="viewStockOutDetails({{ $report->stock_out_id }})" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline transition-colors text-left print:text-black print:no-underline">
                                {{ $report->stockOut->code ?? 'N/A' }}
                            </button>
                        </td>
                        
                        <!-- Tên khách hàng -->
                        <td class="px-4 py-3 font-semibold text-slate-800">
                            {{ explode(' (', $report->customer_name)[0] }}
                        </td>
                        
                        <!-- Tổng tiền (Nợ) -->
                        <td class="px-4 py-3 text-right font-bold text-slate-800">
                            {{ number_format($report->total_amount) }}
                        </td>
                        
                        <!-- Đã thanh toán -->
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600 print:text-black">
                            {{ number_format($report->paid_amount) }}
                        </td>
                        
                        <!-- Còn lại -->
                        <td class="px-4 py-3 text-right font-bold {{ $remaining > 0 ? 'text-red-600' : 'text-slate-400' }} print:text-black">
                            {{ number_format($remaining) }}
                        </td>
                        
                        <!-- Hạn TT -->
                        <td class="px-4 py-3 text-center">
                            @if($remaining == 0 && $report->total_amount > 0)
                                <span class="bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold print:bg-white print:text-black">Hoàn tất</span>
                            @else
                                <div class="{{ $isOverdue ? 'text-red-600 font-bold animate-pulse print:animate-none' : 'text-slate-600' }}">
                                    {{ $dueDate ? $dueDate->format('d/m/Y') : 'Chưa đặt hạn' }}
                                </div>
                                @if($isOverdue)
                                    <div class="text-[10px] text-red-500 font-bold">Quá hạn {{ $daysOverdueCount }} ngày</div>
                                @endif
                            @endif
                        </td>
                        
                        <!-- Tùy chỉnh (Action) -->
                        <td class="px-4 py-3 text-center print:hidden">
                            @if($remaining > 0)
                                <div class="flex items-center justify-center gap-2">
                                    <button wire:click="openPayModal({{ $report->id }})" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 flex items-center justify-center rounded transition" title="Thu tiền">
                                        💰
                                    </button>
                                    <button onclick="confirm('Khách hàng đã trả hết nợ hóa đơn này?') || event.stopImmediatePropagation()" wire:click="markAsFullyPaid({{ $report->id }})" class="bg-emerald-500 hover:bg-emerald-600 text-white px-2 py-1 flex items-center justify-center rounded transition" title="Xong nợ">
                                        ✅
                                    </button>
                                    <button wire:click="openEditModal({{ $report->id }})" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-2 py-1 flex items-center justify-center rounded transition" title="Chỉnh sửa">
                                        📝
                                    </button>
                                </div>
                            @else
                                <button wire:click="openEditModal({{ $report->id }})" class="text-slate-400 hover:text-blue-500 transition text-lg" title="Sửa lại nếu nhầm">
                                    📝
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                            Không tìm thấy dữ liệu công nợ nào hợp lệ.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== PHẦN IN: Footer ký tên (chỉ hiện khi in) ===== --}}
    <div class="hidden print:block" style="margin-top: 40px; page-break-inside: avoid;">
        <table style="width: 100%; text-align: center; font-size: 13px;">
            <tr>
                <td style="width: 33%; padding-top: 10px;">
                    <div style="font-weight: bold;">Người lập</div>
                    <div style="font-size: 11px; color: #888; font-style: italic;">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 70px;"></div>
                </td>
                <td style="width: 33%; padding-top: 10px;">
                    <div style="font-weight: bold;">Quản lý</div>
                    <div style="font-size: 11px; color: #888; font-style: italic;">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 70px;"></div>
                </td>
                <td style="width: 33%; padding-top: 10px;">
                    <div style="font-weight: bold;">Xác nhận khách hàng</div>
                    <div style="font-size: 11px; color: #888; font-style: italic;">(Ký, ghi rõ họ tên)</div>
                    <div style="height: 70px;"></div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Pagination -->
    @if($debts->hasPages())
        <div class="px-4 py-3 bg-white border border-slate-200 rounded-xl print:hidden">
            {{ $debts->links() }}
        </div>
    @endif

    <!-- Modal Thu Tiền / Sửa -->
    @if($showPayModal)
        <div class="fixed inset-0 z-50 overflow-y-auto print:hidden" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showPayModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-md sm:w-full border border-slate-200">
                    <div class="bg-blue-50 px-6 py-4 border-b border-blue-100 flex items-center gap-3">
                        <span class="text-2xl">{{ $isEditMode ? '📝' : '💰' }}</span>
                        <h3 class="text-lg font-bold text-blue-900">{{ $isEditMode ? 'Chỉnh sửa Số Tiền' : 'Thu Thêm Nợ' }}</h3>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Nhập số tiền (VNĐ)</label>
                            <input wire:model="payAmount" type="number" min="0" max="{{ $maxPayAmount }}" class="block w-full border border-slate-300 rounded-lg shadow-sm py-2 px-3 text-lg font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('payAmount') <span class="text-red-500 text-xs mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">📅 Hạn thanh toán</label>
                            <input wire:model="editDueDate" type="date" class="block w-full border border-slate-300 rounded-lg shadow-sm py-2 px-3 text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-[11px] text-slate-400 mt-1">Để trống nếu lấy mặc định (30 ngày sau giao).</p>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-lg border border-slate-200">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-slate-500">Tổng phiếu xuất:</span>
                                <span class="font-bold text-slate-800">{{ number_format($maxPayAmount) }} đ</span>
                            </div>
                            @if(!$isEditMode)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Đã thu trước đó:</span>
                                <span class="font-bold text-emerald-600">{{ number_format($maxPayAmount - $payAmount) }} đ</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 flex justify-end gap-3 border-t border-slate-200">
                        <button type="button" wire:click="$set('showPayModal', false)" class="px-4 py-2 bg-white border border-slate-300 rounded-lg text-sm font-semibold text-slate-700 hover:bg-slate-100">
                            Hủy
                        </button>
                        <button type="button" wire:click="receivePayment" class="px-4 py-2 bg-blue-600 rounded-lg text-sm font-bold text-white shadow-md hover:bg-blue-700 active:scale-95 transition-transform">
                            {{ $isEditMode ? 'Cập Nhật Lại' : 'Xác Nhận Thu' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Chi Tiết Phiếu Xuất -->
    @if($showStockOutModal && $selectedStockOut)
        <div class="fixed inset-0 z-50 overflow-y-auto print:hidden" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/50 backdrop-blur-sm" wire:click="$set('showStockOutModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-4xl sm:w-full border border-slate-200">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📄</span>
                            <h3 class="text-lg font-bold text-indigo-900">Chi Tiết Phiếu Xuất: {{ $selectedStockOut->code }}</h3>
                        </div>
                        <button wire:click="$set('showStockOutModal', false)" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="px-6 py-5">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Khách hàng</p>
                                <p class="font-bold text-slate-800">{{ $selectedStockOut->customer_name }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Ngày tạo phiếu</p>
                                <p class="font-bold text-slate-800">{{ $selectedStockOut->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Người lập phiếu</p>
                                <p class="font-bold text-slate-800">{{ $selectedStockOut->creator->name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="font-bold text-slate-700 mb-3 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                Danh sách mặt hàng
                            </h4>
                            <div class="overflow-hidden border border-slate-200 rounded-lg">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 text-slate-600 text-xs font-bold uppercase">
                                        <tr>
                                            <th class="px-4 py-2">Sản phẩm</th>
                                            <th class="px-4 py-2 text-center">Số lượng</th>
                                            <th class="px-4 py-2 text-right">Đơn giá</th>
                                            <th class="px-4 py-2 text-right">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 italic text-sm">
                                        @foreach($selectedStockOut->items as $item)
                                            <tr>
                                                <td class="px-4 py-2">
                                                    <div class="font-medium text-slate-800">{{ $item->product->name }}</div>
                                                    <div class="text-[10px] text-slate-500">Mã: {{ $item->product->code }}</div>
                                                </td>
                                                <td class="px-4 py-2 text-center">{{ number_format($item->quantity) }}</td>
                                                <td class="px-4 py-2 text-right">{{ number_format($item->unit_price) }} đ</td>
                                                <td class="px-4 py-2 text-right font-bold text-slate-700">{{ number_format($item->total_amount) }} đ</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-indigo-50/30">
                                        <tr class="font-bold text-indigo-900">
                                            <td colspan="3" class="px-4 py-2 text-right uppercase text-xs">Tổng cộng thanh toán:</td>
                                            <td class="px-4 py-2 text-right text-base">{{ number_format($selectedStockOut->items->sum('total_amount')) }} đ</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        @if($selectedStockOut->note)
                        <div class="mt-4">
                            <h4 class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-1">Ghi chú</h4>
                            <p class="text-sm text-slate-600 bg-amber-50 p-3 rounded-lg border border-amber-100 italic">
                                "{{ $selectedStockOut->note }}"
                            </p>
                        </div>
                        @endif
                    </div>
                    
                    <div class="bg-slate-50 px-6 py-4 flex justify-end border-t border-slate-200">
                        <button type="button" wire:click="$set('showStockOutModal', false)" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-bold shadow-md transition-all active:scale-95">
                            Đóng cửa sổ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- ===== CSS In ấn A4 ===== --}}
<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 10mm 12mm;
    }
    body * {
        visibility: hidden;
    }
    /* Hiển thị phần chính */
    .space-y-4,
    .space-y-4 * {
        visibility: visible;
    }
    .space-y-4 {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    /* Ẩn phần không cần in */
    .print\\:hidden,
    nav, .sidebar, header, footer, aside,
    [wire\\:loading], .fixed {
        display: none !important;
    }
    /* Hiện phần chỉ dành cho in */
    .print-header {
        display: block !important;
        visibility: visible !important;
    }
    /* Bảng in */
    .print-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    .print-table th,
    .print-table td {
        border: 1px solid #333;
        padding: 4px 6px;
        white-space: normal;
        word-break: break-word;
    }
    .print-table thead {
        background: #eee !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    /* Ẩn dòng không tick khi có check */
    .print-row-hidden {
        display: none !important;
    }
    /* Đảm bảo bảng và footer không bị cắt */
    .print-table tr {
        page-break-inside: avoid;
    }
    /* Reset style cho in */
    button {
        color: #000 !important;
        text-decoration: none !important;
    }
    .bg-white, .bg-slate-100 {
        background: white !important;
    }
    .rounded-xl {
        border-radius: 0 !important;
    }
    .shadow-sm {
        box-shadow: none !important;
    }
}
</style>

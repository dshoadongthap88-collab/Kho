<div>

    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Date Filter Standard -->
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:ring-2 focus-within:ring-blue-100">
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Giao từ</label>
                    <input type="date" wire:model.live="dateFrom" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                </div>
                <div class="w-px h-4 bg-slate-200 mx-1"></div>
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Đến</label>
                    <input type="date" wire:model.live="dateTo" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                </div>
            </div>

            <!-- Search Standard -->
            <div class="relative w-64">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm tên khách, số phiếu..." class="w-full pl-9 pr-4 py-2 text-xs font-bold rounded-xl border-slate-200 focus:ring-blue-500 shadow-sm transition-all">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filter Payment -->
            <select wire:model.live="filterPayment" class="border-slate-200 rounded-xl px-4 py-2 text-xs font-bold focus:ring-blue-500 shadow-sm">
                <option value="">Tất cả hóa đơn</option>
                <option value="unpaid_or_debt">🔴 Đang nợ</option>
                <option value="paid">🟢 Đã thanh toán</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            @if(count($selectedIds) > 0)
                <div class="flex items-center gap-2 pr-3 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4 duration-300">
                    <span class="text-[10px] font-black text-blue-600 bg-blue-50 px-2 py-1 rounded">Chọn: {{ count($selectedIds) }}</span>
                    <button type="button" onclick="confirm('Xóa {{ count($selectedIds) }} bản ghi nợ đã chọn?') || event.stopImmediatePropagation()" wire:click="deleteSelected" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-black transition">
                        <span>🗑️</span> XÓA
                    </button>
                    <button type="button" wire:click="printSelected" class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg text-xs font-black transition">
                        <span>🖨️</span> IN GHÉP
                    </button>
                </div>
            @endif

            <button wire:click="exportExcel" class="bg-emerald-600 font-black hover:bg-emerald-700 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md shadow-emerald-100">
                <span>📊</span> EXCEL
            </button>
            <button onclick="window.print()" class="bg-slate-800 font-black hover:bg-slate-900 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md">
                <span>📄</span> PDF
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
                    <th class="px-6 py-4 w-10 text-center no-print bg-slate-100/30">
                        <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $debts->pluck('id')->toArray()) }}])" 
                               {{ count($selectedIds) >= count($debts->pluck('id')->toArray()) && count($debts) > 0 ? 'checked' : '' }}
                               class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
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
                    <tr class="hover:bg-slate-50/80 transition group {{ $isOverdue ? 'bg-red-50/30' : '' }} {{ in_array($report->id, $selectedIds) ? 'bg-blue-50/30 is-selected' : '' }} print-row">
                        <td class="px-6 py-4 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $report->id }}" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
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
                                    <button wire:click="toggleSelectAll([{{ $report->id }}])" class="text-slate-400 hover:text-blue-600 p-1" title="In phiếu nợ">🖨️</button>
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


    <!-- PHẦN IN CHI TIẾT CÔNG NỢ (Sổ nợ chi tiết) -->
    @if(count($printItems) > 0)
    <div class="hidden print:block fixed inset-0 bg-white z-[9999]">
        @foreach($printItems as $pItem)
        <div class="print-page p-8 bg-white" style="font-family: 'Times New Roman', serif; min-height: 297mm; page-break-after: always;">
            <div class="flex justify-between items-start mb-6 border-b-2 border-slate-900 pb-4">
                <div>
                    <h1 class="text-xl font-black uppercase">CÔNG TY TNHH SANE</h1>
                    <p class="text-[11px] font-bold text-slate-500">Long An - SĐT: 0708091050</p>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tighter">BIÊN BẢN ĐỐI SOÁT CÔNG NỢ</h2>
                    <p class="text-xs font-bold text-slate-500 mt-1 italic">Phiếu xuất: <span class="text-indigo-700 NOT-italic">{{ $pItem->stockOut->code ?? 'N/A' }}</span></p>
                </div>
            </div>

            <div class="bg-slate-50 p-4 rounded-lg border-2 border-slate-900 mb-6 grid grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Khách hàng</p>
                    <p class="font-black text-slate-800 text-lg uppercase">{{ $pItem->customer_name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Hạn thanh toán</p>
                    <p class="font-black {{ \Carbon\Carbon::parse($pItem->due_date)->lt(now()) ? 'text-red-700' : 'text-slate-800' }}">
                        {{ $pItem->due_date ? \Carbon\Carbon::parse($pItem->due_date)->format('d/m/Y') : 'Chưa xác định' }}
                    </p>
                </div>
            </div>

            <p class="font-black text-[11px] uppercase mb-2 text-slate-800 italic">Chi tiết hàng hóa bàn giao & Nợ đọng:</p>
            <table class="w-full border-collapse border-2 border-slate-900 mb-8">
                <thead>
                    <tr class="bg-slate-100 uppercase text-[10px] font-black">
                        <th class="border border-slate-900 px-2 py-2 text-center w-10">STT</th>
                        <th class="border border-slate-900 px-2 py-2 text-left">Tên sản phẩm / Quy cách</th>
                        <th class="border border-slate-900 px-2 py-2 text-center w-14">ĐVT</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-20">Lượng</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-24">Đơn giá</th>
                        <th class="border border-slate-900 px-2 py-2 text-right w-28">Thành tiền</th>
                    </tr>
                </thead>
                <tbody class="text-[12px]">
                    @if($pItem->stockOut)
                        @foreach($pItem->stockOut->items as $idx => $ii)
                        <tr>
                            <td class="border border-slate-900 px-2 py-2 text-center">{{ $idx + 1 }}</td>
                            <td class="border border-slate-900 px-2 py-2 font-bold">{{ $ii->product->name }} ({{ $ii->product->code }})</td>
                            <td class="border border-slate-900 px-2 py-2 text-center italic">{{ $ii->product->unit }}</td>
                            <td class="border border-slate-900 px-2 py-2 text-right font-bold">{{ number_format($ii->quantity, 1) }}</td>
                            <td class="border border-slate-900 px-2 py-2 text-right italic text-slate-500">{{ number_format($ii->unit_price) }}</td>
                            <td class="border border-slate-900 px-2 py-2 text-right font-black">{{ number_format($ii->total_amount) }}</td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-black text-slate-900">
                        <td colspan="5" class="border border-slate-900 px-2 py-2 text-right uppercase text-xs">Tổng giá trị đơn hàng:</td>
                        <td class="border border-slate-900 px-2 py-2 text-right text-[13px] font-black underline">{{ number_format($pItem->total_amount) }} đ</td>
                    </tr>
                    <tr class="font-bold text-emerald-700 bg-emerald-50/30">
                        <td colspan="5" class="border border-slate-900 px-2 py-2 text-right uppercase text-xs">Số tiền đã trả:</td>
                        <td class="border border-slate-900 px-2 py-2 text-right text-[13px]">{{ number_format($pItem->paid_amount) }} đ</td>
                    </tr>
                    <tr class="bg-rose-50 font-black text-red-700">
                        <td colspan="5" class="border border-slate-900 px-2 py-2 text-right uppercase text-xs">DƯ NỢ CÒN LẠI:</td>
                        <td class="border border-slate-900 px-2 py-2 text-right text-lg underline decoration-double">{{ number_format($pItem->total_amount - $pItem->paid_amount) }} đ</td>
                    </tr>
                </tfoot>
            </table>

            <div class="grid grid-cols-2 gap-4 text-center mt-12 mb-8">
                <div>
                    <p class="font-bold text-sm uppercase">Đại diện khách hàng</p>
                    <p class="text-[10px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-black uppercase tracking-tighter">{{ $pItem->customer_name }}</p>
                </div>
                <div>
                    <p class="font-bold text-sm uppercase">Kế toán công ty</p>
                    <p class="text-[10px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-black">............................................</p>
                </div>
            </div>

            <div class="text-right mt-12 text-[9px] text-slate-400 italic">
                Hệ thống tự động xuất lúc: {{ now()->format('d/m/Y H:i:s') }}
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
    <style>
    @media print {
        @page { size: A4 portrait; margin: 0; }
        nav, .no-print, [wire\\:loading], button, select, input { display: none !important; }
        body { background: white !important; margin: 0 !important; padding: 0 !important; }
    }
    </style>
</div>

<div x-data x-on:trigger-print.window="setTimeout(() => window.print(), 300)">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            .print-only { display: block !important; }
            nav, h1 { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
            @page { size: A4 landscape; margin: 0; }
        }
    </style>

    <!-- Filters and Actions -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-2 mb-6 no-print">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Date Filters -->
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Từ ngày</label>
                    <input type="date" wire:model.live="dateFrom" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                </div>
                <div class="w-px h-4 bg-slate-200 mx-1"></div>
                <div class="flex items-center gap-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">Đến ngày</label>
                    <input type="date" wire:model.live="dateTo" class="text-xs border-none focus:ring-0 p-0 font-bold text-slate-700">
                </div>
            </div>

            <!-- Search -->
            <div class="relative w-64">
                <input wire:model.live.debounce.300ms="searchQuery" type="text" placeholder="Tìm số thu hồi, vật tư..." class="w-full pl-9 pr-4 py-2 text-xs font-bold rounded-xl border-slate-200 focus:ring-indigo-500 shadow-sm transition-all">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Status Filter -->
            <select wire:model.live="status" class="border-slate-200 rounded-xl px-4 py-2 text-xs font-bold focus:ring-indigo-500 shadow-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Đang chờ</option>
                <option value="approved">Đã duyệt</option>
                <option value="completed">Đã thu hồi</option>
                <option value="cancelled">Đã hủy</option>
            </select>

            <!-- Stock Out Filter -->
            <select wire:model.live="stockOutId" class="border-slate-200 rounded-xl px-4 py-2 text-xs font-bold focus:ring-indigo-500 shadow-sm">
                <option value="">Tất cả Phiếu Xuất</option>
                @foreach($stockOuts as $so)
                    <option value="{{ $so->id }}">{{ $so->code }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="exportExcel" class="bg-emerald-600 font-black hover:bg-emerald-700 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md shadow-emerald-100">
                <span>📊</span> EXCEL
            </button>
            <button wire:click="printAll" class="bg-slate-800 font-black hover:bg-slate-900 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md">
                <span>📄</span> IN TẤT CẢ
            </button>
            @if(count($selectedIds) > 0)
            <button wire:click="printSelected" class="bg-indigo-800 font-black hover:bg-indigo-900 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md">
                <span>🖨️</span> IN ĐÃ CHỌN ({{ count($selectedIds) }})
            </button>
            @endif
            <button wire:click="create" class="bg-indigo-600 font-black hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md shadow-indigo-100">
                <span>➕</span> TẠO THU HỒI
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 no-print">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 no-print">
            {{ session('error') }}
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-4 gap-2 mb-6">
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-2 shadow-sm">
            <p class="text-xs font-bold text-indigo-600 uppercase mb-1">Tổng phiếu</p>
            <p class="text-2xl font-black text-indigo-700">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-2 shadow-sm">
            <p class="text-xs font-bold text-yellow-600 uppercase mb-1">Đang chờ</p>
            <p class="text-2xl font-black text-yellow-700">{{ $summary['pending'] }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-2 shadow-sm">
            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Đã duyệt</p>
            <p class="text-2xl font-black text-blue-700">{{ $summary['approved'] }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-xl p-2 shadow-sm">
            <p class="text-xs font-bold text-green-600 uppercase mb-1">Tổng SL thu hồi</p>
            <p class="text-2xl font-black text-green-700">{{ number_format($summary['total_quantity'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden border no-print">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-black tracking-widest">
                    <th class="px-2 py-2 w-10 text-center">
                        <input type="checkbox" wire:click="toggleSelectAll({{ collect($recoveries)->pluck('id') }})" 
                            {{ count(array_intersect(collect($recoveries)->pluck('id')->toArray(), $selectedIds)) === collect($recoveries)->count() && collect($recoveries)->count() > 0 ? 'checked' : '' }}
                            class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </th>
                    <th class="px-2 py-2">Số thu hồi</th>
                    <th class="px-2 py-2">Số PX</th>
                    <th class="px-2 py-2">Mã vật tư</th>
                    <th class="px-2 py-2">Tên vật tư</th>
                    <th class="px-2 py-2 text-center">Số lượng</th>
                    <th class="px-2 py-2 text-center">ĐVT</th>
                    <th class="px-2 py-2">Ngày thu hồi</th>
                    <th class="px-2 py-2">Trạng thái</th>
                    <th class="px-2 py-2 text-right no-print">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($recoveries as $recovery)
                    <tr class="hover:bg-slate-50/80 transition group {{ in_array($recovery->id, $selectedIds) ? 'bg-indigo-50/50' : '' }}">
                        <td class="px-2 py-1.5 text-center">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $recovery->id }}" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </td>
                        <td class="px-2 py-1.5 font-mono font-black text-indigo-700">{{ $recovery->recovery_number }}</td>
                        <td class="px-2 py-1.5 font-mono text-xs text-gray-600">{{ $recovery->stockOut?->code ?? '-' }}</td>
                        <td class="px-2 py-1.5 font-mono text-xs">{{ $recovery->product?->code ?? '' }}</td>
                        <td class="px-2 py-1.5 text-gray-800">{{ $recovery->product?->name ?? '' }}</td>
                        <td class="px-2 py-1.5 text-center font-bold text-indigo-700">{{ number_format($recovery->quantity, 2) }}</td>
                        <td class="px-2 py-1.5 text-center text-gray-600">{{ $recovery->unit ?? '-' }}</td>
                        <td class="px-2 py-1.5 text-gray-600">{{ $recovery->recovery_date->format('d/m/Y') }}</td>
                        <td class="px-2 py-1.5">
                            @switch($recovery->status)
                                @case('pending')
                                    <span class="bg-yellow-100 text-yellow-700 px-1.5 py-1 text-[11px] rounded text-xs">Đang chờ</span>
                                    @break
                                @case('approved')
                                    <span class="bg-blue-100 text-blue-700 px-1.5 py-1 text-[11px] rounded text-xs">Đã duyệt</span>
                                    @break
                                @case('completed')
                                    <span class="bg-green-100 text-green-700 px-1.5 py-1 text-[11px] rounded text-xs">Đã thu hồi</span>
                                    @break
                                @case('cancelled')
                                    <span class="bg-red-100 text-red-700 px-1.5 py-1 text-[11px] rounded text-xs">Đã hủy</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-2 py-1.5 text-right flex gap-1 justify-end no-print">
                            <button wire:click="printSingle({{ $recovery->id }})" class="text-slate-400 hover:text-indigo-600 p-1 transition-all hover:scale-110" title="In phiếu">🖨️</button>
                            <button wire:click="edit({{ $recovery->id }})" class="text-indigo-500 hover:text-indigo-700 p-1 transition-all hover:scale-110" title="Sửa">📝</button>
                            <button wire:confirm="Xóa phiếu thu hồi {{ $recovery->recovery_number }}?" wire:click="destroy({{ $recovery->id }})" class="text-rose-400 hover:text-rose-600 p-1 transition-all hover:scale-110" title="Xóa">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-gray-500">Chưa có phiếu thu hồi nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal Create/Edit -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showCreateModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-2 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ $editingRecovery ? 'Chỉnh sửa phiếu thu hồi' : 'Tạo phiếu thu hồi mới' }}
                        </h3>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số phiếu thu hồi</label>
                                    <input type="text" wire:model="recoveryNumber" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 {{ $editingRecovery ? 'bg-gray-100' : '' }}" placeholder="SCR-2026-000001">
                                    @if(!$editingRecovery)
                                        <button wire:click="generateRecoveryNumber" type="button" class="text-xs text-indigo-600 hover:text-indigo-800 mt-1">Sinh số tự động</button>
                                    @endif
                                    @error('recoveryNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số PX (tùy chọn)</label>
                                    <select wire:model="stockOutIdForm" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="">-- Không liên kết --</option>
                                        @foreach($stockOuts as $so)
                                            <option value="{{ $so->id }}">{{ $so->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Vật tư thu hồi <span class="text-red-500">*</span></label>
                                    <select wire:model.live="productId" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="">-- Chọn vật tư --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">
                                                {{ $product->code }} - {{ $product->name }} ({{ $product->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('productId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Đơn vị tính</label>
                                    <input type="text" wire:model="unit" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-100" readonly>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số lượng <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" wire:model="quantity" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ngày thu hồi <span class="text-red-500">*</span></label>
                                    <input type="date" wire:model="recoveryDate" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    @error('recoveryDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                                    <select wire:model="statusForm" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="pending">Đang chờ</option>
                                        <option value="approved">Đã duyệt</option>
                                        <option value="completed">Đã thu hồi</option>
                                        <option value="cancelled">Đã hủy</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                                <textarea wire:model="notes" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Ghi chú thêm..."></textarea>
                                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" wire:click="save" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:w-auto sm:text-sm">
                            Lưu phiếu
                        </button>
                        <button type="button" wire:click="$set('showCreateModal', false)" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- PRINT TEMPLATE -->
    <div class="hidden print:block absolute inset-0 bg-white z-[9999] w-full" style="min-height: 100vh;">
        @php
            $printList = $isPrintingSelected ? collect($recoveries)->whereIn('id', $selectedIds) : ($printingRecoveryId ? collect($recoveries)->where('id', $printingRecoveryId) : $recoveries);
            $firstRecovery = $printList->first();
        @endphp
        
        @if($printList->count() > 0)
        <div style="font-family: 'Times New Roman', serif; padding: 15mm; width: 100%;">
            <!-- Header -->
            <div class="mb-4 text-left">
                <div class="font-bold uppercase" style="font-size: 18px; letter-spacing: 1px; color: black;">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ THI CÔNG HẠ TẦNG V-ALPHA</div>
                <div class="font-bold uppercase mt-1" style="font-size: 16px; color: black;">DỰ ÁN: {{ mb_strtoupper($firstRecovery->stockOut?->project_name ?? (session('current_house', 1) == 2 ? 'HẬU NGHĨA' : (session('current_house', 1) == 3 ? 'CẦN GIỜ' : (session('current_house', 1) == 4 ? 'CẦN GIUỘC' : 'HÓC MÔN'))), 'UTF-8') }}</div>
            </div>
            <div style="border-bottom: 2px solid #000; margin-bottom: 20px;"></div>

            <!-- Title -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold uppercase tracking-widest text-black">PHIẾU THU HỒI PHẾ PHẨM</h2>
                <p class="italic text-[13px] mt-1">
                    Ngày {{ now()->format('d') }} tháng {{ now()->format('m') }} năm {{ now()->format('Y') }}
                </p>
            </div>

            <!-- Table -->
            <table class="w-full border-collapse border border-black text-[13px] mb-8">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-2 w-10 text-center font-bold">STT</th>
                        <th class="border border-black px-2 py-2 text-left w-24 font-bold">Số thu hồi</th>
                        <th class="border border-black px-2 py-2 text-left w-24 font-bold">Số PX</th>
                        <th class="border border-black px-2 py-2 text-left w-24 font-bold">Mã Vật Tư</th>
                        <th class="border border-black px-2 py-2 text-left font-bold">TÊN VẬT TƯ</th>
                        <th class="border border-black px-2 py-2 text-center w-24 font-bold">SL Thu Hồi</th>
                        <th class="border border-black px-2 py-2 text-center w-16 font-bold">ĐVT</th>
                        <th class="border border-black px-2 py-2 text-center w-24 font-bold">Ngày TH</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($printList as $index => $recovery)
                    <tr>
                        <td class="border border-black px-2 py-2 text-center">{{ $index + 1 }}</td>
                        <td class="border border-black px-2 py-2 font-mono uppercase">{{ $recovery->recovery_number }}</td>
                        <td class="border border-black px-2 py-2 font-mono">{{ $recovery->stockOut?->code ?? '' }}</td>
                        <td class="border border-black px-2 py-2 font-mono uppercase">{{ $recovery->product?->code ?? '' }}</td>
                        <td class="border border-black px-2 py-2 font-semibold">{{ $recovery->product?->name ?? '' }}</td>
                        <td class="border border-black px-2 py-2 text-center font-bold">{{ number_format($recovery->quantity, 2) }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ $recovery->unit ?? '' }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ $recovery->recovery_date->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Footer / Signatures -->
            <div class="grid grid-cols-4 gap-2 text-center mt-8 mb-8">
                <div>
                    <p class="font-bold text-[14px]">Bộ Phận An Ninh</p>
                    <p class="text-[12px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-bold uppercase text-[14px]">........................</p>
                </div>
                <div>
                    <p class="font-bold text-[14px]">Người nhận hàng</p>
                    <p class="text-[12px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-bold uppercase text-[14px]">........................</p>
                </div>
                <div>
                    <p class="font-bold text-[14px]">Thủ kho</p>
                    <p class="text-[12px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-bold uppercase text-[14px]">........................</p>
                </div>
                <div>
                    <p class="font-bold text-[14px]">Trưởng ca</p>
                    <p class="text-[12px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-bold uppercase text-[14px]">........................</p>
                </div>
            </div>

            <div class="text-right mt-12 mb-4 text-[11px] italic text-gray-500">
                In lúc: {{ date('d/m/Y H:i') }}
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('trigger-print', () => {
            setTimeout(() => {
                window.print();
            }, 300);
        });
    });
</script>
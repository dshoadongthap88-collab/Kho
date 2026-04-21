<div x-data x-on:trigger-print.window="setTimeout(() => window.print(), 300)">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            .print-only { display: block !important; }
            /* Hide global layout elements when printing */
            nav, h1 { display: none !important; }
            main { padding: 0 !important; margin: 0 !important; max-width: 100% !important; }
        }
    </style>
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap items-center justify-between gap-4 mb-6 no-print">
        <div class="flex flex-wrap items-center gap-3">
            <!-- Date Filter Standard -->
            <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-sm transition-all focus-within:ring-2 focus-within:ring-amber-100">
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

            <!-- Search Standard -->
            <div class="relative w-64">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Tìm số PO, nhà cung cấp..." class="w-full pl-9 pr-4 py-2 text-xs font-bold rounded-xl border-slate-200 focus:ring-amber-500 shadow-sm transition-all">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filter Status -->
            <select wire:model.live="filterStatus" class="border-slate-200 rounded-xl px-4 py-2 text-xs font-bold focus:ring-amber-500 shadow-sm">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">📊 Đã trình</option>
                <option value="confirmed">✅ Đã duyệt</option>
                <option value="received">📦 Đã nhận</option>
                <option value="cancelled">❌ Đã hủy</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            @if(count($selectedIds) > 0)
                <div class="flex items-center gap-2 pr-3 border-r border-slate-300 mr-2 animate-in slide-in-from-right-4 duration-300">
                    <span class="text-[10px] font-black text-amber-600 bg-amber-50 px-2 py-1 rounded">Chọn: {{ count($selectedIds) }}</span>
                    <button wire:click="deleteSelected" wire:confirm="Xóa {{ count($selectedIds) }} đơn hàng đã chọn?" class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white rounded-lg text-xs font-black transition">
                        <span>🗑️</span> XÓA
                    </button>
                    <button wire:click="printSelected" class="flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white rounded-lg text-xs font-black transition">
                        <span>🖨️</span> IN GỘP
                    </button>
                </div>
            @endif

            <button wire:click="exportExcel" class="bg-emerald-600 font-black hover:bg-emerald-700 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md shadow-emerald-100">
                <span>📊</span> EXCEL
            </button>
            <button onclick="window.print()" class="bg-slate-800 font-black hover:bg-slate-900 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md">
                <span>📄</span> IN PDF
            </button>
            <button wire:click="openModal" class="bg-amber-600 font-black hover:bg-amber-700 text-white px-5 py-2 rounded-xl text-xs flex items-center gap-2 transition shadow-md shadow-amber-100">
                <span>➕</span> TẠO ĐỀ XUẤT
            </button>
            <button wire:click="openOfficeModal" class="bg-indigo-600 font-black hover:bg-indigo-700 text-white px-5 py-2 rounded-xl text-xs transition shadow-md shadow-indigo-100">
                🏢 MUA VĂN PHÒNG
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

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border no-print">
        <table class="w-full text-left border-collapse">
            <thead>
                @php
                    $idsOnPage = $orders->pluck('id')->toArray();
                @endphp
                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase text-[11px] font-black tracking-widest">
                    <th class="px-6 py-4 w-10 text-center no-print bg-slate-100/30">
                        <input type="checkbox" wire:click="toggleSelectAll([{{ implode(',', $idsOnPage) }}])" {{ count($selectedIds) >= count($idsOnPage) && count($idsOnPage) > 0 ? 'checked' : '' }} class="rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                    </th>
                    <th class="px-4 py-3">Số PO</th>
                    <th class="px-4 py-3">Nhà cung cấp</th>
                    <th class="px-4 py-3">Người đặt</th>
                    <th class="px-4 py-3">Ngày đặt hàng</th>
                    <th class="px-4 py-3">Ngày dự kiến giao</th>
                    <th class="px-4 py-3">Tổng tiền</th>
                    <th class="px-4 py-3 no-print">Trạng thái</th>
                    <th class="px-4 py-3 text-right no-print">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/80 transition group {{ in_array($order->id, $selectedIds) ? 'bg-amber-50 is-selected' : '' }}">
                        <td class="px-6 py-4 text-center no-print">
                            <input type="checkbox" wire:model.live="selectedIds" value="{{ $order->id }}" class="w-4 h-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer">
                        </td>
                        <td class="px-4 py-3 font-mono font-black text-indigo-700">{{ $order->po_number }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $order->supplier->name ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-gray-700 text-sm">
                            <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full text-xs">👤 {{ $order->user?->name ?? 'Chưa ghi' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm">{{ $order->order_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-600 text-sm">{{ $order->expected_delivery_date?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-semibold text-amber-700">{{ number_format($order->total_amount, 0, ',', '.') }} đ</td>
                        <td class="px-4 py-3 no-print">
                            @switch($order->status)
                                @case('pending')
                                    <span class="bg-yellow-100 text-yellow-700 px-2 py-1 rounded text-xs">Đã trình</span>
                                    @break
                                @case('confirmed')
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">Đã duyệt</span>
                                    @break
                                @case('received')
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">Đã nhận</span>
                                    @break
                                @case('cancelled')
                                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs">Đã hủy</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-right flex gap-1 justify-end no-print">
                            <button wire:click="toggleSelectAll([{{ $order->id }}])" class="text-slate-400 hover:text-amber-600 p-1" title="In phiếu">🖨️</button>
                            @if($order->status === 'pending')
                                <button wire:click="confirmOrder({{ $order->id }})" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-[10px] font-bold transition" title="Xác nhận">DUYỆT</button>
                            @endif
                            <button wire:click="openModal({{ $order->id }})" class="text-blue-500 hover:text-blue-700 p-1" title="Sửa">📝</button>
                            <button onclick="confirm('Xoá đơn hàng này?') || event.stopImmediatePropagation()" wire:click="delete({{ $order->id }})" class="text-red-500 hover:text-red-700 p-1" title="Xoá">🗑️</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">Chưa có phiếu đề xuất nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 bg-gray-50 border-t">
            {{ $orders->links() }}
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">{{ $isEdit ? 'Chỉnh sửa phiếu đề xuất' : 'Tạo phiếu đề xuất mới' }}</h3>
                        
                        <div class="space-y-4">
                            <!-- Basic Info -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số phiếu (PO)</label>
                                    <input type="text" wire:model="po_number" {{ !$isEdit ? 'readOnly' : '' }} class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 {{ !$isEdit ? 'bg-gray-100' : '' }}" placeholder="PO-2024-001">
                                    @if(!$isEdit)
                                        <small class="text-gray-500">Tự động sinh (tiếp theo)</small>
                                    @endif
                                    @error('po_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nhà cung cấp/Khách hàng <span class="text-red-500">*</span></label>
                                    <select wire:model="supplier_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="">-- Chọn từ danh sách --</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ngày đặt hàng</label>
                                    <input type="date" wire:model="order_date" readOnly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-100 cursor-not-allowed">
                                    <small class="text-gray-500">Ngày hôm nay (không thể sửa)</small>
                                    @error('order_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Ngày dự kiến giao</label>
                                    <input type="date" wire:model="expected_delivery_date" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                    <small class="text-gray-500">Mặc định +3 ngày từ ngày đặt</small>
                                    @error('expected_delivery_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Trạng thái phê duyệt</label>
                                    <select wire:model="status" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="pending">Chờ xác nhận</option>
                                        <option value="confirmed">Đã xác nhận</option>
                                        <option value="received">Đã nhận hàng</option>
                                        <option value="cancelled">Đã hủy</option>
                                    </select>
                                    @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tổng tiền</label>
                                    <input type="number" step="0.01" wire:model="total_amount" readOnly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-100">
                                    @error('total_amount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Ghi chú</label>
                                <textarea wire:model="notes" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2" placeholder="Ghi chú thêm..."></textarea>
                                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Items Section -->
                            <div class="border-t pt-4">
                                <h4 class="font-semibold mb-3 text-gray-800">Mục hàng đặt</h4>
                                
                                <div class="space-y-3 mb-4">
                                    <div class="grid grid-cols-12 gap-2 pb-2">
                                        <div class="col-span-4">
                                            <select wire:model.live="newItemProductId" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                                <option value="">-- Gõ để chọn NVL cảnh báo thiếu hụt --</option>
                                                @foreach($lowStockProducts as $product)
                                                    <option value="{{ $product->id }}">
                                                        {{ $product->code }} - {{ $product->name }} (Hãng: {{ $product->brand ?? 'N/A' }} | Tồn: {{ floatval($product->inventory?->quantity ?? 0) }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <p class="text-[10px] text-gray-500 mt-1">Chỉ hiển thị các nguyên vật liệu có tồn kho ≤ định mức an toàn.</p>
                                        </div>
                                        <div class="col-span-2">
                                            <input type="number" step="0.01" wire:model="newItemQuantity" placeholder="SL Mua" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                        </div>
                                        <div class="col-span-2 no-print">
                                            @php
                                                $selectedProd = $newItemProductId ? $products->firstWhere('id', $newItemProductId) : null;
                                                $invQty = $selectedProd ? ($selectedProd->inventory->quantity ?? 0) : 0;
                                            @endphp
                                            <div class="w-full border border-gray-200 bg-gray-100 rounded-md shadow-sm p-2 text-sm text-gray-500 whitespace-nowrap overflow-hidden text-ellipsis shadow-inner" title="Tồn kho hiện tại: {{ floatval($invQty) }}">
                                                Tồn: {{ floatval($invQty) }}
                                            </div>
                                        </div>
                                        <div class="col-span-2">
                                            <input type="number" step="0.01" wire:model="newItemUnitPrice" placeholder="Đơn giá" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm bg-blue-50">
                                        </div>
                                        <div class="col-span-2">
                                            <button wire:click="addItem" type="button" class="w-full bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm font-semibold transition">Thêm</button>
                                        </div>
                                    </div>
                                    <small class="text-blue-600">💡 Đơn giá tự động lấy từ danh mục sản phẩm (có thể chỉnh sửa)</small>
                                </div>

                                @if(!empty($items))
                                    <div class="bg-gray-50 rounded border overflow-hidden">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="bg-gray-100 border-b">
                                                    <th class="px-2 py-2 text-left">Mã NVL</th>
                                                    <th class="px-2 py-2 text-left">Tên NVL</th>
                                                    <th class="px-2 py-2 text-left">Hãng SX</th>
                                                    <th class="px-2 py-2 text-center">ĐVT</th>
                                                    <th class="px-2 py-2 text-right">SL Mua</th>
                                                    <th class="px-2 py-2 text-right">Đơn giá</th>
                                                    <th class="px-2 py-2 text-right">Thành tiền</th>
                                                    <th class="px-2 py-2 text-center">Xoá</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y">
                                                @foreach($items as $index => $item)
                                                    @php
                                                        $product = $products->firstWhere('id', $item['product_id']);
                                                    @endphp
                                                    <tr class="hover:bg-gray-100">
                                                        <td class="px-2 py-2 font-mono text-xs">{{ $product?->code ?? 'N/A' }}</td>
                                                        <td class="px-2 py-2 font-semibold">{{ $product?->name ?? 'N/A' }}</td>
                                                        <td class="px-2 py-2 text-xs text-gray-600">{{ $product?->brand ?? 'N/A' }}</td>
                                                        <td class="px-2 py-2 text-center text-xs bg-gray-50">{{ $product?->unit ?? 'N/A' }}</td>
                                                        <td class="px-2 py-2 text-right font-bold text-blue-700">{{ $item['quantity'] }}</td>
                                                        <td class="px-2 py-2 text-right">{{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                                                        <td class="px-2 py-2 text-right font-semibold text-amber-700">{{ number_format($item['line_total'], 0, ',', '.') }}</td>
                                                        <td class="px-2 py-2 text-center">
                                                            <button wire:click="removeItem({{ $index }})" type="button" class="text-red-500 hover:text-red-700">✕</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" wire:click="save" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none sm:w-auto sm:text-sm">
                            💾 Lưu phiếu
                        </button>
                        @if(!$isEdit && !empty($items))
                            <button type="button" wire:click="$toggle('status')" wire:click.prevent="$set('status', 'confirmed'); save()" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none sm:w-auto sm:text-sm">
                                ✓ Lưu & Xác nhận
                            </button>
                        @endif
                        <button type="button" wire:click="$set('showModal', false)" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Office Purchase -->
    @if($showOfficeModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showOfficeModal', false)"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-middle bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full max-h-[90vh] overflow-y-auto">
                    <div class="bg-blue-50 border-b border-blue-100 px-4 py-3 sm:px-6">
                        <h3 class="text-lg leading-6 font-semibold text-blue-900 flex items-center gap-2">
                            <span>🏢</span> Tạo đề xuất Mua hàng Văn phòng
                        </h3>
                    </div>
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="space-y-4">
                            <!-- Basic Info -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Số phiếu (PO)</label>
                                    <input type="text" wire:model="po_number" readOnly class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 bg-gray-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nhà cung cấp/Nới báo giá <span class="text-red-500">*</span></label>
                                    <select wire:model="supplier_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                                        <option value="">-- Chọn --</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <!-- Items Section -->
                            <div class="border-t pt-4">
                                <h4 class="font-semibold mb-3 text-gray-800">Danh sách vật tư văn phòng</h4>
                                <div class="flex gap-2 mb-4 items-end bg-blue-50 p-3 rounded-lg border border-blue-100">
                                    <div class="flex-1">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Tên vật tư/VPP</label>
                                        <input type="text" wire:model="officeItemName" placeholder="Ví dụ: Giấy A4, Bút bi..." class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm">
                                    </div>
                                    <div class="w-20">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Số lượng</label>
                                        <input type="number" wire:model="officeItemQuantity" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm text-center">
                                    </div>
                                    <div class="w-28">
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Dự kiến giá</label>
                                        <input type="number" wire:model="officeItemPrice" placeholder="Giá" class="w-full border border-gray-300 rounded-md shadow-sm p-2 text-sm text-right">
                                    </div>
                                    <div>
                                        <button wire:click="addOfficeItem" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm font-semibold transition h-[38px]">Thêm</button>
                                    </div>
                                </div>
                                @error('officeItem') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

                                @if(!empty($officeItems))
                                    <div class="bg-gray-50 rounded border overflow-hidden">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="bg-gray-100 border-b">
                                                    <th class="px-3 py-2 text-left">Tên vật tư/VPP</th>
                                                    <th class="px-3 py-2 text-center w-16">SL</th>
                                                    <th class="px-3 py-2 text-right w-24">Đơn giá</th>
                                                    <th class="px-3 py-2 text-right w-28">Thành tiền</th>
                                                    <th class="px-3 py-2 text-center w-12">Xoá</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y">
                                                @foreach($officeItems as $index => $item)
                                                    <tr>
                                                        <td class="px-3 py-2 font-medium">{{ $item['name'] }}</td>
                                                        <td class="px-3 py-2 text-center font-bold text-blue-700">{{ $item['quantity'] }}</td>
                                                        <td class="px-3 py-2 text-right">{{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                                                        <td class="px-3 py-2 text-right font-semibold text-amber-700">{{ number_format($item['line_total'], 0, ',', '.') }}</td>
                                                        <td class="px-3 py-2 text-center">
                                                            <button wire:click="removeOfficeItem({{ $index }})" type="button" class="text-red-500 hover:text-red-700 font-bold">✕</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button type="button" wire:click="saveOfficePurchase" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:w-auto sm:text-sm">
                            💾 Lưu Đề Xuất
                        </button>
                        <button type="button" wire:click="$set('showOfficeModal', false)" class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:w-auto sm:text-sm">
                            Huỷ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- PHẦN IN PDF BỊ ẨN KHI XEM THƯỜNG -->
    <div class="hidden print:block fixed inset-0 bg-white z-[9999] w-full">
        @foreach($printItems as $printOrder)
        <div style="font-family: 'Times New Roman', serif; padding: 15mm; page-break-after: always; width: 100%;">
            <!-- Header -->
            <div class="mb-4 text-left">
                <h1 class="text-xl font-bold uppercase">CÔNG TY TNHH ABC</h1>
                <p class="text-[14px]">Địa chỉ: 123 Tỉnh Lộ 10 - Long An - SĐT: 0708091050</p>
            </div>
            <div style="border-bottom: 2px solid #000; margin-bottom: 20px;"></div>

            <!-- Title -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold uppercase tracking-widest text-black">PHIẾU ĐỀ XUẤT MUA HÀNG</h2>
                <p class="italic text-[13px] mt-1">
                    Ngày {{ \Carbon\Carbon::parse($printOrder->order_date)->format('d') }} 
                    tháng {{ \Carbon\Carbon::parse($printOrder->order_date)->format('m') }} 
                    năm {{ \Carbon\Carbon::parse($printOrder->order_date)->format('Y') }}
                </p>
            </div>

            <!-- Info -->
            <div style="margin-bottom: 20px;" class="text-[14px]">
                <table class="w-full">
                    <tr>
                        <td class="font-bold w-32 pb-1">Số PO:</td>
                        <td class="pb-1 uppercase font-semibold">{{ $printOrder->po_number }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold pb-1">Tên nhà CC:</td>
                        <td class="pb-1 uppercase font-bold">{{ $printOrder->supplier->name ?? '............................................' }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold pb-1">SĐT:</td>
                        <td class="pb-1">{{ $printOrder->supplier->phone ?? '............................................' }}</td>
                    </tr>
                    <tr>
                        <td class="font-bold pb-1">Địa chỉ:</td>
                        <td class="pb-1">{{ $printOrder->supplier->address ?? '............................................' }}</td>
                    </tr>
                    @if($printOrder->notes)
                    <tr>
                        <td class="font-bold pb-1">Ghi chú:</td>
                        <td class="pb-1">{{ $printOrder->notes }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <!-- Table -->
            <table class="w-full border-collapse border border-black text-[13px] mb-8">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-black px-2 py-2 w-10 text-center font-bold">STT</th>
                        <th class="border border-black px-2 py-2 text-left w-24 font-bold">Mã SP</th>
                        <th class="border border-black px-2 py-2 text-left font-bold">Tên SP (Nguyên vật liệu)</th>
                        <th class="border border-black px-2 py-2 text-center w-24 font-bold">S.Lượng</th>
                        <th class="border border-black px-2 py-2 text-center w-20 font-bold">ĐVT</th>
                        <th class="border border-black px-2 py-2 text-left w-24 font-bold">Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($printOrder->items as $idx => $item)
                    <tr>
                        <td class="border border-black px-2 py-2 text-center">{{ $idx + 1 }}</td>
                        <td class="border border-black px-2 py-2 font-mono uppercase">{{ $item->product->code ?? '' }}</td>
                        <td class="border border-black px-2 py-2 font-semibold">{{ $item->product->name ?? '' }}</td>
                        <td class="border border-black px-2 py-2 text-center font-bold">{{ floatval($item->quantity) }}</td>
                        <td class="border border-black px-2 py-2 text-center">{{ $item->product->unit ?? '' }}</td>
                        <td class="border border-black px-2 py-2 text-center"></td>
                    </tr>
                    @endforeach
                    @for($i = count($printOrder->items); $i < max(8, count($printOrder->items)); $i++)
                    <tr>
                        <td class="border border-black px-2 py-2 text-center text-transparent">_</td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                        <td class="border border-black px-2 py-2"></td>
                    </tr>
                    @endfor
                </tbody>
            </table>

            <!-- Footer / Signatures -->
            <div class="grid grid-cols-2 gap-4 text-center mt-8 mb-8">
                <div>
                    <p class="font-bold text-[14px]">Người đặt hàng</p>
                    <p class="text-[12px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-bold uppercase text-[14px]">{{ $printOrder->user->name ?? '........................' }}</p>
                </div>
                <div>
                    <p class="font-bold text-[14px]">Người xét duyệt</p>
                    <p class="text-[12px] italic">(Ký, ghi rõ họ tên)</p>
                    <div style="height: 100px;"></div>
                    <p class="font-bold uppercase text-[14px]">........................</p>
                </div>
            </div>
            
            <div class="text-right mt-12 mb-4 text-[11px] italic text-gray-500">
                In lúc: {{ date('d/m/Y H:i') }}
            </div>
        </div>
        @endforeach
    </div>
</div>
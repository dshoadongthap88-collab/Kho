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
    <div class="flex justify-between items-center mb-4 no-print">
        <div class="flex gap-4 flex-1 max-w-2xl">
            <div class="flex-1">
                <input wire:model.live="search" type="text" placeholder="Tìm theo số PO, tên nhà cung cấp..." class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
            </div>
            <select wire:model.live="filterStatus" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-amber-500">
                <option value="">Tất cả trạng thái</option>
                <option value="pending">Đã trình</option>
                <option value="confirmed">Đã duyệt</option>
                <option value="received">Đã nhận hàng</option>
                <option value="cancelled">Đã hủy</option>
            </select>
        </div>
        <div class="flex gap-3">
            <button wire:click="printSelected" class="bg-gray-100 font-semibold hover:bg-gray-200 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors shadow-sm">
                <span>🖨️</span> In phiếu
            </button>
            <button wire:click="openModal" class="bg-amber-600 font-semibold hover:bg-amber-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors shadow-sm">
                <span>➕</span> Tạo đề xuất
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
                <tr class="bg-gray-50 border-b text-gray-600 uppercase text-xs font-semibold">
                    <th class="px-4 py-3 w-10 text-center no-print">✔️</th>
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
                    <tr class="hover:bg-gray-50 transition print-hide-unselected {{ in_array($order->id, $selectedOrders) ? 'bg-amber-50 is-selected' : '' }}">
                        <td class="px-4 py-3 text-center no-print bg-gray-50/50">
                            <input type="checkbox" wire:model.live="selectedOrders" value="{{ $order->id }}" class="w-4 h-4 rounded border-gray-300 text-amber-600 shadow-sm focus:ring-amber-500 cursor-pointer">
                        </td>
                        <td class="px-4 py-3 font-mono font-medium">{{ $order->po_number }}</td>
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
                        <td class="px-4 py-3 text-right flex gap-2 justify-end no-print">
                            @if($order->status === 'pending')
                                <button wire:click="confirmOrder({{ $order->id }})" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs" title="Xác nhận">✓ Duyệt phiếu</button>
                            @endif
                            <button wire:click="openModal({{ $order->id }})" class="text-blue-500 hover:text-blue-700" title="Sửa">📝</button>
                            <button onclick="confirm('Xoá đơn hàng này?') || event.stopImmediatePropagation()" wire:click="delete({{ $order->id }})" class="text-red-500 hover:text-red-700" title="Xoá">🗑️</button>
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

    <!-- PHẦN IN PDF BỊ ẨN KHI XEM THƯỜNG -->
    <div class="hidden print-only w-full bg-white text-black">
        @foreach($orders->whereIn('id', $selectedOrders) as $printOrder)
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
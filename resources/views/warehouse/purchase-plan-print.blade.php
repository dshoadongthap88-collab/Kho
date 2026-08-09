<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Phiếu</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; line-height: 1.5; padding: 20px; font-size: 14px; }
        .header { text-align: center; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { font-weight: bold; text-align: center; background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature { text-align: center; width: 30%; }
        @page { size: auto; margin: 0; } /* Hide browser headers/footers */
        @media print {
            .no-print { display: none; }
            body { padding: 15mm; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #000; color: #fff; cursor: pointer;">In Phiếu</button>
        <button onclick="window.location.href='{{ route('purchase-plan') }}'" style="padding: 8px 16px; background: #ccc; cursor: pointer;">Đóng</button>
    </div>

    <div style="margin-bottom: 20px;">
        <div style="font-weight: bold; font-size: 18px; text-transform: uppercase;">PHÒNG KỸ THUẬT SỮA CHỮA</div>
        <div style="font-weight: bold; font-size: 14px;">
            DỰ ÁN: {{ session('current_house', 1) == 2 ? 'HẬU NGHĨA' : (session('current_house', 1) == 3 ? 'CẦN GIỜ' : (session('current_house', 1) == 4 ? 'CẦN GIUỘC' : 'HÓC MÔN')) }}
        </div>
    </div>

    <div class="header" style="text-align: center; margin-bottom: 30px;">
        <div class="title" style="font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 10px 0;">PHIẾU ĐỀ XUẤT MUA HÀNG</div>
        @php
            $houseId = session('current_house', 1);
            $housePrefix = $houseId == 2 ? 'HN' : ($houseId == 3 ? 'CG' : 'HM');
            $dateStr = now()->format('dmY');
            
            $cacheKey = 'print_po_seq_' . $houseId . '_' . $dateStr;
            $sessionKey = 'po_num_' . md5(request()->fullUrl());
            
            if (!session()->has($sessionKey)) {
                $count = cache()->increment($cacheKey);
                cache()->put($cacheKey, $count, now()->endOfDay());
                session()->put($sessionKey, $count);
            } else {
                $count = session()->get($sessionKey);
            }
            
            $poNumber = 'PO_' . $housePrefix . '_' . $dateStr . '_' . str_pad($count, 2, '0', STR_PAD_LEFT);
        @endphp
        <div style="font-size: 16px;">Số PO: <strong>{{ $poNumber }}</strong></div>
        <div style="font-style: italic; margin-top: 5px;">Ngày in: {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%">STT</th>
                <th width="10%">Ngày ĐX</th>
                <th width="20%">Mã & Tên Vật Tư</th>
                <th width="8%">SL Đề Xuất</th>
                <th width="15%">NCC Gần nhất</th>
                <th width="10%">Trạng Thái</th>
                <th width="10%">Tình Trạng</th>
                <th width="10%">Ngày Nhận</th>
                <th width="13%">Ghi Chú</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $index => $plan)
                @php
                    $latestPoItem = \App\Models\PurchaseOrderItem::where('product_id', $plan->product_id)
                        ->whereHas('purchaseOrder', function($q) {
                            $q->whereNotNull('supplier_id');
                        })
                        ->latest('id')
                        ->with('purchaseOrder.supplier')
                        ->first();
                        
                    if ($latestPoItem && $latestPoItem->purchaseOrder && $latestPoItem->purchaseOrder->supplier) {
                        $supplierName = $latestPoItem->purchaseOrder->supplier->name;
                    } else {
                        $latestStockInItem = \App\Models\StockInItem::where('product_id', $plan->product_id)
                            ->whereHas('stockIn', function($q) {
                                $q->whereNotNull('supplier_name')->where('supplier_name', '!=', '');
                            })
                            ->latest('id')
                            ->with('stockIn')
                            ->first();
                        $supplierName = $latestStockInItem && $latestStockInItem->stockIn 
                                        ? $latestStockInItem->stockIn->supplier_name 
                                        : '';
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $plan->created_at->format('d/m/Y') }}</td>
                    <td>
                        <strong>{{ $plan->product?->code }}</strong><br>
                        {{ $plan->product?->name }}
                    </td>
                    <td class="text-right">{{ number_format($plan->proposed_quantity, 0) }} {{ $plan->product?->unit }}</td>
                    <td>{{ $supplierName }}</td>
                    <td class="text-center">
                        @if($plan->status === 'pending') Đề xuất
                        @elseif($plan->status === 'ordered') Đã đặt
                        @elseif($plan->status === 'unreceived') Chưa giao
                        @elseif($plan->status === 'partial') Giao thiếu
                        @else Đủ hàng @endif
                    </td>
                    <td class="text-center" style="{{ $plan->urgency === 'urgent' ? 'font-weight:bold;color:red;' : '' }}">
                        {{ $plan->urgency === 'urgent' ? 'Cần gấp' : 'Bình thường' }}
                    </td>
                    <td class="text-center">{{ $plan->expected_delivery_date ? $plan->expected_delivery_date->format('d/m/Y') : '' }}</td>
                    <td>{{ $plan->notes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <strong>Người đề xuất</strong><br>
            <span style="font-size: 12px; font-style: italic;">(Ký, ghi rõ họ tên)</span>
            <br><br><br><br>
        </div>
        <div class="signature">
            <strong>Trưởng bộ phận</strong><br>
            <span style="font-size: 12px; font-style: italic;">(Ký, ghi rõ họ tên)</span>
            <br><br><br><br>
        </div>
        <div class="signature">
            <strong>Quản lý kho</strong><br>
            <span style="font-size: 12px; font-style: italic;">(Ký, ghi rõ họ tên)</span>
            <br><br><br><br>
        </div>
    </div>
</body>
</html>

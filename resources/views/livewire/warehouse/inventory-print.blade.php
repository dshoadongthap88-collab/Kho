<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo tồn kho</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            color: #111;
            margin: 0;
            padding: 12px;
            background: #fff;
        }
        .toolbar { margin-bottom: 12px; text-align: right; }
        .toolbar button {
            font-family: inherit; font-size: 13px; font-weight: bold; cursor: pointer;
            padding: 8px 18px; border: 1px solid #1e293b; border-radius: 6px;
            background: #1e293b; color: #fff;
        }
        .toolbar a { margin-left: 10px; font-size: 13px; color: #1e293b; }
        h1 { font-size: 20px; text-transform: uppercase; text-align: center; margin: 0 0 4px; }
        .meta { text-align: center; font-size: 12px; color: #444; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 4px 6px; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        th { background: #f1f5f9; font-size: 11px; text-transform: uppercase; }
        .c { text-align: center; }
        .r { text-align: right; }
        .code { font-family: "Courier New", monospace; }
        tfoot td { font-weight: bold; background: #f8fafc; }
        @media print { .toolbar { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">🖨️ In ({{ $inventories->count() }} dòng)</button>
        <a href="{{ route('warehouse.inventory') }}">← Quay lại</a>
    </div>

    <h1>Báo cáo tồn kho chi tiết</h1>
    <div class="meta">
        @if($project) Chi nhánh: <strong>{{ $project->name }}</strong> — @endif
        Ngày in: {{ now()->format('d/m/Y H:i') }} — Tổng số dòng: <strong>{{ $inventories->count() }}</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:34px">STT</th>
                <th>Mã vật tư</th>
                <th>Tên vật tư</th>
                <th>Hãng SX</th>
                <th>Mã code NCC</th>
                <th>Hạn dùng</th>
                <th>ĐVT</th>
                <th>Tồn kho</th>
                <th>Vị trí</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventories as $i => $inv)
                @php
                    $available = $inv->quantity - $inv->reserved_quantity;
                    if ($available < $inv->min_stock) {
                        $statusText = 'Thiếu hàng';
                    } elseif ($available < $inv->min_stock * 1.5) {
                        $statusText = 'Cảnh báo';
                    } else {
                        $statusText = 'Đủ hàng';
                    }
                @endphp
                <tr>
                    <td class="c">{{ $i + 1 }}</td>
                    <td class="code">{{ $inv->product_code }}</td>
                    <td>{{ $inv->product_name }}</td>
                    <td class="c">{{ $inv->brand ?: '-' }}</td>
                    <td class="c code">{{ $inv->batch_number ?: '-' }}</td>
                    <td class="c">{{ $inv->expiry_date ? \Carbon\Carbon::parse($inv->expiry_date)->format('d/m/y') : '-' }}</td>
                    <td class="c">{{ $inv->unit }}</td>
                    <td class="r">{{ number_format($inv->quantity) }}</td>
                    <td class="c">{{ $inv->warehouse_location ?: '-' }}</td>
                    <td class="c">{{ $statusText }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="c">Không có dữ liệu phù hợp với bộ lọc</td></tr>
            @endforelse
        </tbody>
        @if($inventories->count())
            <tfoot>
                <tr>
                    <td colspan="7" class="r">Tổng cộng</td>
                    <td class="r">{{ number_format($inventories->sum('quantity')) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách tồn kho - {{ $project->name ?? 'Kho' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            color: #1a1a1a;
            background: white;
            padding: 15mm 15mm 10mm;
        }

        /* Header */
        .header { text-align: center; margin-bottom: 12px; border-bottom: 2px solid #1e293b; padding-bottom: 10px; }
        .company-name { font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-title { font-size: 18pt; font-weight: bold; text-transform: uppercase; margin: 8px 0 4px; }
        .doc-meta { font-size: 9pt; color: #64748b; }
        .project-badge {
            display: inline-block;
            background: #1e293b;
            color: white;
            padding: 2px 12px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 6px;
        }

        /* Summary stats */
        .stats-row {
            display: flex;
            gap: 12px;
            margin: 12px 0;
        }
        .stat-box {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
        }
        .stat-label { font-size: 8pt; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-value { font-size: 14pt; font-weight: bold; color: #1e293b; margin-top: 2px; }
        .stat-box.critical .stat-value { color: #dc2626; }
        .stat-box.warning .stat-value { color: #d97706; }
        .stat-box.ok .stat-value { color: #16a34a; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 9pt; }
        thead tr { background: #1e293b; color: white; }
        thead th {
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        thead th.text-right { text-align: right; }
        thead th.text-center { text-align: center; }

        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr.critical { background: #fff1f2 !important; }
        tbody tr.warning { background: #fffbeb !important; }

        tbody td { padding: 5px 8px; vertical-align: middle; }
        tbody td.text-right { text-align: right; }
        tbody td.text-center { text-align: center; }

        .code { font-family: 'Courier New', monospace; color: #4338ca; font-weight: bold; font-size: 8.5pt; }
        .name { font-weight: bold; color: #1e293b; }
        .qty { font-weight: bold; font-size: 11pt; color: #1e293b; }
        .qty-critical { color: #dc2626; }
        .qty-warning { color: #d97706; }
        .qty-ok { color: #16a34a; }

        .status-badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 8pt;
            font-weight: bold;
            border: 1px solid;
        }
        .badge-critical { background: #fff1f2; color: #dc2626; border-color: #fca5a5; }
        .badge-warning  { background: #fffbeb; color: #d97706; border-color: #fcd34d; }
        .badge-ok       { background: #f0fdf4; color: #16a34a; border-color: #86efac; }

        /* Footer */
        .footer {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
        }
        .sign-row { display: flex; justify-content: space-around; text-align: center; }
        .sign-col { width: 30%; }
        .sign-title { font-weight: bold; font-size: 10pt; }
        .sign-sub { font-size: 8pt; color: #64748b; font-style: italic; }
        .sign-space { height: 55px; }

        /* Print control */
        @media screen {
            body { background: #f1f5f9; padding: 20px; }
            .page-wrapper {
                background: white;
                max-width: 210mm;
                margin: 0 auto;
                padding: 15mm 15mm 10mm;
                box-shadow: 0 4px 24px rgba(0,0,0,0.12);
                border-radius: 4px;
            }
            .print-btn-bar {
                max-width: 210mm;
                margin: 0 auto 16px;
                display: flex;
                gap: 8px;
                align-items: center;
            }
            .btn-print {
                background: #1e293b;
                color: white;
                border: none;
                padding: 8px 20px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: bold;
                cursor: pointer;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .btn-back {
                background: white;
                color: #64748b;
                border: 1.5px solid #e2e8f0;
                padding: 8px 16px;
                border-radius: 8px;
                font-size: 13px;
                font-weight: 600;
                cursor: pointer;
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 6px;
            }
            .meta-info {
                margin-left: auto;
                font-size: 12px;
                color: #94a3b8;
            }
        }
        @media print {
            body { padding: 10mm 12mm; }
            .print-btn-bar { display: none !important; }
            .page-wrapper { box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>

{{-- Thanh nút (chỉ hiện trên màn hình) --}}
<div class="print-btn-bar">
    <button class="btn-print" onclick="window.print()">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        In / Lưu PDF
    </button>
    <a href="javascript:history.back()" class="btn-back">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Quay lại
    </a>
    <span class="meta-info">{{ $inventories->count() }} mặt hàng · In lúc {{ now()->format('H:i d/m/Y') }}</span>
</div>

<div class="page-wrapper">

    {{-- Header --}}
    <div class="header">
        <div class="company-name">Công ty CPĐT và Thi Công Hạ Tầng VINALPHA</div>
        <div class="doc-title">Báo Cáo Tồn Kho</div>
        <div class="doc-meta">Ngày in: {{ now()->format('d/m/Y H:i') }}</div>
        @if($project)
            <div><span class="project-badge">{{ $project->name }}</span></div>
        @endif
    </div>

    {{-- Stats --}}
    @php
        $totalItems   = $inventories->count();
        $criticalItems = $inventories->filter(fn($i) => ($i->quantity - $i->reserved_quantity) < $i->min_stock)->count();
        $warningItems  = $inventories->filter(fn($i) => ($qty = $i->quantity - $i->reserved_quantity) >= $i->min_stock && $qty < $i->min_stock * 1.5)->count();
        $okItems       = $totalItems - $criticalItems - $warningItems;
        $totalQty      = $inventories->sum('quantity');
    @endphp
    <div class="stats-row">
        <div class="stat-box">
            <div class="stat-label">Tổng mặt hàng</div>
            <div class="stat-value">{{ number_format($totalItems) }}</div>
        </div>
        <div class="stat-box">
            <div class="stat-label">Tổng số lượng</div>
            <div class="stat-value">{{ number_format($totalQty) }}</div>
        </div>
        <div class="stat-box ok">
            <div class="stat-label">Đủ hàng</div>
            <div class="stat-value">{{ $okItems }}</div>
        </div>
        <div class="stat-box warning">
            <div class="stat-label">Cảnh báo</div>
            <div class="stat-value">{{ $warningItems }}</div>
        </div>
        <div class="stat-box critical">
            <div class="stat-label">Thiếu hàng</div>
            <div class="stat-value">{{ $criticalItems }}</div>
        </div>
    </div>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th style="width:100px;">Mã VT</th>
                <th>Tên Vật Tư</th>
                <th class="text-center" style="width:55px;">ĐVT</th>
                <th class="text-right" style="width:72px;">Tồn kho</th>
                <th class="text-center" style="width:75px;">Vị trí</th>
                <th class="text-center" style="width:75px;">Hạn dùng</th>
                <th class="text-center" style="width:75px;">Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventories as $i => $inv)
                @php
                    $available = $inv->quantity - $inv->reserved_quantity;
                    if ($available < $inv->min_stock) {
                        $rowClass   = 'critical';
                        $badgeClass = 'badge-critical';
                        $badgeText  = 'Thiếu';
                        $qtyClass   = 'qty qty-critical';
                    } elseif ($available < $inv->min_stock * 1.5) {
                        $rowClass   = 'warning';
                        $badgeClass = 'badge-warning';
                        $badgeText  = 'Cảnh báo';
                        $qtyClass   = 'qty qty-warning';
                    } else {
                        $rowClass   = '';
                        $badgeClass = 'badge-ok';
                        $badgeText  = 'Đủ hàng';
                        $qtyClass   = 'qty qty-ok';
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td class="text-center" style="color:#94a3b8; font-size:8pt;">{{ $i + 1 }}</td>
                    <td><span class="code">{{ $inv->product_code }}</span></td>
                    <td>
                        <span class="name">{{ $inv->product_name }}</span>
                        @if($inv->brand)
                            <span style="font-size:8pt; color:#64748b;"> · {{ $inv->brand }}</span>
                        @endif
                    </td>
                    <td class="text-center" style="color:#475569;">{{ $inv->unit }}</td>
                    <td class="text-right"><span class="{{ $qtyClass }}">{{ number_format($inv->quantity) }}</span></td>
                    <td class="text-center" style="font-size:8.5pt; color:#475569;">{{ $inv->warehouse_location ?: '—' }}</td>
                    <td class="text-center" style="font-size:8.5pt; color:#64748b; font-style:italic;">
                        {{ $inv->expiry_date ? \Carbon\Carbon::parse($inv->expiry_date)->format('d/m/y') : '—' }}
                    </td>
                    <td class="text-center">
                        <span class="status-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center; padding:20px; color:#94a3b8;">Không có dữ liệu</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- Footer ký tên --}}
    <div class="footer">
        <div class="sign-row">
            <div class="sign-col">
                <div class="sign-title">Thủ kho</div>
                <div class="sign-sub">(Ký, ghi rõ họ tên)</div>
                <div class="sign-space"></div>
            </div>
            <div class="sign-col">
                <div class="sign-title">Kế toán</div>
                <div class="sign-sub">(Ký, ghi rõ họ tên)</div>
                <div class="sign-space"></div>
            </div>
            <div class="sign-col">
                <div class="sign-title">Giám đốc / Quản lý</div>
                <div class="sign-sub">(Ký, đóng dấu)</div>
                <div class="sign-space"></div>
            </div>
        </div>
    </div>

</div>

<script>
    // Tự động mở hộp thoại in sau khi load nếu có param ?autoprint=1
    const url = new URL(window.location.href);
    if (url.searchParams.get('autoprint') === '1') {
        window.addEventListener('load', () => setTimeout(() => window.print(), 400));
    }
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Phiếu Chuyển Kho - {{ $transfer->transfer_code }}</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; line-height: 1.6; color: #000; margin: 0; padding: 0; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm; margin: 10mm auto; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); box-sizing: border-box; position: relative; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .header p { font-size: 14px; margin: 5px 0; }
        .info-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-item { font-size: 15px; margin-bottom: 8px; }
        .info-label { font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th, .table td { border: 1px solid #000; padding: 8px; text-align: center; font-size: 14px; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .table td.text-left { text-align: left; }
        .footer { margin-top: 50px; display: grid; grid-template-columns: 1fr 1fr 1fr; text-align: center; }
        .footer div { font-size: 15px; font-weight: bold; }
        .signature-space { height: 80px; }
        @media print {
            body { background: none; }
            .page { margin: 0; box-shadow: none; width: 100%; }
            .no-print { display: none; }
        }
        .no-print-btn { position: fixed; top: 20px; right: 20px; padding: 10px 20px; background: #4f46e5; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <button class="no-print-btn no-print" onclick="window.print()">🖨️ In Phiếu</button>

    <div class="page">
        <div class="header">
            <div style="font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px;">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ HẠ TẦNG V-ALPHA</div>
            <h1>PHIẾU ĐIỀU CHUYỂN KHO</h1>
            <p>Mã phiếu: <strong>{{ $transfer->transfer_code }}</strong></p>
        </div>

        <div class="info-section">
            <div class="left-col">
                <div class="info-item"><span class="info-label">Ngày chuyển:</span> {{ $transfer->transfer_date?->format('d/m/Y H:i') }}</div>
                <div class="info-item"><span class="info-label">Người lập:</span> {{ $transfer->creator?->name ?? '—' }}</div>
            </div>
            <div class="right-col">
                <div class="info-item"><span class="info-label">Từ Kho:</span> {{ $transfer->from_house == 1 ? 'Hóc Môn' : ($transfer->from_house == 2 ? 'Hậu Nghĩa' : ($transfer->from_house == 3 ? 'Cần Giờ' : 'Số ' . $transfer->from_house)) }}</div>
                <div class="info-item"><span class="info-label">Đến Kho:</span> {{ $transfer->to_house == 1 ? 'Hóc Môn' : ($transfer->to_house == 2 ? 'Hậu Nghĩa' : ($transfer->to_house == 3 ? 'Cần Giờ' : 'Số ' . $transfer->to_house)) }}</div>
            </div>
        </div>

        @if($transfer->note)
            <div style="margin-bottom: 20px; font-size: 14px;">
                <span class="info-label">Ghi chú:</span> {{ $transfer->note }}
            </div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 50px;">STT</th>
                    <th style="width: 150px;">Mã Vật Tư</th>
                    <th>Tên Vật Tư</th>
                    <th style="width: 100px;">Số Lượng</th>
                    <th style="width: 80px;">ĐVT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transfer->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td class="font-mono">{{ $item->product?->code ?? '—' }}</td>
                        <td class="text-left">{{ $item->product?->name ?? '—' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->product?->unit ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <div>
                <p>Người Lập</p>
                <div class="signature-space"></div>
                <p>(Ký và ghi rõ họ tên)</p>
            </div>
            <div>
                <p>Thủ Kho Nguồn</p>
                <div class="signature-space"></div>
                <p>(Ký và ghi rõ họ tên)</p>
            </div>
            <div>
                <p>Thủ Kho Đích</p>
                <div class="signature-space"></div>
                <p>(Ký và ghi rõ họ tên)</p>
            </div>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo vật tư chưa sử dụng</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            margin: 0;
            padding: 20px;
            color: #000;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        .header-left {
            text-align: left;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .header-left .title-strong {
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 30px 0 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th {
            padding: 8px;
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
        }
        td {
            padding: 8px;
            text-align: center;
        }
        .text-left {
            text-align: left !important;
        }
        .footer-sig {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }
        .sig-block {
            text-align: center;
            width: 30%;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <button onclick="window.print()" class="no-print print-btn">🖨️ In Báo Cáo</button>

    <div class="header-left">
        <div class="title-strong">KHO KỸ THUẬT SỬA CHỮA</div>
        <div class="title-strong">DỰ ÁN : {{ config('app.project_name', 'TÊN DỰ ÁN HIỆN TẠI') }}</div>
    </div>

    <div class="report-title">
        BÁO CÁO VẬT TƯ CHƯA SỬ DỤNG
    </div>

    <div style="text-align: center; margin-bottom: 20px; font-style: italic;">
        (Ngày xuất báo cáo: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} - Người lập: {{ auth()->user()->name ?? 'N/A' }})
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th width="15%">Mã vật tư</th>
                <th width="35%">Tên vật tư</th>
                <th width="10%">ĐVT</th>
                <th width="10%">Số lượng</th>
                <th width="15%">Nhà cung cấp</th>
                <th width="10%">Ngày đặt (Nhập kho)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item['code'] }}</td>
                    <td class="text-left">{{ $item['name'] }}</td>
                    <td>{{ $item['unit'] }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ $item['supplier'] }}</td>
                    <td>{{ $item['stock_in_date'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-sig">
        <div class="sig-block">
            <strong>Người lập biểu</strong><br>
            <span style="font-style: italic; font-size: 12px;">(Ký, ghi rõ họ tên)</span>
            <br><br><br><br><br>
            {{ auth()->user()->name ?? '' }}
        </div>
        <div class="sig-block">
            <strong>Kế toán kho</strong><br>
            <span style="font-style: italic; font-size: 12px;">(Ký, ghi rõ họ tên)</span>
        </div>
        <div class="sig-block">
            <strong>Thủ trưởng đơn vị</strong><br>
            <span style="font-style: italic; font-size: 12px;">(Ký, ghi rõ họ tên)</span>
        </div>
    </div>
    
    <script>
        // Tự động mở hộp thoại in sau khi load
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>

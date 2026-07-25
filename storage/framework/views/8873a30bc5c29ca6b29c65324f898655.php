<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Báo Cáo Ngày</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; line-height: 1.5; padding: 20px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 10px; text-align: left; }
        th { font-weight: bold; background-color: #f0f0f0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature { text-align: center; width: 30%; }
        @page { size: auto; margin: 0; }
        @media print {
            .no-print { display: none; }
            body { padding: 15mm; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #000; color: #fff; cursor: pointer;">In Phiếu</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #ccc; cursor: pointer;">Đóng</button>
    </div>

    <div style="margin-bottom: 20px;">
        <div style="font-weight: bold; font-size: 18px; text-transform: uppercase;">PHÒNG KỸ THUẬT SỮA CHỮA</div>
        <div style="font-weight: bold; font-size: 14px;">
            DỰ ÁN: <?php echo e(session('current_house', 1) == 2 ? 'HẬU NGHĨA' : (session('current_house', 1) == 3 ? 'CẦN GIỜ' : 'HÓC MÔN')); ?>

        </div>
    </div>

    <div class="header" style="text-align: center; margin-bottom: 30px;">
        <div class="title" style="font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 10px 0;">BÁO CÁO NGÀY</div>
        <div style="font-size: 16px;">Ngày thống kê: <strong><?php echo e(\Carbon\Carbon::parse($date)->format('d/m/Y')); ?></strong></div>
        <div style="font-style: italic; margin-top: 5px;">Ngày in: <?php echo e(now()->format('d/m/Y H:i')); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th colspan="2" class="text-center">TỔNG HỢP GIAO DỊCH VẬT TƯ</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="70%">Tổng số lượng mã vật tư đã NHẬP KHO:</td>
                <td width="30%" class="text-center font-bold"><?php echo e($reportData['stockInCount']); ?></td>
            </tr>
            <tr>
                <td>Tổng số lượng mã vật tư đã XUẤT KHO:</td>
                <td class="text-center font-bold"><?php echo e($reportData['stockOutCount']); ?></td>
            </tr>
            <tr>
                <td>Tổng số lượng mã vật tư đã CHUYỂN KHO:</td>
                <td class="text-center font-bold"><?php echo e($reportData['stockTransferCount']); ?></td>
            </tr>
            <tr>
                <td>Tổng số lượng mã vật tư đã THU HỒI:</td>
                <td class="text-center font-bold"><?php echo e($reportData['stockRecoveryCount']); ?></td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="2" class="text-center">THỐNG KÊ ĐƠN XUẤT KHO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="70%">Tổng số đơn xuất trong ngày:</td>
                <td width="30%" class="text-center font-bold"><?php echo e($reportData['totalStockOutOrders']); ?></td>
            </tr>
            <tr>
                <td>Số mã Tài sản xuất cho dự án:</td>
                <td class="text-center font-bold"><?php echo e($reportData['assetExportCount']); ?></td>
            </tr>
            <tr>
                <td>Số mã Vật tư xuất cho dự án:</td>
                <td class="text-center font-bold"><?php echo e($reportData['materialExportCount']); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <strong>Người lập báo cáo</strong><br>
            <span style="font-size: 12px; font-style: italic;">(Ký, ghi rõ họ tên)</span>
            <br><br><br><br>
            <strong><?php echo e(Auth::user()->name ?? 'Chưa xác định'); ?></strong>
        </div>
        <div class="signature">
            <strong>Trưởng bộ phận</strong><br>
            <span style="font-size: 12px; font-style: italic;">(Ký, ghi rõ họ tên)</span>
            <br><br><br><br>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\Project\resources\views/warehouse/reports/daily-report-print.blade.php ENDPATH**/ ?>
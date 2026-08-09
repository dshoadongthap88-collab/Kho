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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body onload="initPrint()" id="print-content">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #000; color: #fff; cursor: pointer;">In Phiếu</button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #ccc; cursor: pointer;">Đóng</button>
    </div>

    <div style="margin-bottom: 20px;">
        <div style="font-weight: bold; font-size: 18px; text-transform: uppercase;">PHÒNG KỸ THUẬT SỮA CHỮA</div>
        <div style="font-weight: bold; font-size: 14px;">
            DỰ ÁN: <?php echo e(session('current_house', 1) == 2 ? 'HẬU NGHĨA' : (session('current_house', 1) == 3 ? 'CẦN GIUỘC' : (session('current_house', 1) == 4 ? 'CẦN GIỜ' : 'HÓC MÔN'))); ?>

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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($detailed) && $detailed && isset($transactions) && $transactions->count() > 0): ?>
        <div style="page-break-before: always;"></div>
        <div class="header" style="text-align: center; margin-bottom: 20px; margin-top: 20px;">
            <div class="title" style="font-size: 20px; font-weight: bold; text-transform: uppercase;">CHI TIẾT CÁC GIAO DỊCH TRONG NGÀY</div>
        </div>
        
        <?php
            // Group transactions by document_number
            $groupedTransactions = [];
            foreach($transactions as $tx) {
                $docNum = 'KHÁC';
                $repairStaff = '';
                if ($tx->reference && get_class($tx->reference) === 'App\Models\StockOut') {
                    $docNum = $tx->reference->document_number ?: 'KHÁC';
                    $repairStaff = $tx->reference->repair_staff ?: '';
                }
                
                if (!isset($groupedTransactions[$docNum])) {
                    $groupedTransactions[$docNum] = [
                        'repairStaff' => $repairStaff,
                        'items' => collect()
                    ];
                }
                
                $groupedTransactions[$docNum]['items']->push($tx);
            }
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $groupedTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $docNum => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $groupAssetCount = $group['items']->filter(function($tx) {
                    return $tx->reference && isset($tx->reference->asset_code) && !empty($tx->reference->asset_code);
                })->pluck('reference.asset_code')->unique()->count();

                $groupProductCount = $group['items']->filter(function($tx) {
                    return $tx->product_id;
                })->pluck('product_id')->unique()->count();
            ?>

            <div style="margin-top: 15px; border: 1px solid #ddd; padding: 10px; page-break-inside: avoid;">
                <div style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px;">
                    <div style="width: 50%;">Số Phiếu ĐNSC/BD: <span style="font-weight: normal; font-size: 14px;"><?php echo e($docNum !== 'KHÁC' ? $docNum : '..........................................'); ?></span></div>
                    <div style="width: 50%;">Nhân viên sửa chữa: <span style="font-weight: normal; font-size: 14px;"><?php echo e($group['repairStaff'] ?: '..........................................'); ?></span></div>
                </div>
                <div style="font-size: 11px; font-style: italic; margin-bottom: 10px; color: #555;">
                    Tổng mã tài sản: <?php echo e($groupAssetCount); ?> | Tổng mã vật tư: <?php echo e($groupProductCount); ?> | Số lượng giao dịch: <?php echo e($group['items']->count()); ?>

                </div>

                <table style="width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px;">
                    <thead>
                        <tr>
                            <th style="width: 5%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">STT</th>
                            <th style="width: 15%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">MÃ TÀI SẢN</th>
                            <th style="width: 15%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">MÃ VẬT TƯ</th>
                            <th style="width: 15%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">SỐ LƯỢNG</th>
                            <th style="width: 25%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">BP SỬ DỤNG</th>
                            <th style="width: 25%; border: 1px solid black; padding: 5px; background-color: #f3f4f6;">GHI CHÚ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="text-center" style="border: 1px solid black; padding: 5px;"><?php echo e($index + 1); ?></td>
                                <td class="text-center font-bold" style="border: 1px solid black; padding: 5px;">
                                    <?php echo e(($tx->reference && isset($tx->reference->asset_code)) ? $tx->reference->asset_code : '-'); ?>

                                </td>
                                <td class="text-center" style="border: 1px solid black; padding: 5px;">
                                    <?php echo e($tx->product->code ?? '-'); ?>

                                </td>
                                <td class="text-center font-bold" style="border: 1px solid black; padding: 5px;">
                                    <?php echo e((float)$tx->quantity); ?> <?php echo e($tx->product->unit ?? ''); ?>

                                </td>
                                <td style="border: 1px solid black; padding: 5px;">
                                    <?php echo e(($tx->reference && isset($tx->reference->department)) ? $tx->reference->department : '-'); ?>

                                </td>
                                <td style="border: 1px solid black; padding: 5px;">
                                    <?php echo e($tx->item_note ?? $tx->note ?? ''); ?>

                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 20px; border: 1px solid black;">Không có dữ liệu hiển thị</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
    
    <script>
        function initPrint() {
            const urlParams = new URLSearchParams(window.location.search);
            const isZalo = urlParams.get('zalo') === '1';
            
            if (isZalo) {
                // Hide buttons before PDF generation
                document.querySelector('.no-print').style.display = 'none';
                
                setTimeout(() => {
                    var element = document.getElementById('print-content');
                    var opt = {
                      margin:       [15, 15, 15, 15],
                      filename:     'Bao_Cao_Ngay_<?php echo e($date); ?>.pdf',
                      image:        { type: 'jpeg', quality: 0.98 },
                      html2canvas:  { scale: 2, useCORS: true },
                      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                    };
                    
                    html2pdf().set(opt).from(element).save().then(() => {
                        alert('Đã tải xuống file PDF. Nhấn OK để tự động mở Zalo, sau đó bạn chỉ cần kéo thả file PDF vừa tải vào Zalo để gửi đi.');
                        window.location.href = 'https://chat.zalo.me/';
                    });
                }, 500);
            } else {
                window.print();
            }
        }
    </script>
</body>
</html>
<?php /**PATH D:\Project\resources\views/warehouse/reports/daily-report-print.blade.php ENDPATH**/ ?>
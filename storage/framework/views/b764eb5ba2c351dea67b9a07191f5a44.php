<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>In báo cáo giao dịch ngày</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 13px; line-height: 1.5; color: #000; }
        @page { size: A4 portrait; margin: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mt-5 { margin-top: 1.25rem; }
        .mt-10 { margin-top: 2.5rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-5 { margin-bottom: 1.25rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 5px; }
        th { background-color: #f3f4f6; }
        
        .header-title { font-size: 16px; font-weight: bold; }
        .header-subtitle { font-size: 14px; font-weight: bold; margin-top: 5px; }
        
        .summary-box { margin-bottom: 15px; font-weight: bold; }
        
        .grid-footer { display: flex; justify-content: space-between; margin-top: 30px; text-align: center; }
        .grid-footer > div { width: 24%; }
        
        @media print {
            body { margin: 0; padding: 15mm; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            .grid-footer { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <script>
        window.onload = function() {
            window.print();
        };
        window.onafterprint = function() {
            window.history.back();
        };
    </script>

    <div style="display: flex; margin-bottom: 20px;">
        <!-- Bên trái: Tên dự án -->
        <div style="font-weight: bold; font-size: 14px; text-transform: uppercase; width: 25%;">
            DỰ ÁN: <?php echo e(session('current_house') == 2 ? 'HẬU NGHĨA' : (session('current_house') == 3 ? 'CẦN GIỜ' : (session('current_house') == 4 ? 'CẦN GIUỘC' : 'HÓC MÔN'))); ?>

        </div>
        <!-- Ở giữa: Tiêu đề báo cáo -->
        <div class="text-center" style="flex: 1;">
            <div class="header-title uppercase">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ THI CÔNG HẠ TẦNG V- ALPHA</div>
            <div class="header-subtitle uppercase">BÁO CÁO CHI TIẾT GIAO DỊCH NGÀY</div>
            <div style="font-size: 12px; font-style: italic; margin-top: 5px;">Ngày in: <?php echo e(now()->format('d/m/Y H:i')); ?></div>
        </div>
        <!-- Bên phải: Trống để cân đối -->
        <div style="width: 25%;"></div>
    </div>

    <div class="summary-box">
        <div>- Tổng mã tài sản đã sử dụng: <?php echo e($assetCodesCount); ?> mã</div>
        <div>- Tổng mã vật tư đã giao dịch: <?php echo e($productCodesCount); ?> mã</div>
        <div style="font-style: italic; font-weight: normal; font-size: 12px;">(Lọc từ <?php echo e(count($transactions)); ?> giao dịch được chọn)</div>
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

            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">STT</th>
                        <th style="width: 15%">MÃ TÀI SẢN</th>
                        <th style="width: 15%">MÃ VẬT TƯ</th>
                        <th style="width: 10%">SỐ LƯỢNG</th>
                        <th style="width: 20%">BP SỬ DỤNG</th>
                        <th style="width: 20%">GHI CHÚ</th>
                        <th style="width: 15%">XUẤT APP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td class="text-center"><?php echo e($index + 1); ?></td>
                            <td class="text-center font-bold">
                                <?php echo e(($tx->reference && isset($tx->reference->asset_code)) ? $tx->reference->asset_code : '-'); ?>

                            </td>
                            <td class="text-center">
                                <?php echo e($tx->product->code ?? '-'); ?>

                            </td>
                            <td class="text-center font-bold">
                                <?php echo e((float)$tx->quantity); ?> <?php echo e($tx->product->unit ?? ''); ?>

                            </td>
                            <td>
                                <?php echo e(($tx->reference && isset($tx->reference->department)) ? $tx->reference->department : '-'); ?>

                            </td>
                            <td>
                                <?php echo e($tx->item_note ?? $tx->note ?? ''); ?>

                            </td>
                            <td class="text-center font-bold" style="font-size: 10px; <?php echo e($tx->is_exported_app ? 'color: green;' : 'color: red;'); ?>">
                                <?php echo e($tx->is_exported_app ? 'Đã xuất' : 'Chưa xuất'); ?>

                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 20px;">Không có dữ liệu hiển thị</td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

    <div class="grid-footer">
        <div>
            <p class="font-bold">THỦ KHO</p>
            <p class="text-italic" style="font-size: 11px; margin-bottom: 70px;">(Ký, ghi rõ họ tên)</p>
        </div>
        <div>
            <p class="font-bold">QUẢN LÝ KHO</p>
            <p class="text-italic" style="font-size: 11px; margin-bottom: 70px;">(Ký, ghi rõ họ tên)</p>
        </div>
        <div>
            <p class="font-bold">TT.KTSC</p>
            <p class="text-italic" style="font-size: 11px; margin-bottom: 70px;">(Ký, ghi rõ họ tên)</p>
        </div>
        <div>
            <p class="font-bold">P.QLTB</p>
            <p class="text-italic" style="font-size: 11px; margin-bottom: 70px;">(Ký, ghi rõ họ tên)</p>
        </div>
    </div>

</body>
</html>
<?php /**PATH D:\Project\resources\views\warehouse\transaction-detail-print.blade.php ENDPATH**/ ?>
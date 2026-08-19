<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Nhiều Phiếu Chuyển Kho</title>
    <style>
        body { font-family: "Times New Roman", Times, serif; line-height: 1.6; color: #000; margin: 0; padding: 0; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm; margin: 0 auto; background: #fff; box-sizing: border-box; position: relative; page-break-after: always; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 0; }
        .header p { font-size: 14px; margin: 5px 0; }
        .company-name { font-size: 16px; font-weight: bold; text-transform: uppercase; margin-bottom: 5px; text-align: left;}
        .project-name { font-size: 14px; margin-bottom: 20px; font-style: italic; text-align: left; }
        .info-section { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-item { font-size: 15px; margin-bottom: 8px; }
        .info-label { font-weight: bold; display: inline-block; width: 120px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table th, .table td { border: 1px solid #000; padding: 8px; text-align: center; font-size: 14px; }
        .table th { background-color: #f2f2f2; font-weight: bold; }
        .table td.text-left { text-align: left; }
        .footer { margin-top: 50px; display: grid; grid-template-columns: 1fr 1fr 1fr; text-align: center; }
        .footer div { font-size: 14px; font-weight: bold; }
        .signature-space { height: 80px; }
        @media print {
            @page { margin: 0; size: auto; }
            body { background: none; padding: 10mm; }
            .page { margin: 0; box-shadow: none; width: 100%; }
            .no-print { display: none !important; }
        }
        .btn-container { position: fixed; top: 20px; right: 20px; display: flex; flex-direction: column; gap: 10px; z-index: 1000; }
        .no-print-btn { padding: 10px 20px; background: #4f46e5; color: #fff; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; text-align: center;}
        .cancel-btn { padding: 10px 20px; background: #ef4444; color: #fff; text-decoration: none; border-radius: 5px; text-align: center; font-weight: bold; font-family: sans-serif; font-size: 14px; }
    </style>
</head>
<body>
    <div class="btn-container no-print">
        <button class="no-print-btn" onclick="window.print()">🖨️ In Tất Cả Phiếu</button>
        <a href="<?php echo e(route('warehouse.stock-transfer.index')); ?>" class="cancel-btn">❌ Hủy in</a>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transfer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
    <div class="page">
        <div class="company-name">CÔNG TY CỔ PHẦN ĐẦU TƯ VÀ HẠ TẦNG V-ALPHA</div>
        <div class="project-name">Dự án: <?php echo e($transfer->fromProject?->name ?? 'Nội bộ'); ?></div>
        
        <div class="header">
            <h1>PHIẾU CHUYỂN KHO NỘI BỘ</h1>
            <p>Mã phiếu: <strong><?php echo e($transfer->transfer_code); ?></strong></p>
        </div>

        <div class="info-section">
            <div class="left-col">
                <div class="info-item"><span class="info-label">Ngày chuyển:</span> <?php echo e($transfer->transfer_date?->format('d/m/Y H:i')); ?></div>
                <div class="info-item"><span class="info-label">Từ Chi nhánh:</span> <?php echo e($transfer->fromProject?->name ?? '—'); ?></div>
                <div class="info-item"><span class="info-label">Người gửi:</span> <?php echo e($transfer->creator?->name ?? '—'); ?> <?php echo e($transfer->sender_phone ? '('.$transfer->sender_phone.')' : ''); ?></div>
            </div>
            <div class="right-col">
                <div class="info-item">&nbsp;</div>
                <div class="info-item"><span class="info-label">Đến Chi nhánh:</span> <?php echo e($transfer->toProject?->name ?? '—'); ?></div>
                <div class="info-item"><span class="info-label">Người nhận:</span> <?php echo e($transfer->receiver?->name ?? '—'); ?> <?php echo e($transfer->receiver_phone ? '('.$transfer->receiver_phone.')' : ''); ?></div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transfer->note): ?>
            <div style="margin-bottom: 20px; font-size: 15px;">
                <span class="info-label" style="font-weight: bold; width: 120px; display: inline-block;">Ghi chú:</span> <?php echo e($transfer->note); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 5%;">STT</th>
                    <th style="width: 15%;">Mã Vật Tư</th>
                    <th style="width: 35%;">Tên Vật Tư</th>
                    <th style="width: 10%;">Số Lượng</th>
                    <th style="width: 8%;">ĐVT</th>
                    <th style="width: 12%;">Vị Trí</th>
                    <th style="width: 15%;">Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $transfer->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td class="font-mono"><?php echo e($item->product_code); ?></td>
                        <td class="text-left"><?php echo e($item->product?->name ?? '—'); ?></td>
                        <td><?php echo e($item->quantity); ?></td>
                        <td><?php echo e($item->product?->unit ?? '—'); ?></td>
                        <td><?php echo e($item->location ?? ''); ?></td>
                        <td class="text-left"><?php echo e($item->note ?? ''); ?></td>
                    </tr>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </tbody>
        </table>

        <div class="footer">
            <div>
                <p>Người Gửi</p>
                <div class="signature-space"></div>
            </div>
            <div>
                <p>Người Nhận</p>
                <div class="signature-space"></div>
            </div>
            <div>
                <p>Người Phê Duyệt</p>
                <div class="signature-space"></div>
            </div>
        </div>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</body>
</html>
<?php /**PATH D:\Project\resources\views\warehouse\stock-transfer-print-bulk.blade.php ENDPATH**/ ?>
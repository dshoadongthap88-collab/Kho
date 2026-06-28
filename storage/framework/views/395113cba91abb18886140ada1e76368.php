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
        @page { size: auto; margin: 10mm 15mm; } /* Hide browser headers/footers in Chrome by adjusting margins */
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #000; color: #fff; cursor: pointer;">In Phiếu</button>
        <button onclick="window.location.href='<?php echo e(route('purchase-plan')); ?>'" style="padding: 8px 16px; background: #ccc; cursor: pointer;">Đóng</button>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px;">
        <div style="font-weight: bold; font-size: 16px;">
            DỰ ÁN: <?php echo e(session('current_house', 1) == 2 ? 'HẬU NGHĨA' : (session('current_house', 1) == 3 ? 'CẦN GIỜ' : 'HÓC MÔN')); ?>

        </div>
        <div class="header" style="text-align: center; margin-bottom: 0;">
            <div class="title" style="font-size: 24px; font-weight: bold; text-transform: uppercase; margin: 10px 0;">PHIẾU ĐỀ XUẤT MUA HÀNG</div>
            <div>Ngày in: <?php echo e(now()->format('d/m/Y H:i')); ?></div>
        </div>
        <div style="width: 150px;"></div> <!-- Spacer for flex alignment -->
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th width="12%">Mã VT</th>
                <th width="25%">Tên Vật Tư</th>
                <th width="8%">ĐVT</th>
                <th width="10%">Tồn Hiện Tại</th>
                <th width="10%">SL Đề Xuất</th>
                <th width="12%">Trạng Thái</th>
                <th width="18%">NCC Gần Nhất</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $latestPoItem = \App\Models\PurchaseOrderItem::where('product_id', $plan->product_id)
                        ->whereHas('purchaseOrder', function($q) {
                            $q->whereNotNull('supplier_id');
                        })
                        ->latest('id')
                        ->with('purchaseOrder.supplier')
                        ->first();
                    $supplierName = $latestPoItem && $latestPoItem->purchaseOrder && $latestPoItem->purchaseOrder->supplier 
                                    ? $latestPoItem->purchaseOrder->supplier->name 
                                    : '';
                ?>
                <tr>
                    <td class="text-center"><?php echo e($index + 1); ?></td>
                    <td class="text-center"><?php echo e($plan->product->code); ?></td>
                    <td><?php echo e($plan->product->name); ?></td>
                    <td class="text-center"><?php echo e($plan->product->unit ?? 'Cái'); ?></td>
                    <td class="text-right"><?php echo e(number_format($plan->product->inventory?->quantity ?? 0, 0)); ?></td>
                    <td class="text-right"><?php echo e(number_format($plan->proposed_quantity, 0)); ?></td>
                    <td class="text-center">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($plan->status === 'pending'): ?> Đề xuất
                        <?php elseif($plan->status === 'ordered'): ?> Đã đặt
                        <?php elseif($plan->status === 'unreceived'): ?> Chưa giao
                        <?php elseif($plan->status === 'partial'): ?> Giao thiếu
                        <?php else: ?> Đủ hàng <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </td>
                    <td><?php echo e($supplierName); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
<?php /**PATH D:\Project\resources\views/warehouse/purchase-plan-print.blade.php ENDPATH**/ ?>
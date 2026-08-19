<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title></title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0; /* Removes browser header/footer */
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 14px;
            line-height: 1.5;
            color: #000;
            padding: 15mm; /* Move margin to body */
        }
        .header {
            width: 100%;
            margin-bottom: 20px;
        }
        .company-info {
            float: left;
            text-align: left;
        }
        .company-name {
            font-weight: bold;
            text-transform: uppercase;
        }
        .project-name {
            font-weight: normal;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 30px;
            margin-bottom: 20px;
            clear: both;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            font-weight: bold;
            text-align: center;
            background-color: #f2f2f2;
        }
        .text-center { text-align: center; }
        @media print {
            .no-print { display: none !important; }
            body { padding-top: 15mm; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4f46e5; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">🖨️ IN DANH SÁCH</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #6b7280; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; margin-left: 10px;">❌ ĐÓNG</button>
    </div>

    <div class="header clearfix">
        <div class="company-info">
            <div class="company-name">CÔNG TY CP ĐẦU TƯ VÀ HẠ TẦNG V-ALPHA</div>
            <div class="project-name">Dự án: <?php echo e(session('current_house', 1) == 2 ? 'Hậu Nghĩa' : (session('current_house', 1) == 3 ? 'Cần Giờ' : (session('current_house', 1) == 4 ? 'Cần Giuộc' : 'Hóc Môn'))); ?></div>
        </div>
    </div>

    <?php
        // Determine title based on contacts type
        $types = $contacts->pluck('type')->unique();
        $title = 'DANH SÁCH KHÁCH HÀNG/NHÀ CUNG CẤP';
        if ($types->count() == 1) {
            $type = $types->first();
            if ($type == 'customer') $title = 'DANH SÁCH KHÁCH HÀNG';
            if ($type == 'supplier') $title = 'DANH SÁCH NHÀ CUNG CẤP';
            if ($type == 'internal') $title = 'DANH SÁCH NỘI BỘ';
        }
    ?>

    <div class="title"><?php echo e($title); ?></div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">STT</th>
                <th style="width: 25%">Tên đối tác</th>
                <th style="width: 30%">Địa chỉ</th>
                <th style="width: 15%">Số điện thoại</th>
                <th style="width: 25%">Người liên hệ</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <tr>
                <td class="text-center"><?php echo e($index + 1); ?></td>
                <td><?php echo e($contact->name); ?></td>
                <td><?php echo e($contact->address); ?></td>
                <td><?php echo e($contact->phone); ?></td>
                <td><?php echo e($contact->contact_person); ?></td>
            </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 40px; text-align: right; padding-right: 60px;">
        <div style="display: inline-block; text-align: center;">
            <div style="font-weight: bold; margin-bottom: 80px;">NGƯỜI BÁO CÁO</div>
            <div style="font-weight: bold;"><?php echo e(Auth::user()->name); ?></div>
        </div>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
<?php /**PATH D:\Project\resources\views\warehouse\contacts-print.blade.php ENDPATH**/ ?>
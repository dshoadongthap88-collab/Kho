<div>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Báo Cáo Ngày</h2>
        <div class="flex space-x-4">
            <input type="date" wire:model.live="date" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <a href="<?php echo e(route('warehouse.reports.daily.print', ['date' => $date])); ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                In Báo Cáo
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Tổng nhập -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider mb-2">Số mã Nhập kho</h3>
            <div class="text-3xl font-bold text-gray-800"><?php echo e($reportData['stockInCount']); ?></div>
        </div>

        <!-- Tổng xuất -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
            <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider mb-2">Số mã Xuất kho</h3>
            <div class="text-3xl font-bold text-gray-800"><?php echo e($reportData['stockOutCount']); ?></div>
        </div>

        <!-- Tổng chuyển -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider mb-2">Số mã Chuyển kho</h3>
            <div class="text-3xl font-bold text-gray-800"><?php echo e($reportData['stockTransferCount']); ?></div>
        </div>

        <!-- Tổng thu hồi -->
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider mb-2">Số mã Thu hồi</h3>
            <div class="text-3xl font-bold text-gray-800"><?php echo e($reportData['stockRecoveryCount']); ?></div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Thống Kê Đơn Xuất Kho</h3>
            <div class="flex items-center justify-between py-3 border-b">
                <span class="text-gray-600">Tổng số đơn xuất trong ngày</span>
                <span class="text-xl font-bold text-gray-800"><?php echo e($reportData['totalStockOutOrders']); ?></span>
            </div>
            <div class="flex items-center justify-between py-3 border-b">
                <span class="text-gray-600">Số mã tài sản (dự án) đã xuất</span>
                <span class="text-xl font-bold text-gray-800"><?php echo e($reportData['assetExportCount']); ?></span>
            </div>
            <div class="flex items-center justify-between py-3">
                <span class="text-gray-600">Số mã vật tư (dự án) đã xuất</span>
                <span class="text-xl font-bold text-gray-800"><?php echo e($reportData['materialExportCount']); ?></span>
            </div>
        </div>
    </div>
</div>
<?php /**PATH D:\Project\resources\views/livewire/warehouse/reports/daily-report.blade.php ENDPATH**/ ?>
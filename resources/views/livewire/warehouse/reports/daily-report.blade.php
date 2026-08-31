<div>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Báo Cáo Ngày</h2>
        <div class="flex space-x-4">
            <div class="flex items-center space-x-2">
                <span class="text-sm font-bold text-gray-500 uppercase tracking-widest">Từ</span>
                <input type="date" wire:model.live="dateFrom" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-bold">
                <span class="text-sm font-bold text-gray-500 uppercase tracking-widest">Đến</span>
                <input type="date" wire:model.live="dateTo" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm font-bold">
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('warehouse.reports.daily.print', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'type' => 'all', 'detailed' => 1]) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center shrink-0">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    In Tổng hợp
                </a>
                <a href="{{ route('warehouse.reports.daily.print', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'type' => 'import', 'detailed' => 1]) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center shrink-0">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8h-16"></path></svg>
                    In Nhập
                </a>
                <a href="{{ route('warehouse.reports.daily.print', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'type' => 'export', 'detailed' => 1]) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center shrink-0">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"></path></svg>
                    In Xuất
                </a>
            </div>
            <div class="flex items-center gap-2 ml-2 pl-2 border-l border-gray-200">
                <label class="flex items-center gap-1 text-[10px] font-bold text-gray-500 cursor-pointer">
                    <input type="checkbox" wire:model="includeDailyReport" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                    BÁO CÁO NGÀY
                </label>
                <label class="flex items-center gap-1 text-[10px] font-bold text-gray-500 cursor-pointer">
                    <input type="checkbox" wire:model.live="includeDetailReport" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                    CHI TIẾT NGÀY
                </label>
                <a href="{{ route('warehouse.reports.daily.print', ['dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'detailed' => $includeDetailReport ? 1 : 0, 'zalo' => 1]) }}" target="_blank" class="flex items-center gap-1.5 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-black transition shadow-sm cursor-pointer ml-1" title="Tải PDF báo cáo và mở Zalo">
                    <span class="text-sm">💬</span> GỬI ZALO
                </a>
            </div>
        </div>
        @if (session()->has('error'))
            <div class="absolute top-0 right-0 mt-16 mr-4 text-xs font-bold text-red-500 bg-red-100 px-3 py-2 rounded">{{ session('error') }}</div>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 mb-6">
        <!-- Tổng nhập -->
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-green-500 flex justify-between items-center">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Số mã Nhập kho</h3>
            <div class="text-xl font-black text-gray-800">{{ $reportData['stockInCount'] }}</div>
        </div>

        <!-- Tổng xuất -->
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-red-500 flex justify-between items-center">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Số mã Xuất kho</h3>
            <div class="text-xl font-black text-gray-800">{{ $reportData['stockOutCount'] }}</div>
        </div>

        <!-- Tổng chuyển -->
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-yellow-500 flex justify-between items-center">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Số mã Chuyển kho</h3>
            <div class="text-xl font-black text-gray-800">{{ $reportData['stockTransferCount'] }}</div>
        </div>

        <!-- Tổng thu hồi -->
        <div class="bg-white rounded-lg shadow p-3 border-l-4 border-blue-500 flex justify-between items-center">
            <h3 class="text-gray-500 text-xs font-bold uppercase tracking-wider">Số mã Thu hồi</h3>
            <div class="text-xl font-black text-gray-800">{{ $reportData['stockRecoveryCount'] }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
        <div class="bg-white rounded-lg shadow p-2">
            <h3 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">Thống Kê Đơn Xuất Kho</h3>
            <div class="flex items-center justify-between py-2 border-b">
                <span class="text-sm text-gray-600">Tổng số đơn xuất trong ngày</span>
                <span class="text-lg font-bold text-gray-800">{{ $reportData['totalStockOutOrders'] }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b">
                <span class="text-sm text-gray-600">Số mã tài sản (dự án) đã xuất</span>
                <span class="text-lg font-bold text-gray-800">{{ $reportData['assetExportCount'] }}</span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-gray-600">Số mã vật tư (dự án) đã xuất</span>
                <span class="text-lg font-bold text-gray-800">{{ $reportData['materialExportCount'] }}</span>
            </div>
        </div>

        <!-- Thống Kê Nhập Kho -->
        <div class="bg-white rounded-lg shadow p-2">
            <h3 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">Thống Kê Nhập Kho</h3>
            <div class="flex items-center justify-between py-2 border-b">
                <span class="text-sm text-gray-600">Tổng số đơn nhập/ngày</span>
                <span class="text-lg font-bold text-gray-800">{{ $reportData['totalStockInOrders'] }}</span>
            </div>
            <div class="flex items-center justify-between py-2 border-b">
                <span class="text-sm text-gray-600">Tổng số mã vật tư nhập/ngày</span>
                <span class="text-lg font-bold text-gray-800">{{ $reportData['stockInCount'] }}</span>
            </div>
            <div class="flex items-center justify-between py-2">
                <span class="text-sm text-gray-600">Tổng nhà cung cấp giao/ngày</span>
                <span class="text-lg font-bold text-gray-800">{{ $reportData['supplierDeliveryCount'] }}</span>
            </div>
        </div>

        <!-- Báo cáo chi tiết ngày -->
        <label class="bg-sky-50 rounded-lg shadow p-2 border-2 {{ $printDetailed ? 'border-sky-500 bg-sky-100' : 'border-dashed border-sky-300' }} flex flex-col justify-center items-center hover:bg-sky-100 transition group relative cursor-pointer">
            <input type="checkbox" wire:model.live="printDetailed" class="absolute top-2 right-2 w-5 h-5 text-sky-600 rounded focus:ring-sky-500 border-gray-300">
            <svg class="w-10 h-10 {{ $printDetailed ? 'text-sky-700' : 'text-sky-500' }} mb-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            <h3 class="text-sm font-bold text-sky-900 uppercase tracking-wide">Báo Cáo Chi Tiết Ngày</h3>
            <p class="text-xs text-sky-600 mt-1 mb-2 text-center">Tích chọn để tự động in kèm toàn bộ lịch sử Nhập/Xuất/Chuyển</p>
            <div class="{{ $printDetailed ? 'bg-sky-600' : 'bg-gray-400' }} text-white text-xs font-bold py-1 px-3 rounded-full shadow transition-colors">
                {{ $printDetailed ? 'Đã chọn in kèm' : 'Chưa chọn' }}
            </div>
        </label>
    </div>
</div>

<div>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Báo Cáo Ngày</h2>
        <div class="flex space-x-4">
            <input type="date" wire:model.live="date" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <a href="{{ route('warehouse.reports.daily.print', ['date' => $date, 'detailed' => $printDetailed ? 1 : 0]) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                In Báo Cáo
            </a>
            <div class="flex items-center gap-2 ml-2 pl-2 border-l border-gray-200">
                <label class="flex items-center gap-1 text-[10px] font-bold text-gray-500 cursor-pointer">
                    <input type="checkbox" wire:model="includeDailyReport" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                    BÁO CÁO NGÀY
                </label>
                <label class="flex items-center gap-1 text-[10px] font-bold text-gray-500 cursor-pointer">
                    <input type="checkbox" wire:model="includeDetailReport" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                    CHI TIẾT NGÀY
                </label>
                <button type="button" wire:click="generateZaloMessage" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-xs font-black transition shadow-sm cursor-pointer ml-1" title="Copy báo cáo và mở Zalo">
                    <span wire:loading.remove wire:target="generateZaloMessage" class="text-sm">💬</span>
                    <span wire:loading wire:target="generateZaloMessage" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    GỬI ZALO
                </button>
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

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('zalo-message-generated', (data) => {
                const message = data.message;
                
                navigator.clipboard.writeText(message).then(() => {
                    alert('Đã copy nội dung báo cáo ngày. Hệ thống sẽ mở Zalo để bạn dán (Ctrl+V) và gửi.');
                    window.open('https://chat.zalo.me/', '_blank');
                }).catch(err => {
                    console.error('Không thể copy: ', err);
                    alert('Có lỗi xảy ra khi copy nội dung. Vui lòng thử lại.');
                });
            });
        });
    </script>

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

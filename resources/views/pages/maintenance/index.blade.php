<x-warehouse-layout title="Hệ Thống Quản Lý Bảo Dưỡng (ERP)">
    <div class="py-12">
        <div class="w-full  space-y-6">
            <!-- Dashboard cảnh báo & Danh sách thiết bị -->
            <div class="col-span-1 md:col-span-2 mb-8">
                @livewire('maintenance.asset-maintenance-dashboard')
            </div>

            <!-- Quản lý cập nhật ODO hàng loạt -->
            <div class="col-span-1 md:col-span-2 mb-8">
                @livewire('maintenance.daily-odo-manager')
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <!-- Cập nhật ca làm việc -->
                <div class="col-span-1">
                    @livewire('maintenance.shift-log-form')
                </div>

                <!-- Xác nhận hoàn thành bảo dưỡng -->
                <div class="col-span-1">
                    @livewire('maintenance.ticket-completion-form')
                </div>
            </div>

            <!-- Danh sách phiếu bảo dưỡng -->
            <div class="col-span-1 md:col-span-2 mt-8">
                @livewire('maintenance.ticket-list')
            </div>

        </div>
    </div>
</x-warehouse-layout>

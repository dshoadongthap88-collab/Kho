<x-warehouse-layout title="Khách hàng/NCC">
    <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
        <p class="text-sm"><strong>ℹ️ Thông báo:</strong> Module này đã được chia thành 2 module riêng biệt:</p>
        <ul class="mt-2 ml-4 text-sm">
            <li>• <a href="{{ route('warehouse.customer-supplier') }}" class="underline font-semibold hover:text-blue-900">📋 Danh sách Khách hàng/NCC</a></li>
            <li>• <a href="{{ route('warehouse.purchase-order') }}" class="underline font-semibold hover:text-blue-900">📦 Đơn đặt hàng</a></li>
        </ul>
    </div>
    <livewire:warehouse.contact-manager />
</x-warehouse-layout>

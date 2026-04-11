<div>
    <!-- Tab Navigation -->
    <div class="flex gap-2 border-b border-gray-200 mb-6">
        <button 
            wire:click="switchTab('contacts')" 
            class="px-6 py-3 font-medium transition-colors {{ $activeTab === 'contacts' ? 'border-b-2 border-amber-600 text-amber-600' : 'border-b-2 border-transparent text-gray-600 hover:text-gray-900' }}"
        >
            📋 Danh sách Khách hàng/NCC
        </button>
        <button 
            wire:click="switchTab('purchase-orders')" 
            class="px-6 py-3 font-medium transition-colors {{ $activeTab === 'purchase-orders' ? 'border-b-2 border-amber-600 text-amber-600' : 'border-b-2 border-transparent text-gray-600 hover:text-gray-900' }}"
        >
            📦 Đơn đặt hàng
        </button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        @if ($activeTab === 'contacts')
            <livewire:warehouse.contact-list />
        @elseif ($activeTab === 'purchase-orders')
            <livewire:warehouse.purchase-order-list />
        @endif
    </div>
</div>
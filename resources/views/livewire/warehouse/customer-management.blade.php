<div>
    <!-- Tab Navigation & Actions -->
    <div class="flex justify-between items-end border-b border-gray-200 mb-6">
        <div class="flex gap-2">
            <button 
                class="px-6 py-3 font-medium transition-colors border-b-2 border-amber-600 text-amber-600"
            >
                📋 Danh sách Khách hàng/NCC
            </button>
        </div>
        <div class="mb-2 no-print">
            <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg flex items-center gap-2 shadow-sm transition-colors text-sm font-semibold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>In danh sách</span>
            </button>
        </div>
    </div>

    <!-- Content -->
    <div class="tab-content">
        <livewire:warehouse.contact-list />
    </div>
</div>
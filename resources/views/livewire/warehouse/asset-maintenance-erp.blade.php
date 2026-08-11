<div>

    <!-- Navigation Tabs -->
    <div class="mb-6 overflow-x-auto custom-scrollbar pb-1">
        <nav class="flex space-x-2 min-w-max bg-white p-1.5 rounded-xl border border-slate-200 shadow-sm">
            <button wire:click="switchTab('dashboard')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 {{ $activeTab === 'dashboard' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span>📊</span> Tổng quan (Dashboard)
            </button>
            <button wire:click="switchTab('asset-manager')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 {{ $activeTab === 'asset-manager' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span>⚙️</span> DS Thiết bị
            </button>
            <button wire:click="switchTab('bom-manager')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 {{ $activeTab === 'bom-manager' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span>🔧</span> Định mức BD (BOM)
            </button>
            <button wire:click="switchTab('ticket-list')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 {{ $activeTab === 'ticket-list' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span>📋</span> Phiếu Bảo dưỡng
            </button>
            <button wire:click="switchTab('odo-manager')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 {{ $activeTab === 'odo-manager' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span>⏲️</span> Cập nhật ODO
            </button>
            <button wire:click="switchTab('shift-log')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 {{ $activeTab === 'shift-log' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span>👷</span> Giao ca (Log)
            </button>
            <button wire:click="switchTab('ticket-completion')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 {{ $activeTab === 'ticket-completion' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                <span>✅</span> Xác nhận Hoàn thành
            </button>
        </nav>
    </div>

    <!-- Tab Content Container -->
    <div class="relative bg-transparent rounded-xl min-h-[500px]">
        
        <!-- Loading overlay -->
        <div wire:loading wire:target="switchTab" class="absolute inset-0 z-50 flex items-center justify-center bg-slate-50/50 backdrop-blur-sm rounded-xl">
            <div class="flex flex-col items-center gap-3 p-2 bg-white rounded-2xl shadow-xl border border-slate-100">
                <div class="w-8 h-8 border-4 border-slate-200 border-t-sky-600 rounded-full animate-spin"></div>
                <div class="text-sm font-bold text-slate-600">Đang tải phân hệ...</div>
            </div>
        </div>

        <!-- Render active tab content dynamically -->
        @if ($activeTab === 'dashboard')
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                @livewire('maintenance.asset-maintenance-dashboard')
            </div>
        @elseif ($activeTab === 'asset-manager')
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                @livewire('warehouse.asset.asset-manager')
            </div>
        @elseif ($activeTab === 'bom-manager')
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                @livewire('warehouse.maintenance-bom-manager')
            </div>
        @elseif ($activeTab === 'ticket-list')
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                @livewire('maintenance.ticket-list')
            </div>
        @elseif ($activeTab === 'odo-manager')
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                @livewire('maintenance.daily-odo-manager')
            </div>
        @elseif ($activeTab === 'shift-log')
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                @livewire('maintenance.shift-log-form')
            </div>
        @elseif ($activeTab === 'ticket-completion')
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                @livewire('maintenance.ticket-completion-form')
            </div>
        @endif

    </div>
    
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            height: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1; 
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8; 
        }
    </style>
</div>
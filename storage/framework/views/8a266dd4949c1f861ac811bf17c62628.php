<div>

    <!-- Navigation Tabs -->
    <div class="mb-6 overflow-x-auto custom-scrollbar pb-1">
        <nav class="flex space-x-2 min-w-max bg-white p-1.5 rounded-xl border border-slate-200 shadow-sm">
            <button wire:click="switchTab('dashboard')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 <?php echo e($activeTab === 'dashboard' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'); ?>">
                <span>📊</span> Tổng quan (Dashboard)
            </button>
            <button wire:click="switchTab('asset-manager')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 <?php echo e($activeTab === 'asset-manager' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'); ?>">
                <span>⚙️</span> DS Thiết bị
            </button>
            <button wire:click="switchTab('bom-manager')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 <?php echo e($activeTab === 'bom-manager' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'); ?>">
                <span>🔧</span> Định mức BD (BOM)
            </button>
            <button wire:click="switchTab('ticket-list')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 <?php echo e($activeTab === 'ticket-list' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'); ?>">
                <span>📋</span> Phiếu Bảo dưỡng
            </button>
            <button wire:click="switchTab('odo-manager')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 <?php echo e($activeTab === 'odo-manager' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'); ?>">
                <span>⏲️</span> Cập nhật ODO
            </button>
            <button wire:click="switchTab('shift-log')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 <?php echo e($activeTab === 'shift-log' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'); ?>">
                <span>👷</span> Giao ca (Log)
            </button>
            <button wire:click="switchTab('ticket-completion')" 
                class="px-4 py-2 rounded-lg text-sm font-bold transition-all flex items-center gap-2 <?php echo e($activeTab === 'ticket-completion' ? 'bg-sky-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'); ?>">
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
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'dashboard'): ?>
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('maintenance.asset-maintenance-dashboard');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3030195947-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        <?php elseif($activeTab === 'asset-manager'): ?>
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('warehouse.asset.asset-manager');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3030195947-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        <?php elseif($activeTab === 'bom-manager'): ?>
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('warehouse.maintenance-bom-manager');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3030195947-2', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        <?php elseif($activeTab === 'ticket-list'): ?>
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('maintenance.ticket-list');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3030195947-3', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        <?php elseif($activeTab === 'odo-manager'): ?>
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('maintenance.daily-odo-manager');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3030195947-4', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        <?php elseif($activeTab === 'shift-log'): ?>
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('maintenance.shift-log-form');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3030195947-5', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        <?php elseif($activeTab === 'ticket-completion'): ?>
            <div class="animate-in fade-in slide-in-from-bottom-4 duration-500">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('maintenance.ticket-completion-form');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3030195947-6', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
</div><?php /**PATH D:\Project\resources\views/livewire/warehouse/asset-maintenance-erp.blade.php ENDPATH**/ ?>
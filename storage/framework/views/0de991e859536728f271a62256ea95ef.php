<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <!-- Tổng mã đề xuất -->
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2.5 bg-blue-50 text-blue-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-500 uppercase">Tổng mã đề xuất</h3>
            </div>
            <p class="text-3xl font-black text-slate-800"><?php echo e($totalPlans); ?></p>
        </div>

        <!-- Đã nhận đủ -->
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-500 uppercase">Đã nhận đủ</h3>
            </div>
            <p class="text-3xl font-black text-emerald-600"><?php echo e($completedPlans); ?></p>
        </div>

        <!-- Giao thiếu -->
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2.5 bg-amber-50 text-amber-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-500 uppercase">Giao thiếu</h3>
            </div>
            <p class="text-3xl font-black text-amber-600"><?php echo e($partialPlans); ?></p>
        </div>

        <!-- Chưa giao -->
        <div class="bg-white rounded-xl shadow-sm p-5 border border-slate-200">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2.5 bg-rose-50 text-rose-600 rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-500 uppercase">Chưa giao</h3>
            </div>
            <p class="text-3xl font-black text-rose-600"><?php echo e($unreceivedPlans); ?></p>
        </div>

        <!-- Tổng lượng cần giao bổ sung -->
        <div class="bg-slate-800 rounded-xl shadow-sm p-5 border border-slate-700">
            <div class="flex items-center gap-3 mb-2">
                <div class="p-2.5 bg-slate-700 text-white rounded-lg">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-sm font-bold text-slate-300 uppercase">SL còn thiếu</h3>
            </div>
            <p class="text-3xl font-black text-white"><?php echo e(number_format($totalMissing, 0)); ?></p>
        </div>
    </div>
</div>
<?php /**PATH D:\Project\resources\views\livewire\warehouse\purchase-plan\purchase-plan-dashboard.blade.php ENDPATH**/ ?>
<?php

$file = 'd:/Project/resources/views/livewire/warehouse/stock-in-form.blade.php';
$content = file_get_contents($file);

// 1. Thay thế phần Tab Navigation
$tabRegex = '/<!-- Tab Navigation -->.*?<\/div>/s';
$newTabs = '<!-- Tab Navigation & Import Button -->
    <div class="flex items-center justify-between mb-4 no-print">
        <div class="bg-white p-2 rounded-2xl shadow-md border border-slate-200 flex items-center gap-3 w-fit">
            <button wire:click="$set(\'activeTab\', \'form\')" class="px-8 py-3 rounded-xl text-[13px] font-black transition-all flex items-center gap-2 {{ $activeTab === \'form\' ? \'bg-indigo-600 text-white shadow-xl shadow-indigo-100\' : \'text-slate-500 hover:bg-slate-50\' }}">
                <span>📥</span> LẬP PHIẾU NHẬP
            </button>
            <button wire:click="$set(\'activeTab\', \'list\')" class="px-8 py-3 rounded-xl text-[13px] font-black transition-all flex items-center gap-2 {{ $activeTab === \'list\' ? \'bg-indigo-600 text-white shadow-xl shadow-indigo-100\' : \'text-slate-500 hover:bg-slate-50\' }}">
                <span>📋</span> DANH SÁCH PHIẾU
            </button>
        </div>

        @if($activeTab === \'form\')
        <!-- Nút Nhập Tự Động -->
        <button type="button" wire:click="$set(\'showImportModal\', true)" 
                class="px-5 py-3 text-[13px] font-black text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-2xl shadow-md transition-all duration-150 flex items-center gap-2 active:scale-95">
            ⚡ Nhập từ Excel / PDF / Ảnh AI
        </button>
        @endif
    </div>';

// Chỉ thay thế occurrence đầu tiên (Tab Navigation)
$content = preg_replace($tabRegex, $newTabs, $content, 1);

// 2. Xóa Nút cũ ở PHIẾU NHẬP KHO MỚI
$oldButtonRegex = '/<!-- Nút Nhập Tự Động cực kỳ sang trọng -->.*?<\/button>/s';
$content = preg_replace($oldButtonRegex, '', $content, 1);

// 3. Giảm padding các ô trong bảng
$content = str_replace('py-3', 'py-1', $content);
$content = str_replace('py-1.5', 'py-1', $content);

file_put_contents($file, $content);
echo "Done\n";
?>

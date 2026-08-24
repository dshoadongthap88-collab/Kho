<div>

    {{-- ===== PAGE HEADER ===== --}}
    <div class="flex items-center justify-between mb-6 no-print">
        <div>
            <h1 class="text-xl font-black text-slate-800 flex items-center gap-2.5">
                <span class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center shadow-sm">
                    <svg class="w-4.5 h-4.5 text-white w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
                Quản lý CRM
            </h1>
            <p class="text-sm text-slate-400 mt-1 ml-10">Khách hàng · Nhà cung cấp · Đối tác nội bộ</p>
        </div>

        {{-- Quick action: In danh sách --}}
        <button type="button"
                wire:click="$dispatchTo('warehouse.contact-list', 'trigger-print')"
                class="flex items-center gap-2 px-4 py-2 text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-600 hover:text-white border border-indigo-200 rounded-xl transition shadow-sm no-print">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            In danh sách
        </button>
    </div>

    {{-- ===== NAV TABS ===== --}}
    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-2xl mb-5 w-fit no-print">
        <button wire:click="switchTab('contacts')"
                class="flex items-center gap-2 px-5 py-2 text-sm font-bold rounded-xl transition-all
                       {{ $activeTab === 'contacts'
                            ? 'bg-white text-indigo-600 shadow-sm'
                            : 'text-slate-500 hover:text-slate-700 hover:bg-white/60' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Danh bạ đối tác
        </button>
    </div>

    {{-- ===== CONTENT ===== --}}
    @if($activeTab === 'contacts')
        <livewire:warehouse.contact-list wire:key="contact-list-component" />
    @endif

</div>

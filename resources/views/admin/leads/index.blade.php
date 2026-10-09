<x-app-layout>
    <div class="py-6 sm:py-8 bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 min-h-screen transition-colors duration-300"
         x-data="{ 
            openCreateModal: new URLSearchParams(window.location.search).has('create'),
            viewMode: '{{ $viewMode }}',
            draggedLeadId: null,
            dragOverColumn: null,
            
            async updateLeadStatus(leadId, newStatus) {
                try {
                    const res = await fetch(`/admin/leads/${leadId}/kanban-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (window.showToast) window.showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 400);
                    } else {
                        if (window.showToast) window.showToast('Gagal mengubah status', 'error');
                    }
                } catch(e) {
                    console.error(e);
                }
            },

            async quickSnooze(leadId, days) {
                try {
                    const res = await fetch(`/admin/leads/${leadId}/quick-followup`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ days: days })
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (window.showToast) window.showToast(data.message, 'success');
                        setTimeout(() => window.location.reload(), 400);
                    }
                } catch(e) {
                    console.error(e);
                }
            }
         }">
        
        <div class="w-full space-y-6">

            <!-- Flash Alert -->
            <x-flash />

            <!-- Title & Top Action Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>Leads &amp; Pipeline</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Kelola data prospek, follow-up instan, dan konversi deal ke project.</p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Quick Snippets Button -->
                    <button @click="$dispatch('open-quick-snippets')" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-emerald-500 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-all shadow-xs">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600">content_paste</span>
                        <span>Template Chat WA</span>
                    </button>

                    <!-- Export Dropdown (CSV, PDF, Word) -->
                    <div class="relative" x-data="{ openExport: false }">
                        <button @click="openExport = !openExport" @click.outside="openExport = false" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px] text-sky-600">download</span>
                            <span>Export</span>
                            <span class="material-symbols-outlined text-[14px] text-zinc-400">expand_more</span>
                        </button>
                        <div x-show="openExport" x-cloak 
                             class="absolute left-0 sm:right-0 sm:left-auto mt-1.5 w-44 bg-white dark:bg-zinc-900 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-800 py-1 z-50 text-xs font-medium">
                            <a href="{{ route('admin.export.leads', array_merge(request()->query(), ['format' => 'csv'])) }}" 
                               class="flex items-center gap-2 px-3 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                <span class="material-symbols-outlined text-[16px] text-emerald-600">table_chart</span>
                                <span>Export CSV / Excel</span>
                            </a>
                            <a href="{{ route('admin.export.leads', array_merge(request()->query(), ['format' => 'pdf'])) }}" target="_blank"
                               class="flex items-center gap-2 px-3 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                <span class="material-symbols-outlined text-[16px] text-rose-600">picture_as_pdf</span>
                                <span>Export PDF</span>
                            </a>
                            <a href="{{ route('admin.export.leads', array_merge(request()->query(), ['format' => 'word'])) }}" 
                               class="flex items-center gap-2 px-3 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                <span class="material-symbols-outlined text-[16px] text-blue-600">description</span>
                                <span>Export Word (.doc)</span>
                            </a>
                        </div>
                    </div>

                    <!-- Tambah Lead Button -->
                    <button @click="openCreateModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">add_circle</span>
                        <span>Tambah Lead</span>
                    </button>

                    <!-- View Switcher (Positioned on the Right Side) -->
                    <div class="flex items-center p-0.5 bg-zinc-100 dark:bg-zinc-800/80 rounded-lg border border-zinc-200/80 dark:border-zinc-700/60">
                        <a href="{{ route('admin.leads.index', array_merge(request()->query(), ['view' => 'kanban'])) }}" 
                           class="px-2.5 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition-all {{ $viewMode === 'kanban' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200' }}"
                           title="Tampilan Kanban Board Pipeline">
                            <span class="material-symbols-outlined text-[16px]">view_kanban</span>
                            <span class="hidden sm:inline">Kanban</span>
                        </a>
                        <a href="{{ route('admin.leads.index', array_merge(request()->query(), ['view' => 'table'])) }}" 
                           class="px-2.5 py-1 rounded-md text-xs font-semibold flex items-center gap-1.5 transition-all {{ $viewMode === 'table' ? 'bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white shadow-xs' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-800 dark:hover:text-zinc-200' }}"
                           title="Tampilan Tabel List">
                            <span class="material-symbols-outlined text-[16px]">table_rows</span>
                            <span class="hidden sm:inline">Tabel</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Filter Tabs (Only shown in Table / List View) -->
            @if($viewMode === 'table')
                <div class="inline-flex h-10 items-center justify-start rounded-xl bg-zinc-100 dark:bg-zinc-900 p-1 text-zinc-500 gap-1 overflow-x-auto custom-scrollbar border border-zinc-200/70 dark:border-zinc-800/80 max-w-full">
                    <a href="{{ route('admin.leads.index', ['view' => 'table']) }}" 
                       class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ !request('status') && !request('filter') ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                        Semua ({{ $statusCounts['all'] }})
                    </a>
                    <a href="{{ route('admin.leads.index', ['status' => 'belum_dihubungi', 'view' => 'table']) }}" 
                       class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'belum_dihubungi' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                        Belum Dihubungi ({{ $statusCounts['belum_dihubungi'] }})
                    </a>
                    <a href="{{ route('admin.leads.index', ['status' => 'sudah_chat', 'view' => 'table']) }}" 
                       class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'sudah_chat' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                        Sudah Chat ({{ $statusCounts['sudah_chat'] }})
                    </a>
                    <a href="{{ route('admin.leads.index', ['status' => 'nego', 'view' => 'table']) }}" 
                       class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'nego' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                        Nego ({{ $statusCounts['nego'] }})
                    </a>
                    <a href="{{ route('admin.leads.index', ['status' => 'deal', 'view' => 'table']) }}" 
                       class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'deal' ? 'bg-emerald-600 text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                        Deal ({{ $statusCounts['deal'] }})
                    </a>
                    <a href="{{ route('admin.leads.index', ['status' => 'tidak_lanjut', 'view' => 'table']) }}" 
                       class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'tidak_lanjut' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                        Tidak Lanjut ({{ $statusCounts['tidak_lanjut'] }})
                    </a>
                    @if($statusCounts['overdue'] > 0)
                        <a href="{{ route('admin.leads.index', ['filter' => 'overdue', 'view' => 'table']) }}" 
                           class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('filter') === 'overdue' ? 'bg-rose-600 text-white shadow-xs font-semibold' : 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 font-medium' }}">
                            ⚠️ Telat ({{ $statusCounts['overdue'] }})
                        </a>
                    @endif
                </div>
            @endif

            <!-- Search & Filters Row (Clean Minimalist Toolbar) -->
            <form method="GET" action="{{ route('admin.leads.index') }}" class="bg-white dark:bg-zinc-900 p-3 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs flex flex-col md:flex-row items-center gap-2.5">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif

                <div class="relative flex-1 w-full">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-[18px]">search</span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama usaha, kontak, nomor WhatsApp..." 
                           class="w-full pl-9 pr-4 py-1.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg text-xs text-zinc-900 dark:text-white focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select name="sumber" onchange="this.form.submit()" 
                            class="w-full md:w-40 px-3 py-1.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg text-xs text-zinc-700 dark:text-zinc-300 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Semua Sumber</option>
                        <option value="warm_network" {{ request('sumber') == 'warm_network' ? 'selected' : '' }}>Warm Network</option>
                        <option value="cold_outreach" {{ request('sumber') == 'cold_outreach' ? 'selected' : '' }}>Cold Outreach</option>
                        <option value="komunitas" {{ request('sumber') == 'komunitas' ? 'selected' : '' }}>Komunitas</option>
                        <option value="marketplace" {{ request('sumber') == 'marketplace' ? 'selected' : '' }}>Marketplace</option>
                        <option value="referral" {{ request('sumber') == 'referral' ? 'selected' : '' }}>Referral</option>
                        <option value="website" {{ request('sumber') == 'website' ? 'selected' : '' }}>Website</option>
                    </select>

                    <select name="sort" onchange="this.form.submit()" 
                            class="w-full md:w-36 px-3 py-1.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-lg text-xs text-zinc-700 dark:text-zinc-300 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                        <option value="follow_up" {{ request('sort') == 'follow_up' ? 'selected' : '' }}>Jadwal Follow-up</option>
                    </select>

                    @if(request()->anyFilled(['q', 'sumber', 'sort', 'status', 'filter']))
                        <a href="{{ route('admin.leads.index', ['view' => $viewMode]) }}" class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800" title="Reset Filter">
                            <span class="material-symbols-outlined text-[18px] block">restart_alt</span>
                        </a>
                    @endif
                </div>
            </form>

            <!-- ============================================================ -->
            <!-- TAMPILAN 1: KANBAN BOARD VIEW -->
            <!-- ============================================================ -->
            @if($viewMode === 'kanban')
                @php
                    $columnsConfig = [
                        'belum_dihubungi' => ['title' => 'Belum Dihubungi', 'color' => 'zinc', 'badge' => 'bg-zinc-200 dark:bg-zinc-700 text-zinc-700 dark:text-zinc-200'],
                        'sudah_chat' => ['title' => 'Sudah Chat', 'color' => 'sky', 'badge' => 'bg-sky-100 dark:bg-sky-900/50 text-sky-700 dark:text-sky-300'],
                        'nego' => ['title' => 'Nego / Tertarik', 'color' => 'amber', 'badge' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300'],
                        'deal' => ['title' => 'Deal (Klien)', 'color' => 'emerald', 'badge' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300'],
                        'tidak_lanjut' => ['title' => 'Tidak Lanjut', 'color' => 'rose', 'badge' => 'bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300'],
                    ];
                @endphp

                <!-- Overdue Follow-up Subtle Alert Banner (Shadcn Style) -->
                @if($statusCounts['overdue'] > 0)
                    <div class="rounded-xl border border-rose-200 dark:border-rose-900/50 bg-rose-50/50 dark:bg-rose-950/20 px-4 py-3 text-xs text-rose-900 dark:text-rose-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-[18px] text-rose-600 dark:text-rose-400">warning</span>
                            <span><strong>{{ $statusCounts['overdue'] }} prospek</strong> memiliki jadwal follow-up yang terlambat.</span>
                        </div>
                        <a href="{{ request('filter') === 'overdue' ? route('admin.leads.index', ['view' => 'kanban']) : route('admin.leads.index', ['view' => 'kanban', 'filter' => 'overdue']) }}" 
                           class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg border border-rose-300 dark:border-rose-800 text-xs font-medium bg-white dark:bg-zinc-900 text-rose-700 dark:text-rose-300 hover:bg-rose-50 dark:hover:bg-zinc-800 transition-colors">
                            <span>{{ request('filter') === 'overdue' ? '✕ Tampilkan Semua' : 'Filter Yang Telat Saja' }}</span>
                        </a>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 items-start overflow-x-auto pb-6">
                    @foreach($columnsConfig as $colKey => $colMeta)
                        @php
                            $colLeads = $kanbanColumns[$colKey] ?? collect();
                            $colOverdueCount = $colLeads->filter(fn($l) => $l->isFollowUpOverdue())->count();
                            $colTodayCount = $colLeads->filter(fn($l) => $l->isFollowUpToday())->count();
                        @endphp
                        
                        <!-- Column (Clean Flat Stack, No Nested Card Box) -->
                        <div class="flex flex-col min-h-[500px] rounded-xl transition-colors p-1"
                             :class="dragOverColumn === '{{ $colKey }}' ? 'bg-emerald-50/40 dark:bg-emerald-950/20 ring-1 ring-emerald-500/40' : ''"
                             @dragover.prevent="dragOverColumn = '{{ $colKey }}'"
                             @dragleave.prevent="dragOverColumn = null"
                             @drop.prevent="
                                dragOverColumn = null;
                                if (draggedLeadId) {
                                    updateLeadStatus(draggedLeadId, '{{ $colKey }}');
                                    draggedLeadId = null;
                                }
                             ">
                            
                            <!-- Status Header at Top -->
                            <div class="flex items-center justify-between pb-3 px-1 mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-{{ $colMeta['color'] }}-500"></span>
                                    <h3 class="text-xs font-bold text-zinc-800 dark:text-zinc-200">{{ $colMeta['title'] }}</h3>
                                    @if($colOverdueCount > 0)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-medium bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300">
                                            {{ $colOverdueCount }} telat
                                        </span>
                                    @endif
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-medium {{ $colMeta['badge'] }}">
                                    {{ $colLeads->count() }}
                                </span>
                            </div>

                            <!-- Cards List Below Status -->
                            <div class="space-y-2.5 flex-1">
                                @forelse($colLeads as $kLead)
                                    @php
                                        $isOverdue = $kLead->isFollowUpOverdue();
                                        $isToday = $kLead->isFollowUpToday();
                                    @endphp
                                    <div class="bg-white dark:bg-zinc-950 p-3.5 rounded-lg border border-zinc-200/80 dark:border-zinc-800 shadow-xs hover:border-zinc-300 dark:hover:border-zinc-700 transition-all cursor-grab active:cursor-grabbing space-y-2 group"
                                         draggable="true"
                                         @dragstart="draggedLeadId = {{ $kLead->id }}">
                                        
                                        <!-- Header & Paket -->
                                        <div class="flex items-start justify-between gap-2">
                                            <a href="{{ route('admin.leads.show', $kLead) }}" class="font-semibold text-xs text-zinc-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                                {{ $kLead->nama_usaha }}
                                            </a>
                                            <span class="text-[10px] font-mono text-zinc-400 dark:text-zinc-500 whitespace-nowrap">#{{ $kLead->id }}</span>
                                        </div>

                                        @if($kLead->nama_kontak)
                                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ $kLead->nama_kontak }}</p>
                                        @endif

                                        <div class="text-[11px] font-medium text-zinc-700 dark:text-zinc-300 flex items-center justify-between">
                                            <span>{{ $kLead->paket_label }}</span>
                                            @if($kLead->nilai_nego && $kLead->nilai_nego > 0)
                                                <span class="font-mono text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-1.5 py-0.5 rounded border border-amber-200/60 dark:border-amber-800/40" title="Harga Kesepakatan Nego">
                                                    Nego: Rp {{ number_format($kLead->nilai_nego, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="font-mono text-[10px] text-zinc-400">Rp {{ number_format($kLead->getDefaultPackagePrice(), 0, ',', '.') }}</span>
                                            @endif
                                        </div>

                                        <!-- Overdue or Today Subtle Pill -->
                                        @if($isOverdue)
                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 border border-rose-200/60 dark:border-rose-900/40">
                                                <span class="material-symbols-outlined text-[12px]">alarm</span>
                                                <span>Telat: {{ $kLead->follow_up_date->format('d M') }}</span>
                                            </div>
                                        @elseif($isToday)
                                            <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/60 dark:border-amber-900/40">
                                                <span class="material-symbols-outlined text-[12px]">schedule</span>
                                                <span>Hari ini</span>
                                            </div>
                                        @endif

                                        <!-- Direct WA Chat & Follow up status -->
                                        <div class="pt-2 border-t border-zinc-100 dark:border-zinc-900 flex items-center justify-between gap-1 text-[11px]">
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $kLead->kontak_wa) }}" target="_blank"
                                               class="inline-flex items-center gap-1 font-mono text-emerald-600 dark:text-emerald-400 font-semibold hover:underline"
                                               title="Buka Chat WhatsApp Web">
                                                <span class="material-symbols-outlined text-[14px]">chat</span>
                                                <span>{{ $kLead->kontak_wa }}</span>
                                            </a>

                                            <!-- Quick Snooze Menu (Alpine) -->
                                            <div class="relative" x-data="{ openSnooze: false }">
                                                <button @click="openSnooze = !openSnooze" 
                                                        class="p-1 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200" 
                                                        title="Snooze / Atur Jadwal Follow-Up">
                                                    <span class="material-symbols-outlined text-[16px] block">schedule</span>
                                                </button>

                                                <div x-show="openSnooze" 
                                                     @click.away="openSnooze = false"
                                                     class="absolute right-0 bottom-full mb-1 w-36 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xl py-1 z-30 text-[11px]"
                                                     style="display: none;">
                                                    <div class="px-2.5 py-1 text-[9px] font-bold text-zinc-400 uppercase tracking-wider">Follow-Up:</div>
                                                    <button @click="quickSnooze({{ $kLead->id }}, 'today'); openSnooze = false" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                                                        <span>📅 Hari Ini</span>
                                                    </button>
                                                    <button @click="quickSnooze({{ $kLead->id }}, '1'); openSnooze = false" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                                                        <span>⏱️ +1 Hari</span>
                                                    </button>
                                                    <button @click="quickSnooze({{ $kLead->id }}, '3'); openSnooze = false" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                                                        <span>⏱️ +3 Hari</span>
                                                    </button>
                                                    <button @click="quickSnooze({{ $kLead->id }}, '7'); openSnooze = false" class="w-full text-left px-3 py-1.5 hover:bg-zinc-100 dark:hover:bg-zinc-800 flex items-center gap-1.5 text-zinc-700 dark:text-zinc-300">
                                                        <span>⏱️ +1 Minggu</span>
                                                    </button>
                                                    <button @click="quickSnooze({{ $kLead->id }}, 'clear'); openSnooze = false" class="w-full text-left px-3 py-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 flex items-center gap-1.5 border-t border-zinc-100 dark:border-zinc-800">
                                                        <span>✕ Hapus Jadwal</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        @if($kLead->follow_up_date)
                                            <div class="text-[10px] font-mono flex items-center gap-1 pt-1">
                                                <span class="text-zinc-400">FU:</span>
                                                <span class="font-bold {{ $isOverdue ? 'text-rose-600 dark:text-rose-400' : ($isToday ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-600 dark:text-zinc-400') }}">
                                                    {{ $kLead->follow_up_date->format('d M') }}
                                                </span>
                                                @if($isOverdue)
                                                    <span class="px-1 py-0.2 rounded text-[8px] bg-rose-500 text-white font-bold uppercase">Telat</span>
                                                @elseif($isToday)
                                                    <span class="px-1 py-0.2 rounded text-[8px] bg-amber-500 text-white font-bold uppercase">Hari Ini</span>
                                                @endif
                                            </div>
                                        @endif

                                    </div>
                                @empty
                                    <div class="p-4 text-center text-zinc-400 text-xs border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-xl">
                                        Kosong
                                    </div>
                                @endforelse
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

            <!-- ============================================================ -->
            <!-- TAMPILAN 2: TABLE LIST VIEW (Desktop Table + Mobile Cards) -->
            <!-- ============================================================ -->
            @if($viewMode === 'table')
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
                    <!-- Desktop Table (md:block) -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-zinc-50/70 dark:bg-zinc-950/50 border-b border-zinc-200/80 dark:border-zinc-800 text-[11px] font-bold font-mono uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                                <tr>
                                    <th class="px-6 py-4">Usaha &amp; Kontak</th>
                                    <th class="px-6 py-4">WhatsApp</th>
                                    <th class="px-6 py-4">Paket Diminati</th>
                                    <th class="px-6 py-4">Sumber</th>
                                    <th class="px-6 py-4">Status Lead</th>
                                    <th class="px-6 py-4">Jadwal Follow-up</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 text-xs">
                                @forelse($leads as $lead)
                                    @php
                                        $isOverdue = $lead->isFollowUpOverdue();
                                        $isToday = $lead->isFollowUpToday();
                                        $statusBadges = [
                                            'belum_dihubungi' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
                                            'sudah_chat' => 'bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400 border border-sky-200/50',
                                            'nego' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/50',
                                            'deal' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/50 font-bold',
                                            'tidak_lanjut' => 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500 line-through',
                                        ];
                                    @endphp
                                    <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-colors group">
                                        
                                        <!-- Usaha & Kontak -->
                                        <td class="px-6 py-4">
                                            <a href="{{ route('admin.leads.show', $lead) }}" class="font-bold text-zinc-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors text-sm">
                                                {{ $lead->nama_usaha }}
                                            </a>
                                            @if($lead->nama_kontak)
                                                <p class="text-zinc-500 dark:text-zinc-400 text-[11px] mt-0.5">{{ $lead->nama_kontak }}</p>
                                            @endif
                                        </td>

                                        <!-- WhatsApp Direct Link -->
                                        <td class="px-6 py-4">
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->kontak_wa) }}" target="_blank" 
                                               class="inline-flex items-center gap-1.5 font-mono text-emerald-600 dark:text-emerald-400 hover:underline font-semibold"
                                               title="Buka Chat WhatsApp">
                                                <span class="material-symbols-outlined text-[16px]">chat</span>
                                                <span>{{ $lead->kontak_wa }}</span>
                                            </a>
                                        </td>

                                        <!-- Paket Diminati -->
                                        <td class="px-6 py-4">
                                            <span class="font-semibold text-zinc-800 dark:text-zinc-200 block">{{ $lead->paket_label }}</span>
                                            @if($lead->nilai_nego && $lead->nilai_nego > 0)
                                                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-mono font-bold block">Nego: Rp {{ number_format($lead->nilai_nego, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-[10px] text-zinc-400 font-mono">Rp {{ number_format($lead->getDefaultPackagePrice(), 0, ',', '.') }}</span>
                                            @endif
                                        </td>

                                        <!-- Sumber -->
                                        <td class="px-6 py-4">
                                            <span class="px-2 py-0.5 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-[10px] font-semibold">
                                                {{ $lead->sumber_label }}
                                            </span>
                                        </td>

                                        <!-- Status Lead -->
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-1 rounded-lg text-[10px] uppercase font-bold tracking-wider {{ $statusBadges[$lead->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                                {{ $lead->status_label }}
                                            </span>
                                        </td>

                                        <!-- Follow-up Date & Quick Snooze -->
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                @if($lead->follow_up_date)
                                                    <div class="flex items-center gap-1.5 font-mono">
                                                        <span class="text-zinc-700 dark:text-zinc-300">{{ $lead->follow_up_date->format('d M Y') }}</span>
                                                        @if($isOverdue)
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-rose-500 text-white">Telat</span>
                                                        @elseif($isToday)
                                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-500 text-white">Hari Ini</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-zinc-400 dark:text-zinc-600 italic">-</span>
                                                @endif

                                                <!-- Quick Snooze Dropdown -->
                                                <div class="relative" x-data="{ openSnooze: false }">
                                                    <button @click="openSnooze = !openSnooze" class="p-1 rounded text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800" title="Atur Follow Up">
                                                        <span class="material-symbols-outlined text-[15px] block">schedule</span>
                                                    </button>
                                                    <div x-show="openSnooze" @click.away="openSnooze = false" class="absolute left-0 top-full mt-1 w-32 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-xl py-1 z-30 text-[11px]" style="display: none;">
                                                        <button @click="quickSnooze({{ $lead->id }}, 'today'); openSnooze = false" class="w-full text-left px-2.5 py-1 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">📅 Hari Ini</button>
                                                        <button @click="quickSnooze({{ $lead->id }}, '1'); openSnooze = false" class="w-full text-left px-2.5 py-1 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">⏱️ +1 Hari</button>
                                                        <button @click="quickSnooze({{ $lead->id }}, '3'); openSnooze = false" class="w-full text-left px-2.5 py-1 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300">⏱️ +3 Hari</button>
                                                        <button @click="quickSnooze({{ $lead->id }}, 'clear'); openSnooze = false" class="w-full text-left px-2.5 py-1 hover:bg-rose-50 text-rose-600 border-t border-zinc-100 dark:border-zinc-800">✕ Hapus</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Actions -->
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-1.5">
                                                @if($lead->status !== 'deal')
                                                    <form method="POST" action="{{ route('admin.leads.convert-deal', $lead) }}" x-ref="convertDealDesktop{{ $lead->id }}">
                                                        @csrf
                                                        <button type="button" @click="VhSwal.confirm('Konfirmasi: Tandai Deal untuk {{ $lead->nama_usaha }} dan buat Project baru?', () => $refs['convertDealDesktop{{ $lead->id }}'].submit())" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-colors flex items-center gap-1 shadow-sm" title="Konversi ke Proyek Deal">
                                                            <span class="material-symbols-outlined text-[14px]">handshake</span>
                                                            <span>Tandai Deal</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                <a href="{{ route('admin.leads.show', $lead) }}" class="p-1.5 rounded-lg text-zinc-500 hover:text-emerald-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" title="Buka Detail & Chat">
                                                    <span class="material-symbols-outlined text-[18px]">chat</span>
                                                </a>

                                                <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" x-ref="deleteLeadDesktop{{ $lead->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" @click="VhSwal.confirmDelete('Yakin ingin menghapus lead ini?', $refs['deleteLeadDesktop{{ $lead->id }}'])" class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors" title="Hapus Lead">
                                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-zinc-400 dark:text-zinc-500">
                                            <span class="material-symbols-outlined text-[36px] block mb-1">person_search</span>
                                            <p class="text-xs">Tidak ada data lead yang cocok dengan filter pencarian.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards (md:hidden) -->
                    <div class="md:hidden divide-y divide-zinc-100 dark:divide-zinc-800/60">
                        @forelse($leads as $lead)
                            @php
                                $isOverdue = $lead->isFollowUpOverdue();
                                $isToday = $lead->isFollowUpToday();
                                $statusBadges = [
                                    'belum_dihubungi' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
                                    'sudah_chat' => 'bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400 border border-sky-200/50',
                                    'nego' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/50',
                                    'deal' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/50 font-bold',
                                    'tidak_lanjut' => 'bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500 line-through',
                                ];
                            @endphp
                            <div class="p-4 space-y-3">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <a href="{{ route('admin.leads.show', $lead) }}" class="font-bold text-xs text-zinc-900 dark:text-white hover:text-emerald-600">
                                            {{ $lead->nama_usaha }}
                                        </a>
                                        @if($lead->nama_kontak)
                                            <p class="text-[11px] text-zinc-500 mt-0.5">{{ $lead->nama_kontak }}</p>
                                        @endif
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider {{ $statusBadges[$lead->status] ?? 'bg-zinc-100 text-zinc-600' }}">
                                        {{ $lead->status_label }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between text-xs text-zinc-600 dark:text-zinc-400">
                                    <span>{{ $lead->paket_label }}</span>
                                    @if($lead->nilai_nego && $lead->nilai_nego > 0)
                                        <span class="font-mono text-amber-600 dark:text-amber-400 font-bold">Nego: Rp {{ number_format($lead->nilai_nego, 0, ',', '.') }}</span>
                                    @else
                                        <span class="font-mono text-zinc-500">Rp {{ number_format($lead->getDefaultPackagePrice(), 0, ',', '.') }}</span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800/80 text-xs">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->kontak_wa) }}" target="_blank"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-[11px]">
                                        <span class="material-symbols-outlined text-[14px]">chat</span>
                                        <span>Chat WA</span>
                                    </a>

                                    <div class="flex items-center gap-1.5">
                                        @if($lead->status !== 'deal')
                                            <form method="POST" action="{{ route('admin.leads.convert-deal', $lead) }}" x-ref="convertDealMobile{{ $lead->id }}">
                                                @csrf
                                                <button type="button" @click="VhSwal.confirm('Tandai Deal untuk {{ $lead->nama_usaha }}?', () => $refs['convertDealMobile{{ $lead->id }}'].submit())" class="px-2.5 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-[11px] font-bold">
                                                    Deal
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.leads.show', $lead) }}" class="px-2.5 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300 text-[11px] font-bold">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-zinc-400 text-xs">
                                Tidak ada data lead yang cocok.
                            </div>
                        @endforelse
                    </div>

                    @if($leads->hasPages())
                        <div class="p-4 border-t border-zinc-200/80 dark:border-zinc-800">
                            {{ $leads->links() }}
                        </div>
                    @endif
                </div>
            @endif

        </div>

        <!-- ============================================================ -->
        <!-- MODAL TAMBAH LEAD BARU -->
        <!-- ============================================================ -->
        <div x-show="openCreateModal" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-sm"
             style="display: none;">
            
            <div @click.away="openCreateModal = false" class="bg-white dark:bg-zinc-900 w-full max-w-lg rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-[20px]">person_add</span>
                        </div>
                        <h3 class="font-extrabold text-zinc-900 dark:text-white text-lg">Tambah Lead / Prospek Baru</h3>
                    </div>
                    <button @click="openCreateModal = false" class="p-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-white rounded-lg">
                        <span class="material-symbols-outlined text-[20px] block">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.leads.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Nama Usaha / Bisnis UMKM <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_usaha" required placeholder="Contoh: Kopi Kenangan Senja, Bengkel Berkah"
                               class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Nama Kontak / Owner</label>
                            <input type="text" name="nama_kontak" placeholder="Contoh: Budi Santoso"
                                   class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Nomor WhatsApp <span class="text-rose-500">*</span></label>
                            <input type="text" name="kontak_wa" required placeholder="081234567890"
                                   class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Sumber Lead <span class="text-rose-500">*</span></label>
                            <select name="sumber" required class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-700 dark:text-zinc-300 focus:ring-emerald-500">
                                <option value="warm_network">Warm Network / Teman</option>
                                <option value="cold_outreach">Cold Outreach</option>
                                <option value="komunitas">Komunitas Bisnis</option>
                                <option value="marketplace">Marketplace / Iklan</option>
                                <option value="referral">Referral Klien</option>
                                <option value="website">Website / Form Organik</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Status Awal <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-700 dark:text-zinc-300 focus:ring-emerald-500">
                                <option value="belum_dihubungi">Belum Dihubungi</option>
                                <option value="sudah_chat">Sudah Chat Manual</option>
                                <option value="nego">Nego / Tertarik</option>
                                <option value="deal">Langsung Deal (Buat Project)</option>
                                <option value="tidak_lanjut">Tidak Lanjut</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Paket Diminati <span class="text-rose-500">*</span></label>
                            <select name="paket_diminati" required class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-700 dark:text-zinc-300 focus:ring-emerald-500">
                                <option value="landing_page">Landing Page (Rp 499.000)</option>
                                <option value="company_profile">Company Profile (Rp 999.000)</option>
                                <option value="toko_kasir">Toko &amp; Kasir POS (Rp 1.500.000)</option>
                                <option value="custom">Custom Web App</option>
                                <option value="belum_tahu">Belum Tahu / Konsultasi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Estimasi / Nilai Nego (Rp)</label>
                            <input type="number" name="nilai_nego" placeholder="Opsional (misal: 750000)" min="0"
                                   class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            <span class="text-[10px] text-zinc-400">Kosongkan jika mengikuti harga standar paket</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Email Klien (Opsional)</label>
                            <input type="email" name="email" placeholder="Contoh: klien@bisnis.com"
                                   class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Jadwal Follow-Up</label>
                            <input type="date" name="follow_up_date" 
                                   class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Catatan Kebutuhan Prospek</label>
                        <textarea name="catatan" rows="3" placeholder="Contoh: Butuh website menu resto + fitur cetak struk Bluetooth kasir."
                                  class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                    </div>

                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-end gap-3">
                        <button type="button" @click="openCreateModal = false" class="px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-700 text-xs font-semibold text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-colors">
                            Simpan Lead
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

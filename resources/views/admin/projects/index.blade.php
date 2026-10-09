<x-app-layout>
    <div class="space-y-6 w-full"
         x-data="{
            openStatusModal: false,
            activeProject: {},
            newStatus: '',
            dpAmount: '',
            linkWebsite: '',
            createMaintenance: false,
            sendWa: true
         }">

            <!-- Flash Alert -->
            <x-flash />

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Proyek Klien</h1>
                    <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Kelola progres pengerjaan website, pencatatan DP, serah terima, dan automasi WhatsApp.</p>
                </div>
                <div class="relative" x-data="{ openExport: false }">
                    <button @click="openExport = !openExport" @click.outside="openExport = false" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-all shadow-xs">
                        <span class="material-symbols-outlined text-[16px] text-sky-600">download</span>
                        <span>Export Proyek</span>
                        <span class="material-symbols-outlined text-[14px] text-zinc-400">expand_more</span>
                    </button>
                    <div x-show="openExport" x-cloak 
                         class="absolute right-0 mt-1.5 w-44 bg-white dark:bg-zinc-900 rounded-lg shadow-lg border border-zinc-200 dark:border-zinc-800 py-1 z-50 text-xs font-medium">
                        <a href="{{ route('admin.export.projects', array_merge(request()->query(), ['format' => 'csv'])) }}" 
                           class="flex items-center gap-2 px-3 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                            <span class="material-symbols-outlined text-[16px] text-emerald-600">table_chart</span>
                            <span>Export CSV / Excel</span>
                        </a>
                        <a href="{{ route('admin.export.projects', array_merge(request()->query(), ['format' => 'pdf'])) }}" target="_blank"
                           class="flex items-center gap-2 px-3 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                            <span class="material-symbols-outlined text-[16px] text-rose-600">picture_as_pdf</span>
                            <span>Export PDF</span>
                        </a>
                        <a href="{{ route('admin.export.projects', array_merge(request()->query(), ['format' => 'word'])) }}" 
                           class="flex items-center gap-2 px-3 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                            <span class="material-symbols-outlined text-[16px] text-blue-600">description</span>
                            <span>Export Word (.doc)</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Filter Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar pb-1 text-xs font-bold">
                <a href="{{ route('admin.projects.index') }}" 
                   class="px-3.5 py-2 rounded-xl border transition-all whitespace-nowrap {{ !request('status') ? 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 border-transparent shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800' }}">
                    Semua ({{ $statusCounts['all'] }})
                </a>
                <a href="{{ route('admin.projects.index', ['status' => 'draft']) }}" 
                   class="px-3.5 py-2 rounded-xl border transition-all whitespace-nowrap {{ request('status') === 'draft' ? 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 border-transparent shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800' }}">
                    Draft ({{ $statusCounts['draft'] }})
                </a>
                <a href="{{ route('admin.projects.index', ['status' => 'dp_diterima']) }}" 
                   class="px-3.5 py-2 rounded-xl border transition-all whitespace-nowrap {{ request('status') === 'dp_diterima' ? 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 border-transparent shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800' }}">
                    DP Diterima ({{ $statusCounts['dp_diterima'] }})
                </a>
                <a href="{{ route('admin.projects.index', ['status' => 'dikerjakan']) }}" 
                   class="px-3.5 py-2 rounded-xl border transition-all whitespace-nowrap {{ request('status') === 'dikerjakan' ? 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 border-transparent shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800' }}">
                    Sedang Dikerjakan ({{ $statusCounts['dikerjakan'] }})
                </a>
                <a href="{{ route('admin.projects.index', ['status' => 'review']) }}" 
                   class="px-3.5 py-2 rounded-xl border transition-all whitespace-nowrap {{ request('status') === 'review' ? 'bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 border-transparent shadow-sm' : 'bg-white dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-800' }}">
                    Review Klien ({{ $statusCounts['review'] }})
                </a>
                <a href="{{ route('admin.projects.index', ['status' => 'selesai']) }}" 
                   class="px-3.5 py-2 rounded-xl border transition-all whitespace-nowrap {{ request('status') === 'selesai' ? 'bg-emerald-600 text-white border-transparent shadow-sm' : 'bg-white dark:bg-zinc-900 text-emerald-600 dark:text-emerald-400 border-zinc-200 dark:border-zinc-800' }}">
                    Selesai &amp; Live ({{ $statusCounts['selesai'] }})
                </a>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.projects.index') }}" class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex items-center gap-3">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-[20px]">search</span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama project, nama klien, atau nomor WhatsApp..." 
                           class="w-full pl-10 pr-4 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                </div>
            </form>

            <!-- Projects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($projects as $project)
                    @php
                        $paidPercent = $project->harga > 0 ? min(100, round(($project->total_paid / $project->harga) * 100)) : 0;
                        $statusClasses = [
                            'draft' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                            'dp_diterima' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-400 border border-sky-200/60',
                            'dikerjakan' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border border-amber-200/60 font-bold',
                            'review' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-400 border border-purple-200/60',
                            'selesai' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/60 font-bold',
                            'dibatalkan' => 'bg-zinc-100 text-zinc-400 line-through',
                        ];
                    @endphp
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition-all group">
                        <div class="space-y-4">
                            
                            <!-- Top Badge Row -->
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider {{ $statusClasses[$project->status] ?? 'bg-zinc-100' }}">
                                    {{ $project->status_label }}
                                </span>
                                <span class="text-xs font-mono font-bold text-zinc-900 dark:text-white">
                                    Rp {{ number_format($project->harga, 0, ',', '.') }}
                                </span>
                            </div>

                            <!-- Project Title & Client -->
                            <div>
                                <a href="{{ route('admin.projects.show', $project) }}" class="font-extrabold text-base text-zinc-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors line-clamp-1">
                                    {{ $project->nama_project }}
                                </a>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">storefront</span>
                                    <span>Klien: <b>{{ $project->lead->nama_usaha }}</b></span>
                                </p>
                            </div>

                            <!-- Package Badge -->
                            <div class="flex items-center gap-2 text-xs">
                                <span class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 text-[11px] font-medium">
                                    {{ $project->paket_label }}
                                </span>
                                @if($project->link_website)
                                    <a href="{{ $project->link_website }}" target="_blank" class="text-[11px] font-mono text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-0.5 truncate max-w-[150px]">
                                        <span class="material-symbols-outlined text-[12px]">link</span>
                                        <span>{{ parse_url($project->link_website, PHP_URL_HOST) ?: $project->link_website }}</span>
                                    </a>
                                @endif
                            </div>

                            <!-- Payment Progress Bar -->
                            <div class="space-y-1.5 pt-2 border-t border-zinc-100 dark:border-zinc-800/80">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-zinc-400">Pembayaran</span>
                                    <span class="font-bold text-zinc-800 dark:text-zinc-200">
                                        Rp {{ number_format($project->total_paid, 0, ',', '.') }} <span class="font-normal text-zinc-400">({{ $paidPercent }}%)</span>
                                    </span>
                                </div>
                                <div class="w-full bg-zinc-100 dark:bg-zinc-800 h-2 rounded-full overflow-hidden">
                                    <div class="h-full bg-emerald-500 transition-all duration-300" style="width: {{ $paidPercent }}%"></div>
                                </div>
                            </div>

                        </div>

                        <!-- Action Buttons Row -->
                        <div class="pt-5 mt-5 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between gap-2">
                            <button @click="
                                activeProject = {{ json_encode($project) }};
                                newStatus = '{{ $project->status }}';
                                dpAmount = '{{ $project->harga / 2 }}';
                                linkWebsite = '{{ $project->link_website ?? '' }}';
                                openStatusModal = true;
                            " class="flex-1 px-3 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-xs font-bold hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/50 dark:hover:text-emerald-400 transition-colors flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">sync_alt</span>
                                <span>Ubah Status</span>
                            </button>

                            <a href="{{ route('admin.projects.show', $project) }}" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors">
                                Detail
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 text-center text-zinc-400 dark:text-zinc-500 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800">
                        <span class="material-symbols-outlined text-[48px] text-zinc-300 dark:text-zinc-700 block mb-2">inventory_2</span>
                        <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300">Belum ada proyek website.</p>
                        <p class="text-xs mt-1">Proyek akan dibuat otomatis saat menandai Deal pada halaman Leads.</p>
                    </div>
                @endforelse
            </div>

            @if($projects->hasPages())
                <div class="mt-6">
                    {{ $projects->links() }}
                </div>
            @endif

        </div>

        <!-- ============================================================ -->
        <!-- MODAL UBAH STATUS PROYEK & TRIGGER WHATSAPP -->
        <!-- ============================================================ -->
        <div x-show="openStatusModal" 
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="openStatusModal = false" class="bg-white dark:bg-zinc-900 w-full max-w-lg rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <div>
                        <h3 class="font-extrabold text-zinc-900 dark:text-white text-base">Ubah Status Proyek</h3>
                        <p class="text-xs text-zinc-400 mt-0.5" x-text="activeProject.nama_project"></p>
                    </div>
                    <button @click="openStatusModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form method="POST" :action="'/admin/projects/' + activeProject.id + '/status'" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Pilih Status Baru <span class="text-rose-500">*</span></label>
                        <select name="status" x-model="newStatus" required class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-800 dark:text-zinc-200 font-bold focus:ring-emerald-500">
                            <option value="draft">Draft / Baru</option>
                            <option value="dp_diterima">DP Diterima (Picu WA Konfirmasi DP)</option>
                            <option value="dikerjakan">Sedang Dikerjakan (Picu WA Progres Dimulai)</option>
                            <option value="review">Review Klien (Picu WA Pratinjau &amp; Feedback)</option>
                            <option value="selesai">Selesai &amp; Live (Picu WA Website Live)</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>

                    <!-- Additional inputs for DP Diterima -->
                    <div x-show="newStatus === 'dp_diterima'" class="p-4 rounded-xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200/60 dark:border-sky-800/40 space-y-3">
                        <p class="text-xs font-bold text-sky-800 dark:text-sky-300 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                            <span>Pencatatan Pembayaran Uang Muka (DP)</span>
                        </p>
                        <div>
                            <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1">Nominal DP Diterima (Rp)</label>
                            <input type="number" name="dp_amount" x-model="dpAmount"
                                   class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg text-xs">
                        </div>
                    </div>

                    <!-- Additional inputs for Selesai or Review -->
                    <div x-show="newStatus === 'selesai' || newStatus === 'review'" class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/40 space-y-3">
                        <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">public</span>
                            <span x-text="newStatus === 'selesai' ? 'Informasi Website Live' : 'Link Pratinjau / Preview Website'"></span>
                        </p>
                        <div>
                            <label class="block text-[11px] font-semibold text-zinc-600 dark:text-zinc-400 mb-1" x-text="newStatus === 'selesai' ? 'Link URL Website Live' : 'Link URL Website Pratinjau'"></label>
                            <input type="url" name="link_website" x-model="linkWebsite" placeholder="https://domainklien.com"
                                   class="w-full px-3 py-2 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg text-xs">
                        </div>

                        <label x-show="newStatus === 'selesai'" class="flex items-center gap-2 cursor-pointer pt-1">
                            <input type="checkbox" name="create_maintenance" value="1" x-model="createMaintenance" class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs text-zinc-700 dark:text-zinc-300 font-semibold">Daftarkan langganan maintenance bulanan (Hanya jika klien deal/minta maintenance)</span>
                        </label>
                    </div>

                    <!-- WhatsApp Auto-Send Toggle -->
                    <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">chat</span>
                            <div>
                                <p class="text-xs font-bold text-zinc-900 dark:text-white">Kirim WhatsApp Otomatis</p>
                                <p class="text-[10px] text-zinc-400">Pesan transactional sesuai template VexaHost WA</p>
                            </div>
                        </div>
                        <input type="checkbox" name="send_wa" value="1" x-model="sendWa" class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4">
                    </div>

                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button type="button" @click="openStatusModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold border text-zinc-600">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm">Simpan Status</button>
                    </div>
                </form>
            </div>
    </div>
</x-app-layout>

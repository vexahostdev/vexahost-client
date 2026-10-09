<x-app-layout>
    <div class="py-6 sm:py-8 bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 min-h-screen transition-colors duration-300"
         x-data="{
            openEditModal: false,
            openPaymentModal: false,
            openStatusModal: false,
            editHarga: {{ (int)$project->harga }},
            totalPaid: {{ (int)$project->total_paid }},
            newStatus: '{{ $project->status }}',
            dpAmount: '{{ $project->harga / 2 }}',
            linkWebsite: '{{ $project->link_website ?? '' }}',
            createMaintenance: false,
            sendWa: true
         }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            <x-flash />

            <!-- Breadcrumb Navigation -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-2 text-xs font-semibold text-zinc-400">
                    <a href="{{ route('admin.projects.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        <span>Daftar Proyek</span>
                    </a>
                    <span>/</span>
                    <span class="text-zinc-800 dark:text-zinc-200 font-bold truncate">{{ $project->nama_project }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    @if($project->client_id)
                        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-xs font-bold transition-all shadow-sm hover:border-blue-500">
                            <span class="material-symbols-outlined text-[18px] text-blue-600">view_kanban</span>
                            <span>Papan Kanban Klien</span>
                        </a>
                        <form action="{{ route('admin.projects.provision-client', $project) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="send_wa" value="0">
                            <button type="submit" title="Perbarui akun klien, tugas Kanban & invoice" class="inline-flex items-center gap-1 px-3 py-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-300 text-xs font-bold hover:border-blue-500 transition-all shadow-sm">
                                <span class="material-symbols-outlined text-[16px]">sync</span>
                                <span>Segarkan Data Klien</span>
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.projects.provision-client', $project) }}" method="POST" class="inline">
                            @csrf
                            <button type="button" @click="VhSwal.confirm('Buat akun klien untuk proyek ini dan kirim info login via WhatsApp?', () => $el.closest('form').submit())" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow active:scale-95">
                                <span class="material-symbols-outlined text-[18px]">person_add</span>
                                <span>Buat Akun Klien</span>
                            </button>
                        </form>
                    @endif

                    @php
                        $activeInvoice = $project->latestInvoice ?: $project->invoices()->first();
                        $directPayUrl = $activeInvoice?->payment_token ? route('invoices.pay', $activeInvoice->payment_token) : null;
                    @endphp
                    @if($directPayUrl)
                        <a href="{{ $directPayUrl }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow active:scale-95" title="Buka halaman pembayaran langsung (Tanpa Login / Guest Checkout)">
                            <span class="material-symbols-outlined text-[18px]">bolt</span>
                            <span>Halaman Bayar (Tanpa Login)</span>
                        </a>
                    @endif
                    <a href="{{ route('admin.invoices.project', $project) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-800 dark:text-zinc-200 hover:border-emerald-500 text-xs font-bold transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600">receipt_long</span>
                        <span>Lihat &amp; Cetak Invoice</span>
                    </a>
                    <form action="{{ route('admin.invoices.project.send-wa', $project) }}" method="POST" class="inline">
                        @csrf
                        <button type="button" @click="VhSwal.confirm('Kirim dokumen PDF Invoice resmi ke WhatsApp klien ({{ $project->lead?->kontak_wa }}) via Gateway?', () => $el.closest('form').submit())" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold transition-all shadow-sm hover:shadow active:scale-95" title="Kirim dokumen PDF Invoice langsung ke WhatsApp klien">
                            <span class="material-symbols-outlined text-[18px]">send</span>
                            <span>Kirim Invoice WA</span>
                        </button>
                    </form>
                    @if($project->remaining_balance > 0)
                        <a href="{{ route('admin.invoices.settlement', $project) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-300 hover:border-amber-500 text-xs font-bold transition-all shadow-sm" title="Lihat &amp; cetak dokumen resmi Invoice Pelunasan">
                            <span class="material-symbols-outlined text-[18px] text-amber-600">description</span>
                            <span>Lihat Dokumen Pelunasan</span>
                        </a>
                        <form action="{{ route('admin.invoices.settlement.send-wa', $project) }}" method="POST" class="inline">
                            @csrf
                            <button type="button" @click="VhSwal.confirm('Kirim dokumen PDF resmi Invoice Pelunasan (Sisa: Rp {{ number_format($project->remaining_balance, 0, ',', '.') }}) langsung ke WhatsApp klien ({{ $project->lead?->kontak_wa }}) via Gateway?', () => $el.closest('form').submit())" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl {{ $project->status === 'review' ? 'bg-amber-600 hover:bg-amber-700 ring-2 ring-amber-400' : 'bg-amber-600 hover:bg-amber-700' }} text-white text-xs font-bold transition-all shadow-sm hover:shadow active:scale-95" title="Kirim dokumen PDF Tagihan Pelunasan langsung ke WhatsApp klien">
                                <span class="material-symbols-outlined text-[18px]">send</span>
                                <span>Kirim Pelunasan (PDF) WA</span>
                            </button>
                        </form>
                    @endif
                    <button @click="openEditModal = true" class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-800 dark:text-zinc-200 hover:border-emerald-500 text-xs font-bold transition-all shadow-sm hover:shadow active:scale-95" title="Ubah informasi proyek, paket, dan nilai kesepakatan harga">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600">edit_square</span>
                        <span>Edit Proyek &amp; Harga</span>
                    </button>
                    <button @click="openStatusModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 text-xs font-bold transition-all shadow-sm hover:shadow active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">sync_alt</span>
                        <span>Ubah Status Proyek</span>
                    </button>
                    <button @click="openPaymentModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm hover:shadow active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">add_card</span>
                        <span>Catat Pembayaran</span>
                    </button>
                </div>
            </div>

            <!-- Project Details Card -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
                                {{ $project->status_label }}
                            </span>
                            @if($project->client_id)
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/40 inline-flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">cloud_done</span>
                                    <span>Akun Klien Aktif</span>
                                </span>
                            @endif
                            <span class="text-xs text-zinc-400 font-mono">ID: #PRJ-{{ str_pad($project->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white mt-2 tracking-tight">
                            {{ $project->nama_project }}
                        </h1>
                        <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                            Klien: <a href="{{ route('admin.leads.show', $project->lead) }}" class="font-bold text-emerald-600 hover:underline">{{ $project->lead->nama_usaha }}</a>
                            @if($project->lead->nama_kontak)
                                ({{ $project->lead->nama_kontak }})
                            @endif
                        </p>
                    </div>

                    <!-- Financial Summary Pill -->
                    <div class="grid grid-cols-3 gap-4 bg-zinc-50 dark:bg-zinc-950 p-4 rounded-2xl border border-zinc-100 dark:border-zinc-800">
                        <div>
                            <p class="text-[10px] font-mono uppercase text-zinc-400 font-bold">Total Nilai</p>
                            <p class="text-base sm:text-lg font-black text-zinc-900 dark:text-white mt-0.5">Rp {{ number_format($project->harga, 0, ',', '.') }}</p>
                        </div>
                        <div class="border-l border-zinc-200 dark:border-zinc-800 pl-4">
                            <p class="text-[10px] font-mono uppercase text-emerald-600 dark:text-emerald-400 font-bold">Terbayar</p>
                            <p class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400 mt-0.5">Rp {{ number_format($project->total_paid, 0, ',', '.') }}</p>
                        </div>
                        <div class="border-l border-zinc-200 dark:border-zinc-800 pl-4">
                            <p class="text-[10px] font-mono uppercase text-rose-500 font-bold">Sisa Tagihan</p>
                            <p class="text-base sm:text-lg font-black text-rose-500 mt-0.5">Rp {{ number_format($project->remaining_balance, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                @if($directPayUrl)
                    <div class="mt-6 pt-5 border-t border-zinc-100 dark:border-zinc-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-orange-50/70 dark:bg-orange-950/30 p-3.5 rounded-xl border border-orange-200/80 dark:border-orange-800/50">
                        <div class="flex items-center gap-2.5 text-xs text-zinc-700 dark:text-zinc-300">
                            <span class="material-symbols-outlined text-orange-600 text-[20px]">bolt</span>
                            <div>
                                <span class="font-bold text-zinc-900 dark:text-white">Link Pembayaran Langsung Klien (Tanpa Perlu Login):</span>
                                <span class="text-zinc-500 dark:text-zinc-400 block sm:inline sm:ml-1">Mendukung QRIS, Mandiri VA &amp; BNI VA (Otomatis 24 Jam)</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly value="{{ $directPayUrl }}" class="w-full sm:w-72 text-xs font-mono bg-white dark:bg-zinc-900 border border-orange-200 dark:border-zinc-700 rounded-lg px-2.5 py-1.5 text-zinc-800 dark:text-zinc-200 select-all focus:outline-none">
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $directPayUrl }}'); VhSwal.toast('Link pembayaran langsung berhasil disalin!', 'success')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-white dark:bg-zinc-800 border border-orange-300 dark:border-zinc-700 text-orange-700 dark:text-orange-300 text-xs font-bold hover:bg-orange-100 transition-all cursor-pointer shrink-0">
                                <span class="material-symbols-outlined text-[15px]">content_copy</span>
                                <span>Salin</span>
                            </button>
                            <a href="{{ $directPayUrl }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold transition-all shrink-0">
                                <span>Buka Link</span>
                                <span class="material-symbols-outlined text-[15px]">open_in_new</span>
                            </a>
                        </div>
                    </div>
                @endif

                @if($project->link_website)
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 bg-zinc-50/80 dark:bg-zinc-800/40 p-3.5 rounded-xl border border-zinc-200/60 dark:border-zinc-700/50">
                        <div class="flex items-center gap-2.5 text-xs text-zinc-700 dark:text-zinc-300">
                            <span class="material-symbols-outlined text-emerald-600 text-[20px]">language</span>
                            <div>
                                <span class="font-bold text-zinc-900 dark:text-white">Website Klien:</span>
                                <span class="text-zinc-500 dark:text-zinc-400">Tautan publik / pratinjau sistem</span>
                            </div>
                        </div>
                        <a href="{{ $project->link_website }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-300 dark:border-zinc-700 text-emerald-600 dark:text-emerald-400 text-xs font-mono font-bold hover:bg-emerald-50 dark:hover:bg-zinc-800 transition-all shadow-2xs">
                            <span>{{ $project->link_website }}</span>
                            <span class="material-symbols-outlined text-[15px]">open_in_new</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- Two Columns: Payments Table & Client WhatsApp Chat History -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                <!-- Payments History Box -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="font-bold text-zinc-900 dark:text-white text-base flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">receipt</span>
                            <span>Riwayat Pembayaran</span>
                        </h3>
                        <button @click="openPaymentModal = true" class="text-xs font-bold text-emerald-600 hover:underline">
                            + Tambah
                        </button>
                    </div>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse($project->payments as $p)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs text-zinc-900 dark:text-white">{{ $p->jenis_label }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase {{ $p->status === 'lunas' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ strtoupper($p->status) }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-zinc-400 mt-0.5">{{ $p->tanggal ? $p->tanggal->format('d M Y') : '-' }} • {{ $p->catatan ?: 'Tanpa catatan' }}</p>
                                </div>
                                <div class="flex items-center gap-3 text-right">
                                    <span class="font-mono font-bold text-xs text-zinc-900 dark:text-white">
                                        Rp {{ number_format($p->jumlah, 0, ',', '.') }}
                                    </span>
                                    <a href="{{ route('admin.invoices.receipt', $p) }}" target="_blank" class="p-1.5 rounded-lg text-zinc-400 hover:text-emerald-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" title="Lihat & Cetak Kwitansi Resmi">
                                        <span class="material-symbols-outlined text-[18px]">receipt</span>
                                    </a>
                                    <form action="{{ route('admin.invoices.receipt.send-wa', $p) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="button" @click="VhSwal.confirm('Kirim dokumen PDF Kwitansi ini langsung ke nomor WhatsApp {{ $project->lead?->kontak_wa }} via VexaHost WA Gateway?', () => $el.closest('form').submit())" class="p-1.5 rounded-lg text-zinc-400 hover:text-emerald-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" title="Kirim Kwitansi PDF via WhatsApp">
                                            <span class="material-symbols-outlined text-[18px]">send</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-zinc-400 text-xs">
                                Belum ada riwayat pembayaran tercatat untuk proyek ini.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Client Chat Quick Box -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="font-bold text-zinc-900 dark:text-white text-base flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">chat</span>
                            <span>Riwayat Chat Klien</span>
                        </h3>
                        <a href="{{ route('admin.leads.show', $project->lead) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                            Buka Chat Lengkap
                        </a>
                    </div>

                    <div class="space-y-3 max-h-[300px] overflow-y-auto custom-scrollbar p-2">
                        @forelse($project->lead->messageLogs->take(4) as $msg)
                            <div class="p-3 rounded-xl {{ $msg->arah === 'keluar' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-900 dark:text-emerald-200' : 'bg-zinc-50 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200' }} text-xs">
                                <div class="flex items-center justify-between font-mono text-[10px] text-zinc-400 mb-1">
                                    <span>{{ $msg->arah === 'keluar' ? 'VexaHost DIGITAL' : $project->lead->nama_usaha }}</span>
                                    <span>{{ $msg->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="line-clamp-3 leading-relaxed">{{ $msg->isi_pesan }}</p>
                            </div>
                        @empty
                            <div class="py-8 text-center text-zinc-400 text-xs">
                                Belum ada log pesan WhatsApp untuk klien ini.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Subscription / Masa Berlaku Section -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base flex items-center gap-2">
                        <span class="material-symbols-outlined text-amber-500">license</span>
                        <span>Masa Berlaku / Lisensi</span>
                    </h3>
                    <a href="{{ route('admin.subscriptions.index') }}?q={{ urlencode($project->nama_project) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                        Kelola
                    </a>
                </div>

                @if($project->subscriptions->isNotEmpty())
                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($project->subscriptions as $sub)
                            @php
                                $colorMap = ['green' => 'emerald', 'yellow' => 'amber', 'red' => 'rose', 'gray' => 'zinc'];
                                $c = $colorMap[$sub->status_color] ?? 'zinc';
                            @endphp
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs text-zinc-900 dark:text-white">{{ $sub->tipe_label }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase bg-{{ $c }}-50 dark:bg-{{ $c }}-950/40 text-{{ $c }}-700 dark:text-{{ $c }}-400 border border-{{ $c }}-200/50 dark:border-{{ $c }}-900/40">
                                            {{ $sub->status_label }}
                                        </span>
                                        @if($sub->sisa_hari > 0 && $sub->sisa_hari <= 30 && !$sub->isExpired())
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-500 text-white">{{ $sub->sisa_hari }} Hari</span>
                                        @elseif($sub->isExpired())
                                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-rose-500 text-white">Expired</span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-zinc-400 mt-0.5">
                                        Mulai: {{ $sub->tanggal_mulai->format('d M Y') }} • Expired: <span class="font-semibold {{ $sub->isExpired() ? 'text-rose-500' : ($sub->sisa_hari <= 30 ? 'text-amber-600' : 'text-zinc-700 dark:text-zinc-300') }}">{{ $sub->tanggal_expired->format('d M Y') }}</span>
                                        • Rp {{ number_format($sub->harga, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <form method="POST" action="{{ route('admin.subscriptions.reminder', $sub) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 transition-colors" title="Kirim Reminder WA">
                                            <span class="material-symbols-outlined text-[16px]">chat</span>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.subscriptions.renew', $sub) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="send_wa" value="1">
                                        <button type="button" @click="VhSwal.confirm('Perpanjang subscription ini dengan durasi yang sama?', () => $el.closest('form').submit())" class="px-2.5 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 text-[11px] font-bold hover:bg-indigo-100 transition-colors" title="Perpanjang">
                                            <span class="material-symbols-outlined text-[14px] align-middle">autorenew</span> Perpanjang
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-6 text-center text-zinc-400 text-xs">
                        <span class="material-symbols-outlined text-[28px] text-zinc-300 dark:text-zinc-600 block mb-1">license</span>
                        Belum ada subscription / masa berlaku untuk proyek ini.
                        <div class="mt-3">
                            <a href="{{ route('admin.subscriptions.index') }}?create=1&project_id={{ $project->id }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-all">
                                <span class="material-symbols-outlined text-[14px]">add</span> Tambah Subscription
                            </a>
                        </div>
                    </div>
                @endif
            </div>

        </div>

        <!-- ============================================================ -->
        <!-- MODAL CATAT PEMBAYARAN -->
        <!-- ============================================================ -->
        <div x-show="openPaymentModal" 
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="openPaymentModal = false" class="bg-white dark:bg-zinc-900 w-full max-w-md rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="font-extrabold text-zinc-900 dark:text-white text-base">Catat Pembayaran Proyek</h3>
                    <button @click="openPaymentModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.payments.store') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Jenis Pembayaran</label>
                        <select name="jenis" class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-950 border rounded-xl text-xs">
                            <option value="dp">Uang Muka (DP)</option>
                            <option value="pelunasan">Pelunasan Akhir</option>
                            <option value="maintenance">Biaya Maintenance</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Nominal Pembayaran (Rp)</label>
                        <input type="number" name="jumlah" value="{{ $project->remaining_balance > 0 ? $project->remaining_balance : $project->harga / 2 }}" required
                               class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-950 border rounded-xl text-xs">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-950 border rounded-xl text-xs">
                                <option value="lunas">LUNAS</option>
                                <option value="pending">PENDING</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Tanggal</label>
                            <input type="date" name="tanggal" value="{{ now()->toDateString() }}" required
                                   class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-950 border rounded-xl text-xs">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Catatan / No. Ref Transfer</label>
                        <input type="text" name="catatan" placeholder="Contoh: Transfer BCA a.n Budi"
                               class="w-full px-3 py-2 bg-zinc-50 dark:bg-zinc-950 border rounded-xl text-xs">
                    </div>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button type="button" @click="openPaymentModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold border text-zinc-600">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL UBAH STATUS PROYEK -->
        <!-- ============================================================ -->
        <div x-show="openStatusModal" 
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="openStatusModal = false" class="bg-white dark:bg-zinc-900 w-full max-w-lg rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <div>
                        <h3 class="font-extrabold text-zinc-900 dark:text-white text-base">Ubah Status Proyek</h3>
                        <p class="text-xs text-zinc-400 mt-0.5">{{ $project->nama_project }}</p>
                    </div>
                    <button @click="openStatusModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.projects.update-status', $project) }}" class="space-y-4">
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
                    </div>                    <!-- Additional inputs for Selesai or Review -->
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

        <!-- ============================================================ -->
        <!-- MODAL EDIT INFORMASI & NILAI KONTRAK PROYEK -->
        <!-- ============================================================ -->
        <div x-show="openEditModal" 
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="openEditModal = false" class="bg-white dark:bg-zinc-900 w-full max-w-lg rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto custom-scrollbar">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-[20px]">edit_square</span>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-zinc-900 dark:text-white text-base">Edit Proyek &amp; Nilai Kontrak</h3>
                            <p class="text-xs text-zinc-400 mt-0.5">{{ $project->nama_project }}</p>
                        </div>
                    </div>
                    <button @click="openEditModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.projects.update', $project) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Nama Proyek <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_project" value="{{ old('nama_project', $project->nama_project) }}" required
                               class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Paket Layanan <span class="text-rose-500">*</span></label>
                            <select name="paket" required class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-800 dark:text-zinc-200 font-bold focus:ring-emerald-500">
                                <option value="landing_page" {{ $project->paket == 'landing_page' ? 'selected' : '' }}>Landing Page</option>
                                <option value="company_profile" {{ $project->paket == 'company_profile' ? 'selected' : '' }}>Company Profile</option>
                                <option value="toko_kasir" {{ $project->paket == 'toko_kasir' ? 'selected' : '' }}>Toko &amp; Kasir POS</option>
                                <option value="custom" {{ $project->paket == 'custom' ? 'selected' : '' }}>Custom Web App</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Total Nilai Kontrak (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="harga" x-model.number="editHarga" min="{{ (int)$project->total_paid }}" required
                                   class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white font-mono font-bold focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Live Calculation Box -->
                    <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-zinc-500">Total Telah Terbayar:</span>
                            <span class="font-mono font-bold text-emerald-600 block">Rp {{ number_format($project->total_paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-zinc-500">Estimasi Sisa Tagihan Baru:</span>
                            <span class="font-mono font-black text-rose-500 block" x-text="'Rp ' + Math.max(0, editHarga - totalPaid).toLocaleString('id-ID')"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" value="{{ $project->tanggal_mulai ? $project->tanggal_mulai->toDateString() : '' }}"
                                   class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Target Selesai</label>
                            <input type="date" name="tanggal_selesai" value="{{ $project->tanggal_selesai ? $project->tanggal_selesai->toDateString() : '' }}"
                                   class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Link URL Website (Live / Preview)</label>
                        <input type="url" name="link_website" value="{{ old('link_website', $project->link_website) }}" placeholder="https://domainklien.com"
                               class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Catatan Proyek</label>
                        <textarea name="catatan" rows="3" placeholder="Catatan internal kesepakatan fitur..."
                                  class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500">{{ old('catatan', $project->catatan) }}</textarea>
                    </div>

                    @if($project->client_id)
                        <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200/60 dark:border-blue-800/40 flex items-center gap-2 text-xs text-blue-700 dark:text-blue-300">
                            <span class="material-symbols-outlined text-[18px]">sync</span>
                            <span>Perubahan harga &amp; data ini otomatis memperbarui tagihan di Client Panel.</span>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button type="button" @click="openEditModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold border text-zinc-600">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

<x-app-layout>
    <div class="py-6 sm:py-8 bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 min-h-screen transition-colors duration-300"
         x-data="{ 
            activeTab: 'chat',
            openDealModal: false
         }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Alert -->
            <x-flash />

            <!-- Breadcrumbs & Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-center gap-2 text-xs font-semibold text-zinc-400">
                    <a href="{{ route('admin.leads.index') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        <span>Daftar Leads</span>
                    </a>
                    <span>/</span>
                    <span class="text-zinc-800 dark:text-zinc-200 font-bold truncate">{{ $lead->nama_usaha }}</span>
                </div>

                <div class="flex items-center gap-2.5">
                    @if($lead->status !== 'deal')
                        <button @click="openDealModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all duration-200 shadow-sm hover:shadow active:scale-95">
                            <span class="material-symbols-outlined text-[18px]">handshake</span>
                            <span>Tandai Deal (Konversi Project)</span>
                        </button>
                    @endif
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->kontak_wa) }}" target="_blank" 
                       class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200/60 dark:border-emerald-800/40 text-emerald-700 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-100 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">chat</span>
                        <span>Buka di WhatsApp Web</span>
                    </a>
                </div>
            </div>

            <!-- Profile Summary Card -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    
                    <div class="flex items-start gap-4 min-w-0">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 flex items-center justify-center font-black text-xl shrink-0">
                            {{ strtoupper(substr($lead->nama_usaha, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h1 class="text-xl sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">{{ $lead->nama_usaha }}</h1>
                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] uppercase font-bold tracking-wider {{ $lead->status === 'deal' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300' }}">
                                    {{ $lead->status_label }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                                @if($lead->nama_kontak)
                                    <span class="flex items-center gap-1 font-medium text-zinc-800 dark:text-zinc-200">
                                        <span class="material-symbols-outlined text-[16px] text-zinc-400">person</span>
                                        {{ $lead->nama_kontak }}
                                    </span>
                                @endif
                                <span class="flex items-center gap-1 font-mono">
                                    <span class="material-symbols-outlined text-[16px] text-zinc-400">call</span>
                                    {{ $lead->kontak_wa }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-zinc-400">hub</span>
                                    Sumber: {{ $lead->sumber_label }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px] text-zinc-400">web</span>
                                    Paket: {{ $lead->paket_label }}
                                </span>
                                @if($lead->nilai_nego && $lead->nilai_nego > 0)
                                    <span class="flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 dark:bg-amber-950/40 border border-amber-200/60 dark:border-amber-800/40 text-amber-700 dark:text-amber-300 font-bold font-mono">
                                        <span class="material-symbols-outlined text-[14px]">price_change</span>
                                        Nego: Rp {{ number_format($lead->nilai_nego, 0, ',', '.') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Metrics Badge on Right -->
                    <div class="flex items-center gap-4 shrink-0 bg-zinc-50 dark:bg-zinc-950/50 p-4 rounded-xl border border-zinc-100 dark:border-zinc-800/80">
                        <div class="text-center">
                            <p class="text-[10px] font-mono uppercase text-zinc-400 dark:text-zinc-500 font-bold">Proyek</p>
                            <p class="text-lg font-black text-zinc-900 dark:text-white mt-0.5">{{ $lead->projects->count() }}</p>
                        </div>
                        <div class="w-px h-8 bg-zinc-200 dark:bg-zinc-800"></div>
                        <div class="text-center">
                            <p class="text-[10px] font-mono uppercase text-zinc-400 dark:text-zinc-500 font-bold">Pesan WA</p>
                            <p class="text-lg font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $lead->messageLogs->count() }}</p>
                        </div>
                        <div class="w-px h-8 bg-zinc-200 dark:bg-zinc-800"></div>
                        <div class="text-center">
                            <p class="text-[10px] font-mono uppercase text-zinc-400 dark:text-zinc-500 font-bold">Follow-Up</p>
                            <p class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1">
                                {{ $lead->follow_up_date ? $lead->follow_up_date->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="flex items-center gap-3 border-b border-zinc-200 dark:border-zinc-800 text-xs font-bold">
                <button @click="activeTab = 'chat'" 
                        :class="activeTab === 'chat' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400 border-b-2 bg-emerald-50/50 dark:bg-emerald-950/20' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white border-transparent'"
                        class="px-4 py-3 rounded-t-xl transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">chat</span>
                    <span>Riwayat Obrolan WhatsApp ({{ $lead->messageLogs->count() }})</span>
                </button>
                <button @click="activeTab = 'projects'" 
                        :class="activeTab === 'projects' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400 border-b-2 bg-emerald-50/50 dark:bg-emerald-950/20' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white border-transparent'"
                        class="px-4 py-3 rounded-t-xl transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">view_kanban</span>
                    <span>Proyek &amp; Pembayaran ({{ $lead->projects->count() }})</span>
                </button>
                <button @click="activeTab = 'edit'" 
                        :class="activeTab === 'edit' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400 border-b-2 bg-emerald-50/50 dark:bg-emerald-950/20' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-white border-transparent'"
                        class="px-4 py-3 rounded-t-xl transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">edit_note</span>
                    <span>Ubah Info &amp; Catatan</span>
                </button>
            </div>

            <!-- TAB 1: WHATSAPP CHAT TIMELINE -->
            <div x-show="activeTab === 'chat'" class="space-y-6">
                
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm overflow-hidden flex flex-col min-h-[500px]">
                    <!-- Chat Header Bar -->
                    <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-950/60 border-b border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                            <div>
                                <h3 class="text-xs font-bold text-zinc-900 dark:text-white">Riwayat Obrolan WhatsApp Klien</h3>
                                <p class="text-[10px] text-zinc-400 font-mono">Nomor tujuan: {{ $lead->kontak_wa }}</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-mono text-zinc-400 uppercase font-bold">Sinkronisasi Otomatis</span>
                    </div>

                    <!-- Chat Messages List Area -->
                    <div class="flex-1 p-6 space-y-4 overflow-y-auto max-h-[550px] bg-zinc-50/40 dark:bg-zinc-950/30">
                        @forelse($lead->messageLogs as $msg)
                            @if($msg->arah === 'keluar')
                                <!-- Outgoing Message (Green Bubble) -->
                                <div class="flex flex-col items-end">
                                    <div class="max-w-lg bg-emerald-600 text-white p-4 rounded-2xl rounded-tr-xs shadow-sm text-xs leading-relaxed space-y-1">
                                        <div class="text-[10px] font-mono text-emerald-100 font-bold uppercase tracking-wider mb-1 flex items-center justify-between gap-4">
                                            <span>VexaHost DIGITAL ({{ strtoupper(str_replace('_', ' ', $msg->tipe_pesan)) }})</span>
                                            <span class="material-symbols-outlined text-[14px]">done_all</span>
                                        </div>
                                        <div class="whitespace-pre-line">{!! nl2br(e($msg->isi_pesan)) !!}</div>
                                    </div>
                                    <span class="text-[10px] font-mono text-zinc-400 mt-1 mr-1">{{ $msg->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            @else
                                <!-- Incoming Message (White / Zinc Bubble) -->
                                <div class="flex flex-col items-start">
                                    <div class="max-w-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 border border-zinc-200 dark:border-zinc-700/80 p-4 rounded-2xl rounded-tl-xs shadow-sm text-xs leading-relaxed space-y-1">
                                        <div class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold uppercase tracking-wider mb-1 flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">call_received</span>
                                            <span>BALASAN KLIEN ({{ $lead->nama_kontak ?: $lead->nama_usaha }})</span>
                                        </div>
                                        <div class="whitespace-pre-line">{!! nl2br(e($msg->isi_pesan)) !!}</div>
                                    </div>
                                    <span class="text-[10px] font-mono text-zinc-400 mt-1 ml-1">{{ $msg->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            @endif
                        @empty
                            <div class="py-16 text-center text-zinc-400 dark:text-zinc-500">
                                <span class="material-symbols-outlined text-[48px] text-zinc-300 dark:text-zinc-700 block mb-2">chat</span>
                                <p class="text-sm font-bold text-zinc-600 dark:text-zinc-400">Belum ada riwayat pesan WhatsApp.</p>
                                <p class="text-xs mt-1">Pesan otomatis akan tercatat saat status project diubah atau ketika mengirim pesan manual di bawah.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Manual WhatsApp Send Box -->
                    <div class="p-4 bg-white dark:bg-zinc-900 border-t border-zinc-200/80 dark:border-zinc-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300">Kirim Pesan WhatsApp Manual</span>
                            <button type="button" @click="$dispatch('open-quick-snippets')" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                                <span class="material-symbols-outlined text-[14px]">content_paste</span>
                                <span>Template Chat WA</span>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('admin.messages.send-manual') }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                            
                            <div class="relative">
                                <textarea name="isi_pesan" rows="3" required placeholder="Tulis pesan WhatsApp santai untuk {{ $lead->nama_usaha }}..."
                                          class="w-full p-3.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"></textarea>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                                <p class="text-[11px] text-zinc-400 dark:text-zinc-500">
                                    💡 Pesan dikirim langsung via WhatsApp ke nomor <b>{{ $lead->kontak_wa }}</b>.
                                </p>
                                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">send</span>
                                    <span>Kirim Chat WhatsApp</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <!-- TAB 2: PROYEK & PEMBAYARAN -->
            <div x-show="activeTab === 'projects'" class="space-y-6" style="display: none;">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="font-bold text-zinc-900 dark:text-white text-base">Daftar Proyek Website</h3>
                        @if($lead->status === 'deal')
                            <a href="{{ route('admin.projects.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">Buka Semua Proyek</a>
                        @endif
                    </div>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800 mt-2">
                        @forelse($lead->projects as $project)
                            <div class="py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <div>
                                    <a href="{{ route('admin.projects.show', $project) }}" class="text-sm font-bold text-zinc-900 dark:text-white hover:text-emerald-600">
                                        {{ $project->nama_project }}
                                    </a>
                                    <p class="text-xs text-zinc-500 mt-0.5">Paket: {{ $project->paket_label }} • Nilai: Rp {{ number_format($project->harga, 0, ',', '.') }}</p>
                                    @if($project->link_website)
                                        <a href="{{ $project->link_website }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:underline mt-1 font-mono">
                                            <span class="material-symbols-outlined text-[14px]">link</span>
                                            {{ $project->link_website }}
                                        </a>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                        {{ $project->status_label }}
                                    </span>
                                    @if($project->latestInvoice && $project->latestInvoice->payment_token)
                                        <a href="{{ route('invoices.pay', $project->latestInvoice->payment_token) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 inline-flex items-center gap-1 shadow-xs">
                                            <span class="material-symbols-outlined text-[14px]">payments</span>
                                            <span>Halaman Bayar Xendit</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.projects.show', $project) }}" class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-100">
                                        Kelola Proyek
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-zinc-400 dark:text-zinc-500">
                                <p class="text-xs">Belum ada proyek untuk prospek ini. Klik tombol "Tandai Deal" untuk memulai proyek baru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Maintenance Subscriptions Section -->
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm p-6">
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base pb-4 border-b border-zinc-100 dark:border-zinc-800">
                        Langganan Maintenance Bulanan
                    </h3>

                    <div class="divide-y divide-zinc-100 dark:divide-zinc-800 mt-2">
                        @forelse($lead->maintenanceSubscriptions as $sub)
                            <div class="py-4 flex items-center justify-between gap-4">
                                <div>
                                    <span class="text-sm font-bold text-zinc-900 dark:text-white">Rp {{ number_format($sub->harga_bulanan, 0, ',', '.') }} / bulan</span>
                                    <p class="text-xs text-zinc-500 mt-0.5">Jatuh tempo berikutnya: {{ $sub->tanggal_jatuh_tempo_berikutnya ? $sub->tanggal_jatuh_tempo_berikutnya->format('d F Y') : '-' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase {{ $sub->status === 'aktif' ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">
                                        {{ strtoupper($sub->status) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-zinc-400 dark:text-zinc-500">
                                <p class="text-xs">Belum berlangganan maintenance bulanan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- TAB 3: EDIT INFO & CATATAN -->
            <div x-show="activeTab === 'edit'" class="space-y-6" style="display: none;">
                <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm p-6 sm:p-8">
                    <h3 class="font-bold text-zinc-900 dark:text-white text-base pb-4 border-b border-zinc-100 dark:border-zinc-800 mb-6">
                        Perbarui Data Prospek &amp; Catatan
                    </h3>

                    <form method="POST" action="{{ route('admin.leads.update', $lead) }}" class="space-y-4 max-w-2xl">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Nama Usaha / Bisnis UMKM</label>
                            <input type="text" name="nama_usaha" value="{{ old('nama_usaha', $lead->nama_usaha) }}" required
                                   class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Nama Kontak / Owner</label>
                                <input type="text" name="nama_kontak" value="{{ old('nama_kontak', $lead->nama_kontak) }}"
                                       class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Nomor WhatsApp</label>
                                <input type="text" name="kontak_wa" value="{{ old('kontak_wa', $lead->kontak_wa) }}" required
                                       class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Email Klien (Opsional)</label>
                                <input type="email" name="email" value="{{ old('email', $lead->email) }}" placeholder="klien@bisnis.com"
                                       class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Status Prospek</label>
                                <select name="status" class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-700 dark:text-zinc-300 focus:ring-emerald-500">
                                    <option value="belum_dihubungi" {{ $lead->status == 'belum_dihubungi' ? 'selected' : '' }}>Belum Dihubungi</option>
                                    <option value="sudah_chat" {{ $lead->status == 'sudah_chat' ? 'selected' : '' }}>Sudah Chat</option>
                                    <option value="nego" {{ $lead->status == 'nego' ? 'selected' : '' }}>Nego / Tertarik</option>
                                    <option value="deal" {{ $lead->status == 'deal' ? 'selected' : '' }}>Deal / Klien</option>
                                    <option value="tidak_lanjut" {{ $lead->status == 'tidak_lanjut' ? 'selected' : '' }}>Tidak Lanjut</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Sumber Lead</label>
                                <select name="sumber" class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-700 dark:text-zinc-300 focus:ring-emerald-500">
                                    <option value="warm_network" {{ $lead->sumber == 'warm_network' ? 'selected' : '' }}>Warm Network</option>
                                    <option value="cold_outreach" {{ $lead->sumber == 'cold_outreach' ? 'selected' : '' }}>Cold Outreach</option>
                                    <option value="komunitas" {{ $lead->sumber == 'komunitas' ? 'selected' : '' }}>Komunitas</option>
                                    <option value="marketplace" {{ $lead->sumber == 'marketplace' ? 'selected' : '' }}>Marketplace</option>
                                    <option value="referral" {{ $lead->sumber == 'referral' ? 'selected' : '' }}>Referral</option>
                                    <option value="website" {{ $lead->sumber == 'website' ? 'selected' : '' }}>Website</option>
                                    <option value="lainnya" {{ $lead->sumber == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Paket Diminati</label>
                                <select name="paket_diminati" class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-700 dark:text-zinc-300 focus:ring-emerald-500">
                                    <option value="landing_page" {{ $lead->paket_diminati == 'landing_page' ? 'selected' : '' }}>Landing Page (Rp 499rb)</option>
                                    <option value="company_profile" {{ $lead->paket_diminati == 'company_profile' ? 'selected' : '' }}>Company Profile (Rp 999rb)</option>
                                    <option value="toko_kasir" {{ $lead->paket_diminati == 'toko_kasir' ? 'selected' : '' }}>Toko &amp; Kasir POS (Rp 1.5jt)</option>
                                    <option value="custom" {{ $lead->paket_diminati == 'custom' ? 'selected' : '' }}>Custom Web App</option>
                                    <option value="belum_tahu" {{ $lead->paket_diminati == 'belum_tahu' ? 'selected' : '' }}>Belum Tahu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Estimasi / Nilai Nego (Rp)</label>
                                <input type="number" name="nilai_nego" value="{{ old('nilai_nego', $lead->nilai_nego) }}" placeholder="Contoh: 750000" min="0"
                                       class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                                <span class="text-[10px] text-zinc-400">Kosongkan jika mengikuti harga paket resmi</span>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Jadwal Follow-Up</label>
                                <input type="date" name="follow_up_date" value="{{ $lead->follow_up_date ? $lead->follow_up_date->toDateString() : '' }}"
                                       class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-300 mb-1">Catatan</label>
                            <textarea name="catatan" rows="4"
                                      class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500">{{ old('catatan', $lead->catatan) }}</textarea>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-colors">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- ============================================================ -->
        <!-- MODAL KONVERSI KE DEAL -->
        <!-- ============================================================ -->
        <div x-show="openDealModal" 
             x-transition
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/70 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="openDealModal = false" class="bg-white dark:bg-zinc-900 w-full max-w-md rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-6 space-y-5">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <h3 class="font-extrabold text-zinc-900 dark:text-white text-base flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600">handshake</span>
                        <span>Konversi ke Proyek Deal</span>
                    </h3>
                    <button @click="openDealModal = false" class="text-zinc-400 hover:text-zinc-600">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.leads.convert-deal', $lead) }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Nama Proyek</label>
                        <input type="text" name="nama_project" value="Website {{ $lead->nama_usaha }}" required
                               class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Harga Kesepakatan (Rp)</label>
                        <input type="number" name="harga" value="{{ $lead->getEstimatedDealPrice() }}" required
                               class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-900 dark:text-white font-mono font-bold">
                        @if($lead->nilai_nego)
                            <p class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold mt-1">
                                ✓ Menggunakan estimasi harga kesepakatan nego prospek (Rp {{ number_format($lead->nilai_nego, 0, ',', '.') }}).
                            </p>
                        @endif
                    </div>

                    <input type="hidden" name="sync_portal" value="0">
                    <label class="flex items-start gap-2.5 p-3 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-800/50 cursor-pointer">
                        <input type="checkbox" name="sync_portal" value="1" checked class="mt-0.5 rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="text-[11px] text-zinc-700 dark:text-zinc-300 leading-relaxed">
                            <strong>Kirim WhatsApp Otomatis Sekarang:</strong> Buat akun Client Panel &amp; kirim pesan WA berisi akses login + <strong>Link Pembayaran Langsung Xendit</strong> (QRIS / VA Mandiri &amp; BNI).
                        </span>
                    </label>

                    <p class="text-[11px] text-zinc-500">
                        Status lead akan diubah menjadi <b>Deal</b> dan project baru akan dibuat dalam status <b>Draft</b>.
                    </p>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button type="button" @click="openDealModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold border text-zinc-600">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm">Konfirmasi Deal</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

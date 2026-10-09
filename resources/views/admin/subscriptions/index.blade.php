<x-app-layout>
    <div class="space-y-6 w-full"
         x-data="{
            openCreateModal: false,
            openRenewModal: false,
            renewSubscription: { id: null, project_name: '', tipe: 'tahunan', harga: '', tanggal_expired: '', send_wa: true },

            createTipe: 'tahunan',
            createTanggalMulai: '{{ now()->toDateString() }}',
            createTanggalExpired: '',

            computeExpired() {
                if (!this.createTanggalMulai) return;
                let d = new Date(this.createTanggalMulai);
                if (isNaN(d)) return;
                switch (this.createTipe) {
                    case 'bulanan': d.setMonth(d.getMonth() + 1); break;
                    case '6_bulan': d.setMonth(d.getMonth() + 6); break;
                    case 'tahunan': d.setFullYear(d.getFullYear() + 1); break;
                    case 'custom': return;
                }
                this.createTanggalExpired = d.toISOString().split('T')[0];
            },

            initCreate() {
                this.computeExpired();
            },

            openRenew(sub) {
                this.renewSubscription = {
                    id: sub.id,
                    project_name: sub.project_name,
                    tipe: sub.tipe || 'tahunan',
                    harga: '',
                    tanggal_expired: '',
                    send_wa: true,
                };
                this.openRenewModal = true;
            }
         }"
         x-init="initCreate()">

        <!-- Flash Alert -->
        <x-flash />

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight">Masa Berlaku & Lisensi Proyek</h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">Kelola subscription, masa berlaku domain/hosting, dan lisensi aplikasi klien.</p>
            </div>
            <div>
                <button @click="openCreateModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Tambah Subscription Baru</span>
                </button>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Card 1: Total Subscription Aktif -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 font-mono">Total Subscription Aktif</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ ($statusCounts['aktif'] ?? 0) + ($statusCounts['diperpanjang'] ?? 0) }} <span class="text-xs text-zinc-400 font-normal">Aktif</span></h3>
                </div>
                <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                    <span class="material-symbols-outlined text-[28px]">verified</span>
                </div>
            </div>

            <!-- Card 2: Akan Expired (H-30) -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 font-mono">Akan Expired (H-30)</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-amber-500 mt-1">{{ $statusCounts['akan_expired'] ?? 0 }} <span class="text-xs text-zinc-400 font-normal">Subscription</span></h3>
                </div>
                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-500">
                    <span class="material-symbols-outlined text-[28px]">schedule_send</span>
                </div>
            </div>

            <!-- Card 3: Sudah Expired -->
            <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-zinc-400 font-mono">Sudah Expired</p>
                    <h3 class="text-2xl sm:text-3xl font-black text-rose-500 mt-1">{{ $statusCounts['expired'] ?? 0 }} <span class="text-xs text-zinc-400 font-normal">Subscription</span></h3>
                </div>
                <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/50 text-rose-500">
                    <span class="material-symbols-outlined text-[28px]">error</span>
                </div>
            </div>
        </div>

        <!-- Status Filter Tabs -->
        <div class="inline-flex h-10 items-center justify-start rounded-xl bg-zinc-100 dark:bg-zinc-900 p-1 text-zinc-500 gap-1 overflow-x-auto custom-scrollbar border border-zinc-200/70 dark:border-zinc-800/80 max-w-full">
            <a href="{{ route('admin.subscriptions.index') }}"
               class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ !request('status') ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                Semua ({{ $statusCounts['all'] ?? 0 }})
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'aktif']) }}"
               class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'aktif' ? 'bg-emerald-600 text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                Aktif ({{ $statusCounts['aktif'] ?? 0 }})
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'akan_expired']) }}"
               class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'akan_expired' ? 'bg-amber-500 text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                Akan Expired ({{ $statusCounts['akan_expired'] ?? 0 }})
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'expired']) }}"
               class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'expired' ? 'bg-rose-600 text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                Expired ({{ $statusCounts['expired'] ?? 0 }})
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'diperpanjang']) }}"
               class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'diperpanjang' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                Diperpanjang ({{ $statusCounts['diperpanjang'] ?? 0 }})
            </a>
            <a href="{{ route('admin.subscriptions.index', ['status' => 'nonaktif']) }}"
               class="inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-xs transition-all {{ request('status') === 'nonaktif' ? 'bg-white dark:bg-zinc-800 text-zinc-900 dark:text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white font-medium' }}">
                Nonaktif ({{ $statusCounts['nonaktif'] ?? 0 }})
            </a>
        </div>

        <!-- Subscriptions Table Card -->
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">
            <!-- Desktop Table (md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50/70 dark:bg-zinc-950/50 border-b border-zinc-200/80 dark:border-zinc-800 text-[11px] font-bold font-mono uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                        <tr>
                            <th class="px-6 py-4">Klien / Project</th>
                            <th class="px-6 py-4">Tipe</th>
                            <th class="px-6 py-4">Harga</th>
                            <th class="px-6 py-4">Tanggal Mulai</th>
                            <th class="px-6 py-4">Tanggal Expired</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60 text-xs">
                        @forelse($subscriptions as $sub)
                            @php
                                $sisaHari = $sub->sisa_hari;
                                $statusColor = $sub->status_color;
                            @endphp
                            <tr class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/30 transition-colors">
                                <!-- Klien / Project -->
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.projects.show', $sub->project) }}" class="font-bold text-zinc-900 dark:text-white hover:text-emerald-600">
                                        {{ $sub->project->nama_project ?? '-' }}
                                    </a>
                                    <p class="text-zinc-400 text-[11px] mt-0.5">{{ $sub->lead->nama_usaha ?? '-' }}</p>
                                </td>

                                <!-- Tipe -->
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold text-[11px]">
                                        {{ $sub->tipe_label }}
                                    </span>
                                </td>

                                <!-- Harga -->
                                <td class="px-6 py-4 font-mono font-bold text-zinc-900 dark:text-white">
                                    Rp {{ number_format($sub->harga, 0, ',', '.') }}
                                </td>

                                <!-- Tanggal Mulai -->
                                <td class="px-6 py-4 font-mono text-zinc-500">
                                    {{ $sub->tanggal_mulai ? $sub->tanggal_mulai->format('d M Y') : '-' }}
                                </td>

                                <!-- Tanggal Expired -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold {{ $statusColor === 'red' ? 'text-rose-600 dark:text-rose-400' : ($statusColor === 'yellow' ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-700 dark:text-zinc-300') }}">
                                            {{ $sub->tanggal_expired ? $sub->tanggal_expired->format('d M Y') : '-' }}
                                        </span>
                                        @if($sub->tanggal_expired)
                                            @if($sub->isExpired())
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-rose-500 text-white">Expired</span>
                                            @elseif($sisaHari <= 30 && $sisaHari > 0)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-amber-500 text-white">H-{{ $sisaHari }}</span>
                                            @else
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-500">{{ $sisaHari }} hari</span>
                                            @endif
                                        @endif
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider
                                        @if($statusColor === 'green') bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/50
                                        @elseif($statusColor === 'yellow') bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/50
                                        @elseif($statusColor === 'red') bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/50
                                        @else bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400
                                        @endif">
                                        {{ $sub->status_label }}
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Reminder WA -->
                                        <form method="POST" action="{{ route('admin.subscriptions.reminder', $sub) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 text-[11px] font-bold hover:bg-emerald-100 dark:hover:bg-emerald-950/80 flex items-center gap-1 transition-colors" title="Kirim Reminder WA">
                                                <span class="material-symbols-outlined text-[14px]">chat</span>
                                                <span>WA</span>
                                            </button>
                                        </form>

                                        <!-- Perpanjang -->
                                        <button @click="openRenew({ id: {{ $sub->id }}, project_name: '{{ addslashes($sub->project->nama_project ?? '') }}', tipe: '{{ $sub->tipe }}' })"
                                                class="px-2.5 py-1.5 rounded-lg bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400 text-[11px] font-bold hover:bg-sky-100 dark:hover:bg-sky-950/80 flex items-center gap-1 transition-colors" title="Perpanjang Subscription">
                                            <span class="material-symbols-outlined text-[14px]">autorenew</span>
                                            <span>Perpanjang</span>
                                        </button>

                                        <!-- Toggle Status -->
                                        <form method="POST" action="{{ route('admin.subscriptions.toggle', $sub) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors" title="Ubah Aktif/Nonaktif">
                                                <span class="material-symbols-outlined text-[18px]">{{ in_array($sub->status, ['aktif', 'akan_expired', 'diperpanjang']) ? 'toggle_on' : 'toggle_off' }}</span>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('admin.subscriptions.destroy', $sub) }}" x-ref="deleteSubDesktop{{ $sub->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" @click="VhSwal.confirmDelete('Apakah Anda yakin ingin menghapus subscription untuk {{ addslashes($sub->project->nama_project ?? '') }}?', $refs['deleteSubDesktop{{ $sub->id }}'])" class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors" title="Hapus Subscription">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-zinc-400 text-xs">
                                    Belum ada data subscription proyek.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards (md:hidden) -->
            <div class="md:hidden divide-y divide-zinc-100 dark:divide-zinc-800/60">
                @forelse($subscriptions as $sub)
                    @php
                        $sisaHari = $sub->sisa_hari;
                        $statusColor = $sub->status_color;
                    @endphp
                    <div class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <a href="{{ route('admin.projects.show', $sub->project) }}" class="font-bold text-xs text-zinc-900 dark:text-white">
                                    {{ $sub->project->nama_project ?? '-' }}
                                </a>
                                <p class="text-[11px] text-zinc-500 mt-0.5">{{ $sub->lead->nama_usaha ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                @if($statusColor === 'green') bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400
                                @elseif($statusColor === 'yellow') bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400
                                @elseif($statusColor === 'red') bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400
                                @else bg-zinc-100 text-zinc-500
                                @endif">
                                {{ $sub->status_label }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 font-semibold text-[10px]">
                                {{ $sub->tipe_label }}
                            </span>
                            <span class="font-mono font-bold text-xs text-zinc-900 dark:text-white">
                                Rp {{ number_format($sub->harga, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-500">Mulai</span>
                            <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $sub->tanggal_mulai ? $sub->tanggal_mulai->format('d M Y') : '-' }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-zinc-500">Expired</span>
                            <div class="flex items-center gap-1 font-mono font-semibold {{ $statusColor === 'red' ? 'text-rose-600' : ($statusColor === 'yellow' ? 'text-amber-600' : 'text-zinc-700 dark:text-zinc-300') }}">
                                <span>{{ $sub->tanggal_expired ? $sub->tanggal_expired->format('d M Y') : '-' }}</span>
                                @if($sub->tanggal_expired)
                                    @if($sub->isExpired())
                                        <span class="px-1 py-0.5 rounded text-[8px] bg-rose-500 text-white font-bold uppercase">Expired</span>
                                    @elseif($sisaHari <= 30 && $sisaHari > 0)
                                        <span class="px-1 py-0.5 rounded text-[8px] bg-amber-500 text-white font-bold uppercase">H-{{ $sisaHari }}</span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-zinc-100 dark:border-zinc-800/80 text-xs">
                            <div class="flex items-center gap-1.5">
                                <form method="POST" action="{{ route('admin.subscriptions.reminder', $sub) }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white font-bold text-[11px]">
                                        <span class="material-symbols-outlined text-[14px]">chat</span>
                                        <span>Reminder WA</span>
                                    </button>
                                </form>
                                <button @click="openRenew({ id: {{ $sub->id }}, project_name: '{{ addslashes($sub->project->nama_project ?? '') }}', tipe: '{{ $sub->tipe }}' })"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-sky-100 dark:bg-sky-950/50 text-sky-700 dark:text-sky-400 font-bold text-[11px]">
                                    <span class="material-symbols-outlined text-[14px]">autorenew</span>
                                    <span>Perpanjang</span>
                                </button>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <form method="POST" action="{{ route('admin.subscriptions.toggle', $sub) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" title="Ubah Aktif/Nonaktif">
                                        <span class="material-symbols-outlined text-[18px]">{{ in_array($sub->status, ['aktif', 'akan_expired', 'diperpanjang']) ? 'toggle_on' : 'toggle_off' }}</span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.subscriptions.destroy', $sub) }}" x-ref="deleteSubMobile{{ $sub->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" @click="VhSwal.confirmDelete('Hapus subscription ini?', $refs['deleteSubMobile{{ $sub->id }}'])" class="p-1.5 rounded-lg text-zinc-400 hover:text-rose-600 dark:hover:text-rose-400" title="Hapus Subscription">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-zinc-400 text-xs">
                        Belum ada data subscription proyek.
                    </div>
                @endforelse
            </div>

            @if($subscriptions->hasPages())
                <div class="p-4 border-t border-zinc-200/80 dark:border-zinc-800">
                    {{ $subscriptions->links() }}
                </div>
            @endif
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: Tambah Subscription Baru -->
        <!-- ============================================================ -->
        <div x-show="openCreateModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-800 p-6 space-y-4"
                 @click.outside="openCreateModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[22px]">add_card</span>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white">Tambah Subscription Baru</h3>
                    </div>
                    <button type="button" @click="openCreateModal = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.subscriptions.store') }}" class="space-y-4">
                    @csrf

                    <!-- Pilih Project -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Pilih Proyek <span class="text-rose-500">*</span></label>
                        <select name="project_id"
                                x-ref="createProjectSelect"
                                @change="
                                    let opt = $event.target.options[$event.target.selectedIndex];
                                    $refs.createLeadId.value = opt.dataset.leadId || '';
                                "
                                class="w-full px-3.5 py-2.5 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs text-zinc-800 dark:text-zinc-200 font-semibold focus:ring-emerald-500" required>
                            <option value="">-- Pilih Proyek --</option>
                            @foreach($availableProjects as $proj)
                                <option value="{{ $proj->id }}" data-lead-id="{{ $proj->lead_id }}">
                                    {{ $proj->nama_project }} ({{ $proj->lead?->nama_usaha }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="lead_id" x-ref="createLeadId">
                    </div>

                    <!-- Tipe + Harga -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Tipe Subscription <span class="text-rose-500">*</span></label>
                            <select name="tipe" x-model="createTipe" @change="computeExpired()"
                                    class="w-full px-3.5 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs font-semibold focus:ring-emerald-500" required>
                                <option value="tahunan">1 Tahun</option>
                                <option value="bulanan">Bulanan</option>
                                <option value="6_bulan">6 Bulan</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Harga (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" name="harga" required min="0" step="1000" placeholder="0"
                                   class="w-full px-3.5 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs font-mono font-bold focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Tanggal Mulai + Expired -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_mulai" x-model="createTanggalMulai" @change="computeExpired()" required
                                   class="w-full px-3.5 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Expired <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_expired" x-model="createTanggalExpired" required
                                   :readonly="createTipe !== 'custom'"
                                   :class="createTipe !== 'custom' ? 'bg-zinc-100 dark:bg-zinc-900 cursor-not-allowed' : 'bg-zinc-50 dark:bg-zinc-950'"
                                   class="w-full px-3.5 py-2 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs focus:ring-emerald-500">
                        </div>
                    </div>

                    <!-- Auto Renew -->
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="auto_renew" value="1" id="create_auto_renew"
                               class="rounded border-zinc-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-500">
                        <label for="create_auto_renew" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Auto Renew (perpanjang otomatis saat expired)</label>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Catatan</label>
                        <textarea name="catatan" rows="2" placeholder="Detail subscription, domain, hosting provider, lisensi key, dsb..."
                                  class="w-full px-3.5 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs focus:ring-emerald-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button type="button" @click="openCreateModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition-all">Simpan Subscription</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- MODAL: Perpanjang Subscription -->
        <!-- ============================================================ -->
        <div x-show="openRenewModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-zinc-900/60 backdrop-blur-xs"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl shadow-xl border border-zinc-200 dark:border-zinc-800 p-6 space-y-4"
                 @click.outside="openRenewModal = false">
                <div class="flex items-center justify-between pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-sky-600 text-[22px]">autorenew</span>
                        <h3 class="text-base font-bold text-zinc-900 dark:text-white">Perpanjang Subscription</h3>
                    </div>
                    <button type="button" @click="openRenewModal = false" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    Memperpanjang: <strong class="text-zinc-900 dark:text-white" x-text="renewSubscription.project_name"></strong>
                </p>

                <form method="POST" :action="`{{ url('/admin/subscriptions') }}/${renewSubscription.id}/renew`" class="space-y-4">
                    @csrf

                    <!-- Tipe Perpanjangan -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Tipe Perpanjangan <span class="text-rose-500">*</span></label>
                        <select name="tipe" x-model="renewSubscription.tipe"
                                class="w-full px-3.5 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs font-semibold focus:ring-emerald-500" required>
                            <option value="tahunan">1 Tahun</option>
                            <option value="bulanan">Bulanan</option>
                            <option value="6_bulan">6 Bulan</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <!-- Harga Baru (opsional) -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Harga Baru (opsional)</label>
                        <input type="number" name="harga" x-model="renewSubscription.harga" min="0" step="1000" placeholder="Kosongkan jika tetap sama"
                               class="w-full px-3.5 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs font-mono font-bold focus:ring-emerald-500">
                    </div>

                    <!-- Tanggal Expired Baru (opsional, untuk custom) -->
                    <div>
                        <label class="block text-xs font-bold uppercase text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Expired Baru <span class="text-zinc-400 font-normal normal-case">(opsional, untuk tipe custom)</span></label>
                        <input type="date" name="tanggal_expired" x-model="renewSubscription.tanggal_expired"
                               class="w-full px-3.5 py-2 bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 rounded-xl text-xs focus:ring-emerald-500">
                    </div>

                    <!-- Kirim Notifikasi WA -->
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="send_wa" value="1" id="renew_send_wa" x-model="renewSubscription.send_wa"
                               class="rounded border-zinc-300 dark:border-zinc-700 text-emerald-600 focus:ring-emerald-500">
                        <label for="renew_send_wa" class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Kirim notifikasi WhatsApp ke klien</label>
                    </div>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex justify-end gap-2">
                        <button type="button" @click="openRenewModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition-all">Perpanjang Sekarang</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>

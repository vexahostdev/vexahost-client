<x-app-layout>
    <div class="w-full space-y-6">
        
        <!-- Navigation Back & Title -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <a href="{{ route('invoices.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-100 mb-2 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                    <span>Kembali ke Daftar Tagihan</span>
                </a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl sm:text-3xl font-black text-zinc-900 dark:text-white tracking-tight">
                        {{ $invoice->invoice_number }}
                    </h1>
                    @php $badge = $invoice->status_badge; @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badge['class'] }}">
                        <span class="material-symbols-outlined text-[16px]">{{ $badge['icon'] }}</span>
                        <span>{{ $badge['label'] }}</span>
                    </span>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                    {{ $invoice->title }} &bull; Proyek: <strong>{{ $invoice->project?->name ?? '-' }}</strong>
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('invoices.download-pdf', $invoice) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-emerald-500 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all shadow-2xs hover:shadow-xs">
                    <span class="material-symbols-outlined text-[18px] text-rose-500">picture_as_pdf</span>
                    <span>Unduh PDF Tagihan</span>
                </a>

                @if($invoice->payment_token)
                    <a href="{{ route('invoices.pay', $invoice->payment_token) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 hover:border-emerald-500 text-emerald-700 dark:text-emerald-300 text-xs font-bold transition-all shadow-2xs hover:shadow-xs" title="Buka Halaman Pembayaran Langsung (Guest Checkout Tanpa Login)">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600 dark:text-emerald-400">bolt</span>
                        <span>Link Bayar (Tanpa Login)</span>
                    </a>
                @endif

                @if($invoice->status === 'paid' || $invoice->status === 'partially_paid' || $invoice->paid_amount > 0)
                    <a href="{{ route('invoices.receipt', $invoice) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 hover:border-blue-500 text-blue-700 dark:text-blue-300 text-xs font-bold transition-all shadow-2xs hover:shadow-xs">
                        <span class="material-symbols-outlined text-[18px] text-blue-600">receipt_long</span>
                        <span>{{ $invoice->status === 'partially_paid' ? 'Lihat Kwitansi DP' : 'Lihat Kwitansi Lunas' }}</span>
                    </a>
                @endif

                @if($isAdmin)
                    @php
                        $clientCleanPhone = !empty($invoice->client?->phone) ? preg_replace('/[^0-9]/', '', $invoice->client->phone) : '';
                        $directUrl = $invoice->payment_token ? route('invoices.pay', $invoice->payment_token) : null;
                        $directPayText = $directUrl ? "\n\n⚡ Link Bayar Langsung (Tanpa Login):\n👉 " . $directUrl : "";
                        $waMsgToClient = rawurlencode("Halo Kak " . ($invoice->client?->name ?? 'Klien') . ",\n\nKami dari Tim Finance PT DESTINARA CHAKRAWALA ARTHA ingin mengonfirmasi perihal tagihan proyek *" . ($invoice->project?->name ?? 'Proyek') . "* (No. Invoice: {$invoice->invoice_number}).\n\nStatus saat ini: *" . $badge['label'] . "*\nSisa Tagihan: *Rp " . number_format($invoice->balance_due, 0, ',', '.') . "*{$directPayText}\n\nApakah ada yang bisa kami bantu? Terima kasih!");
                    @endphp
                    @if($clientCleanPhone)
                        <a href="https://wa.me/{{ $clientCleanPhone }}?text={{ $waMsgToClient }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#BAFF39] hover:bg-[#a6ec27] text-zinc-950 text-xs font-black transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[18px]">chat</span>
                            <span>Chat WA Klien</span>
                        </a>
                    @endif
                @else
                    @php
                        $waPhone = !empty($settings->phone_support) ? preg_replace('/[^0-9]/', '', $settings->phone_support) : '6285808749131';
                        $waShareText = rawurlencode("Halo Tim Finance {$settings->company_name},\n\nSaya ingin mengonfirmasi pembayaran untuk tagihan:\n- No. Invoice: {$invoice->invoice_number}\n- Proyek: {$invoice->project?->name}\n- Total Nilai: Rp " . number_format($invoice->amount, 0, ',', '.') . "\n- Sisa Tagihan: Rp " . number_format($invoice->balance_due, 0, ',', '.') . "\n\nTerlampir bukti transfer saya. Mohon dicek. Terima kasih!");
                    @endphp
                    <a href="https://wa.me/{{ $waPhone }}?text={{ $waShareText }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#BAFF39] hover:bg-[#a6ec27] text-zinc-950 text-xs font-black transition-all shadow-xs">
                        <span class="material-symbols-outlined text-[18px]">chat</span>
                        <span>Konfirmasi via WhatsApp</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Mode Administrator Banner (If Admin) -->
        @if($isAdmin)
            <div class="p-3.5 rounded-xl bg-zinc-900 border border-zinc-800 text-white flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-emerald-400 text-[20px]">shield_person</span>
                    <div class="text-xs">
                        <span class="font-bold text-white uppercase tracking-wider text-[11px] bg-emerald-950 text-emerald-300 border border-emerald-800/60 px-2 py-0.5 rounded mr-1">Admin Mode</span>
                        <span class="text-zinc-300">Anda sedang meninjau tagihan klien <strong>{{ $invoice->client?->name ?? 'Klien' }}</strong> &bull; Proyek: <strong>{{ $invoice->project?->name ?? '-' }}</strong></span>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-800 hover:bg-zinc-700 text-zinc-200 text-xs font-bold transition-all border border-zinc-700">
                        <span class="material-symbols-outlined text-[15px]">open_in_new</span>
                        <span>Buka CRM Proyek</span>
                    </a>
                </div>
            </div>
        @endif

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-start gap-3 shadow-xs">
                <span class="material-symbols-outlined text-[20px] text-emerald-600 dark:text-emerald-400 shrink-0">check_circle</span>
                <div>
                    <span class="font-bold text-xs">Berhasil!</span>
                    <p class="text-xs mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div class="p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-rose-800 dark:text-rose-300 rounded-xl flex items-start gap-3 shadow-xs">
                <span class="material-symbols-outlined text-[20px] text-rose-600 dark:text-rose-400 shrink-0">error</span>
                <div>
                    <span class="font-bold text-xs">Perhatian!</span>
                    <p class="text-xs mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Main Invoice Grid Layout (2 Columns) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Left Column: Official Invoice Details (2 cols) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Main Invoice Printable Sheet -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-6 sm:p-8 space-y-6">
                    
                    <!-- Top Invoice Meta -->
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between pb-6 border-b border-zinc-200/60 dark:border-zinc-800 gap-4">
                        <div class="space-y-2">
                            <div class="flex items-center gap-3">
                                <x-vh-logo size="md" />
                                <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 dark:bg-zinc-800 text-emerald-800 dark:text-[#BAFF39] font-bold">
                                    Dokumen Resmi
                                </span>
                            </div>
                            <div class="pt-1">
                                <div class="font-extrabold text-zinc-900 dark:text-white text-sm sm:text-base tracking-tight">
                                    {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}
                                </div>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed max-w-sm mt-0.5">
                                    Layanan Pembuatan Website, Sistem POS &amp; Aplikasi Digital<br>
                                    WhatsApp: {{ $settings->phone_support ?? '0858-0874-9131' }} &bull; Email: {{ $settings->email_support ?? 'vexahostcloudtech@gmail.com' }}
                                </p>
                            </div>
                        </div>
                        <div class="sm:text-right space-y-1">
                            <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400 font-bold">Tanggal Terbit</span>
                            <div class="text-xs font-bold text-zinc-900 dark:text-white">
                                {{ $invoice->created_at->translatedFormat('d F Y') }}
                            </div>
                            <div class="text-[11px] text-zinc-400 font-mono">
                                Status: <span class="font-bold text-zinc-700 dark:text-zinc-300">{{ $badge['label'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Client & Project Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-2">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400 font-bold block mb-1">Ditagihkan Kepada:</span>
                            <div class="text-sm font-bold text-zinc-900 dark:text-white">
                                {{ $invoice->client?->name ?? 'Klien Terhormat' }}
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 space-y-0.5">
                                <div>{{ $invoice->client?->email ?? '-' }}</div>
                                @if($invoice->client?->phone)
                                    <div>{{ $invoice->client->phone }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="sm:text-right">
                            <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400 font-bold block mb-1">Batas Waktu (Due Date):</span>
                            <div class="text-sm font-bold text-zinc-900 dark:text-white">
                                {{ $invoice->due_date ? $invoice->due_date->translatedFormat('d F Y') : now()->addDays(7)->translatedFormat('d F Y') }}
                            </div>
                            <span class="text-[11px] text-zinc-500 block mt-0.5">
                                Domisili: {{ $settings->domicile_city ?? 'Jakarta' }}, Banten
                            </span>
                        </div>
                    </div>

                    <!-- Financial Summary Table -->
                    <div class="pt-4">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[11px] font-mono text-zinc-400 uppercase tracking-wider border-b border-zinc-200/60 dark:border-zinc-800">
                                    <th class="py-2.5">Deskripsi Layanan / Proyek</th>
                                    <th class="py-2.5 text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                <tr>
                                    <td class="py-3.5">
                                        <div class="font-bold text-zinc-900 dark:text-white">{{ $invoice->project?->name ?? 'Proyek' }}</div>
                                        <div class="text-[11px] text-zinc-500 mt-0.5">{{ $invoice->title }}</div>
                                    </td>
                                    <td class="py-3.5 text-right font-semibold text-zinc-900 dark:text-white">
                                        Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="py-2.5 text-zinc-500">Telah Dibayar (Uang Muka / DP)</td>
                                    <td class="py-2.5 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                        - Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                                <tr class="border-t-2 border-zinc-900 dark:border-zinc-100 text-sm font-black">
                                    <td class="py-3 text-zinc-900 dark:text-white uppercase">Sisa Tagihan (Balance Due):</td>
                                    <td class="py-3 text-right {{ $invoice->balance_due > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                        Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>

                <!-- Section Below Table: Differentiated for Admin vs Client -->
                @if($isAdmin)
                    <!-- Admin View: Rekonsiliasi Kas Masuk & Akun Bank Perusahaan -->
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-5 sm:p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600">account_balance</span>
                                <span>Rekonsiliasi Kas Masuk &amp; Akun Bank Perusahaan</span>
                            </h3>
                            <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 font-bold text-zinc-600 dark:text-zinc-400">
                                Info Finance
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40 space-y-1.5">
                                <div class="text-[10px] font-mono uppercase text-zinc-400 font-bold">Rekening Penerima Kas Masuk</div>
                                <div class="font-bold text-zinc-900 dark:text-white text-sm">{{ $settings->bank_name ?? '-' }}</div>
                                <div class="font-mono font-bold text-zinc-800 dark:text-zinc-200">{{ $settings->bank_account_number ?? '-' }}</div>
                                <div class="text-[11px] text-zinc-500">a.n {{ $settings->bank_account_holder ?? '-' }}</div>
                            </div>

                            <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/70 dark:bg-zinc-800/40 space-y-1.5">
                                <div class="text-[10px] font-mono uppercase text-zinc-400 font-bold">Status Audit &amp; Verifikator</div>
                                <div class="font-bold text-zinc-900 dark:text-white text-sm flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] {{ $invoice->status === 'paid' ? 'text-emerald-500' : ($invoice->status === 'partially_paid' ? 'text-blue-500' : ($invoice->status === 'verifying' ? 'text-amber-500' : 'text-zinc-400')) }}">
                                        {{ $badge['icon'] }}
                                    </span>
                                    <span>{{ $badge['label'] }}</span>
                                </div>
                                <div class="text-[11px] text-zinc-600 dark:text-zinc-300">
                                    @if($invoice->status === 'paid')
                                        Lunas Terverifikasi &bull; Petugas: <strong>{{ $invoice->verifier?->name ?? 'Admin Finance' }}</strong>
                                    @elseif($invoice->status === 'partially_paid')
                                        DP Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }} Terverifikasi Sah &bull; Dicatat: <strong>{{ $invoice->verifier?->name ?? 'Admin Finance (CRM)' }}</strong>
                                    @elseif($invoice->status === 'verifying')
                                        <span class="text-amber-600 dark:text-amber-400 font-bold">Bukti Klien Menunggu Persetujuan</span>
                                    @else
                                        Menunggu Pembayaran Klien
                                    @endif
                                </div>
                                <div class="text-[10px] text-zinc-400 font-mono">
                                    @if($invoice->verified_at && in_array($invoice->status, ['paid', 'partially_paid']))
                                        Diverifikasi: {{ $invoice->verified_at->translatedFormat('d F Y, H:i') }} WIB
                                    @else
                                        Audit: Sistem Terintegrasi VexaHost Admin
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            <!-- Right Column: Differentiated Panel (Admin Desk vs Client Confirmation) -->
            <div class="space-y-6">
                
                @if($isAdmin)
                    <!-- ========================================== -->
                    <!-- ADMIN PERSPECTIVE: Finance Verification Box -->
                    <!-- ========================================== -->
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-5 space-y-5">
                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                            <h3 class="text-sm font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600">verified_user</span>
                                <span>Pusat Verifikasi Finance</span>
                            </h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-zinc-900 text-white dark:bg-white dark:text-zinc-900">
                                ADMIN
                            </span>
                        </div>

                        <!-- Info Klien / Pembayar -->
                        <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/40 space-y-2">
                            <div class="text-[10px] font-mono font-bold text-zinc-400 uppercase tracking-wider">Informasi Klien:</div>
                            <div class="text-xs font-bold text-zinc-900 dark:text-white">
                                {{ $invoice->client?->name ?? 'Klien' }}
                            </div>
                            <div class="text-[11px] text-zinc-500 space-y-0.5">
                                <div>Email: <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $invoice->client?->email ?? '-' }}</span></div>
                                <div>WA/Telp: <span class="font-mono text-zinc-700 dark:text-zinc-300">{{ $invoice->client?->phone ?? '-' }}</span></div>
                            </div>
                            @if(!empty($invoice->client?->phone))
                                <div class="pt-1">
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $invoice->client->phone) }}" target="_blank" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 hover:underline">
                                        <span class="material-symbols-outlined text-[15px]">chat</span>
                                        <span>Hubungi Klien via WhatsApp</span>
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Link Pembayaran Langsung (Tanpa Perlu Login) -->
                        @if($invoice->payment_token)
                            @php
                                $directPayUrl = route('invoices.pay', $invoice->payment_token);
                            @endphp
                            <div class="p-3.5 rounded-xl border border-emerald-200 dark:border-emerald-900/50 bg-emerald-50/70 dark:bg-emerald-950/30 space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-mono font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[15px]">bolt</span>
                                        Link Bayar Klien (Tanpa Login)
                                    </span>
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-emerald-200 dark:bg-emerald-900 text-emerald-900 dark:text-emerald-200">1-Klik</span>
                                </div>
                                <div class="text-[11px] text-zinc-600 dark:text-zinc-400">
                                    Kirim link ini ke chat WA klien agar langsung masuk ke halaman bayar &amp; QRIS/VA otomatis tanpa perlu login akun.
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <input type="text" readonly value="{{ $directPayUrl }}" id="directPayAdminInput" class="w-full text-xs font-mono bg-white dark:bg-zinc-900 border border-emerald-200 dark:border-zinc-700 rounded-lg px-2.5 py-1.5 text-zinc-800 dark:text-zinc-200 focus:outline-none select-all">
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $directPayUrl }}'); Swal.fire({toast:true,position:'top-end',icon:'success',title:'Link pembayaran disalin!',showConfirmButton:false,timer:2000})" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shrink-0 flex items-center gap-1 cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]">content_copy</span>
                                        <span>Salin</span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- Status Tagihan Saat Ini -->
                        @if($invoice->status === 'paid')
                            <div class="p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 flex items-start gap-2.5">
                                <span class="material-symbols-outlined text-[20px] text-emerald-600 shrink-0">check_circle</span>
                                <div>
                                    <span class="font-bold">Tagihan Telah Lunas Penuh</span>
                                    <p class="text-[11px] mt-0.5">Seluruh tagihan telah diverifikasi lunas. Kwitansi resmi telah aktif dan sah digunakan sebagai bukti pembukuan.</p>
                                </div>
                            </div>
                        @elseif($invoice->status === 'partially_paid')
                            <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-900 dark:text-blue-200 space-y-2">
                                <div class="font-bold flex items-center gap-1.5 text-blue-800 dark:text-blue-300">
                                    <span class="material-symbols-outlined text-[18px]">verified</span>
                                    <span>Uang Muka (DP) Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }} Sah &amp; Terverifikasi</span>
                                </div>
                                <p class="text-[11px] text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                    DP telah dicatat langsung di CRM oleh <strong>{{ $invoice->verifier?->name ?? 'Admin Finance' }}</strong>. Klien tidak perlu konfirmasi ulang untuk pembayaran ini.
                                </p>
                                <div class="pt-1 text-[11px] font-bold text-rose-600 dark:text-rose-400">
                                    Status Proyek: Menunggu Pelunasan Sisa Tagihan (Rp {{ number_format($invoice->balance_due, 0, ',', '.') }})
                                </div>
                                <div class="pt-2 border-t border-blue-200/80 dark:border-blue-800/60">
                                    <a href="{{ route('invoices.receipt', $invoice) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 dark:text-blue-300 hover:underline">
                                        <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                                        <span>Buka Lembar Kwitansi Resmi DP &rarr;</span>
                                    </a>
                                </div>
                            </div>
                        @elseif($invoice->status === 'verifying')
                            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5">
                                <span class="material-symbols-outlined text-[22px] text-amber-600 shrink-0">mark_email_unread</span>
                                <div>
                                    <span class="font-bold">🚨 Klien Telah Mengunggah Bukti Transfer!</span>
                                    <p class="text-[11px] mt-0.5">Silakan periksa mutasi rekening sebelum menyetujui pelunasan tagihan ini.</p>
                                </div>
                            </div>
                        @else
                            <div class="p-3.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs text-zinc-700 dark:text-zinc-300 flex items-start gap-2.5">
                                <span class="material-symbols-outlined text-[20px] text-zinc-400 shrink-0">pending</span>
                                <div>
                                    <span class="font-bold">Menunggu Pembayaran Klien</span>
                                    <p class="text-[11px] mt-0.5">Klien belum mengonfirmasi transfer atau mengunggah struk pembayaran melalui portal.</p>
                                </div>
                            </div>
                        @endif

                        <!-- Bukti Pembayaran Klien (Jika Ada) -->
                        @if($invoice->payment_proof)
                            <div class="p-3.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 space-y-2.5">
                                <span class="text-[10px] font-mono font-bold text-zinc-400 uppercase tracking-wider block">Struk / Bukti Transfer Klien:</span>
                                @php
                                    $isPdfProof = str_ends_with(strtolower($invoice->payment_proof), '.pdf');
                                @endphp
                                @if($isPdfProof)
                                    <a href="{{ Storage::url($invoice->payment_proof) }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-600 hover:underline">
                                        <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                                        <span>Buka Dokumen PDF Bukti Transfer</span>
                                    </a>
                                @else
                                    <a href="{{ Storage::url($invoice->payment_proof) }}" target="_blank" class="block group overflow-hidden rounded-lg border border-zinc-300 dark:border-zinc-700">
                                        <img src="{{ Storage::url($invoice->payment_proof) }}" alt="Bukti Transfer" class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-200">
                                    </a>
                                @endif
                                @if($invoice->payment_notes)
                                    <div class="text-[11px] text-zinc-600 dark:text-zinc-400 italic bg-white dark:bg-zinc-900 p-2 rounded border border-zinc-200 dark:border-zinc-800">
                                        Catatan Klien: "{{ $invoice->payment_notes }}"
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Aksi Tombol Admin -->
                        <div class="pt-2 border-t border-zinc-200/80 dark:border-zinc-800 space-y-2">
                            @if($invoice->status === 'verifying')
                                <!-- Ketika Klien Mengunggah Bukti: Verifikasi DP vs LUNAS -->
                                @php
                                    $defaultDp = $invoice->payment_amount_transferred ?: round($invoice->amount / 2);
                                @endphp
                                <div x-data="{ 
                                    verifyType: '{{ $invoice->payment_type === 'dp' ? 'dp' : ($invoice->paid_amount > 0 ? 'full' : 'dp') }}',
                                    dpAmount: {{ $defaultDp }},
                                    totalAmount: {{ $invoice->amount }},
                                    get balanceAfterDp() { return Math.max(0, this.totalAmount - this.dpAmount); }
                                }" class="space-y-3">
                                    <div class="text-xs font-bold text-zinc-900 dark:text-white flex items-center justify-between">
                                        <span>Tentukan Persetujuan Finance:</span>
                                        <span class="text-[10px] px-2 py-0.5 rounded font-bold uppercase {{ $invoice->payment_type === 'dp' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' }}">
                                            Klaim: {{ $invoice->payment_type === 'dp' ? 'Uang Muka (DP)' : 'Pelunasan Penuh' }}
                                        </span>
                                    </div>

                                    <!-- Mode Toggle -->
                                    <div class="grid grid-cols-2 gap-2 p-1 bg-zinc-100 dark:bg-zinc-800 rounded-xl">
                                        <button type="button" @click="verifyType = 'dp'" :class="verifyType === 'dp' ? 'bg-white dark:bg-zinc-700 text-blue-600 dark:text-blue-400 shadow-xs font-bold' : 'text-zinc-500 font-medium'" class="py-1.5 px-2 rounded-lg text-xs transition-all text-center">
                                            Setujui sbg DP
                                        </button>
                                        <button type="button" @click="verifyType = 'full'" :class="verifyType === 'full' ? 'bg-white dark:bg-zinc-700 text-emerald-600 dark:text-emerald-400 shadow-xs font-bold' : 'text-zinc-500 font-medium'" class="py-1.5 px-2 rounded-lg text-xs transition-all text-center">
                                            Setujui sbg LUNAS
                                        </button>
                                    </div>

                                    <!-- Mode A: Setujui sebagai DP -->
                                    <form x-show="verifyType === 'dp'" x-ref="formApproveDp" action="{{ route('invoices.verify', $invoice->id) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="action" value="approve_dp">
                                        <div>
                                            <label class="block text-[11px] font-medium text-zinc-500 mb-1">Nominal Uang Muka (DP) yang Diterima:</label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-zinc-400">Rp</span>
                                                <input type="number" name="dp_amount" x-model.number="dpAmount" step="any" min="0" class="w-full text-xs font-bold rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 pl-9 py-1.5 pr-3 focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        </div>

                                        <div class="p-2.5 rounded-lg bg-blue-50/70 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-800/40 text-[11px] space-y-1">
                                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                                <span>Total Proyek:</span>
                                                <span class="font-mono font-bold">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="flex justify-between text-blue-700 dark:text-blue-300 font-bold">
                                                <span>DP Diterima:</span>
                                                <span class="font-mono">Rp <span x-text="Number(dpAmount).toLocaleString('id-ID')"></span></span>
                                            </div>
                                            <div class="flex justify-between text-rose-600 dark:text-rose-400 font-bold pt-1 border-t border-blue-200 dark:border-blue-800">
                                                <span>Sisa Tagihan Pelunasan:</span>
                                                <span class="font-mono">Rp <span x-text="Number(balanceAfterDp).toLocaleString('id-ID')"></span></span>
                                            </div>
                                        </div>

                                        <button type="button" @click="VhSwal.confirm('Konfirmasi penerimaan Uang Muka (DP) ini? Status tagihan menjadi DP Diterima (Partially Paid) dan saldo sisa tagihan tetap aktif.', () => $refs.formApproveDp.submit())" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-xs">
                                            <span class="material-symbols-outlined text-[18px]">verified</span>
                                            <span>Konfirmasi Penerimaan DP</span>
                                        </button>
                                    </form>

                                    <!-- Mode B: Setujui sebagai LUNAS -->
                                    <form x-show="verifyType === 'full'" x-ref="formApproveFull" action="{{ route('invoices.verify', $invoice->id) }}" method="POST" class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="action" value="approve_full">
                                        <div class="p-2.5 rounded-lg bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-800/40 text-[11px] space-y-1">
                                            <div class="flex justify-between text-emerald-800 dark:text-emerald-300 font-bold">
                                                <span>Status Baru:</span>
                                                <span>LUNAS PENUH (100%)</span>
                                            </div>
                                            <div class="flex justify-between text-zinc-600 dark:text-zinc-400">
                                                <span>Sisa Tagihan:</span>
                                                <span class="font-mono font-bold text-emerald-600">Rp 0</span>
                                            </div>
                                        </div>
                                        <button type="button" @click="VhSwal.confirm('Konfirmasi tagihan ini sebagai LUNAS 100% dan kirim kwitansi lunas ke WhatsApp klien?', () => $refs.formApproveFull.submit())" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                                            <span class="material-symbols-outlined text-[18px]">task_alt</span>
                                            <span>Verifikasi Pembayaran LUNAS</span>
                                        </button>
                                    </form>

                                    <!-- Reject Form -->
                                    <form x-ref="formRejectProof" action="{{ route('invoices.verify', $invoice->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="button" @click="VhSwal.confirm('Tolak bukti transfer ini dan minta klien upload ulang?', () => $refs.formRejectProof.submit())" class="w-full inline-flex items-center justify-center gap-2 px-4 py-1.5 rounded-xl text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-bold transition-all">
                                            <span class="material-symbols-outlined text-[16px]">close</span>
                                            <span>Tolak Bukti Transfer</span>
                                        </button>
                                    </form>
                                </div>
                            @elseif($invoice->status === 'partially_paid')
                                <!-- Ketika DP Sudah Tercatat tapi Klien Belum Upload Pelunasan -->
                                <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 text-center space-y-2.5">
                                    <span class="material-symbols-outlined text-[22px] text-zinc-400 block mx-auto">schedule</span>
                                    <div class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Menunggu Pelunasan dari Klien</div>
                                    <p class="text-[11px] text-zinc-500">
                                        Tombol persetujuan akan otomatis aktif ketika klien mengunggah struk pelunasan (Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}) via portal.
                                    </p>
                                    <div class="pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                        <a href="{{ route('invoices.receipt', $invoice) }}" target="_blank" class="w-full py-2.5 px-3 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 hover:border-blue-500 text-blue-700 dark:text-blue-300 font-bold text-xs inline-flex items-center justify-center gap-2 transition-all shadow-xs">
                                            <span class="material-symbols-outlined text-[18px] text-blue-600">receipt_long</span>
                                            <span>Lihat Kwitansi Resmi DP (Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }})</span>
                                        </a>
                                    </div>
                                </div>

                                <form x-data x-ref="formManualSettlement" action="{{ route('invoices.verify', $invoice->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="button" @click="VhSwal.confirm('Tandai pelunasan sisa tagihan Rp {{ number_format($invoice->balance_due, 0, ',', '.') }} ini secara manual (misal klien bayar tunai/offline langsung) dan kirim kwitansi lunas ke WhatsApp klien?', () => $refs.formManualSettlement.submit())" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-all border border-zinc-200 dark:border-zinc-700">
                                        <span class="material-symbols-outlined text-[16px]">done_all</span>
                                        <span>Tandai Sisa Tagihan Lunas Manual (Offline)</span>
                                    </button>
                                </form>
                            @elseif($invoice->status === 'unpaid')
                                <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 text-center space-y-1">
                                    <span class="material-symbols-outlined text-[20px] text-zinc-400 block mx-auto">pending</span>
                                    <div class="text-xs font-bold text-zinc-800 dark:text-zinc-200">Klien Belum Mengirimkan Bukti</div>
                                    <p class="text-[11px] text-zinc-500">
                                        Menunggu klien melakukan transfer atau mengunggah bukti bayar melalui portal.
                                    </p>
                                </div>

                                <form x-data x-ref="formManualPaid" action="{{ route('invoices.verify', $invoice->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="action" value="approve">
                                    <button type="button" @click="VhSwal.confirm('Tandai tagihan Rp {{ number_format($invoice->amount, 0, ',', '.') }} ini LUNAS secara manual (misal klien bayar tunai langsung) dan kirim kwitansi lunas ke WhatsApp klien?', () => $refs.formManualPaid.submit())" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-semibold transition-all border border-zinc-200 dark:border-zinc-700">
                                        <span class="material-symbols-outlined text-[16px]">done_all</span>
                                        <span>Tandai Lunas Manual (Offline)</span>
                                    </button>
                                </form>
                            @elseif($invoice->status === 'paid')
                                <a href="{{ route('invoices.receipt', $invoice) }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-bold transition-all hover:bg-emerald-100">
                                    <span class="material-symbols-outlined text-[20px] text-emerald-600">receipt_long</span>
                                    <span>Lihat &amp; Cetak Kwitansi Resmi Pelunasan</span>
                                </a>
                            @endif
                        </div>
                    </div>

                @else
                    <!-- ========================================== -->
                    <!-- CLIENT PERSPECTIVE: Payment Confirmation   -->
                    <!-- ========================================== -->
                    <div class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-5 space-y-4">
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-white flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">verified</span>
                            <span>{{ $invoice->status === 'partially_paid' ? 'Pelunasan Sisa Tagihan' : 'Konfirmasi Pembayaran' }}</span>
                        </h3>

                        <!-- Status Banner Klien -->
                        @if($invoice->status === 'paid')
                            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 space-y-3">
                                <div class="flex items-start gap-2.5">
                                    <span class="material-symbols-outlined text-[22px] text-emerald-600 shrink-0">check_circle</span>
                                    <div>
                                        <span class="font-bold text-sm">Tagihan Sudah Lunas Penuh!</span>
                                        <p class="text-[11px] mt-0.5 text-zinc-600 dark:text-zinc-400">Terima kasih banyak atas pelunasan pembayaran Anda. Seluruh berkas pengerjaan proyek aktif berjalan.</p>
                                    </div>
                                </div>
                                <div class="pt-2 border-t border-emerald-200/80 dark:border-emerald-800/60">
                                    <a href="{{ route('invoices.receipt', $invoice) }}" target="_blank" class="w-full py-3 px-4 rounded-xl bg-[#BAFF39] hover:bg-[#a6ec27] text-zinc-950 font-black text-sm inline-flex items-center justify-center gap-2.5 shadow-md hover:shadow-lg transition-all active:scale-[0.98]">
                                        <span class="material-symbols-outlined text-[22px]">receipt_long</span>
                                        <span>Lihat &amp; Cetak Kwitansi Resmi Pelunasan</span>
                                    </a>
                                </div>
                            </div>
                        @elseif($invoice->status === 'partially_paid')
                            <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-xs text-blue-900 dark:text-blue-200 space-y-3">
                                <div class="flex items-start gap-2.5">
                                    <span class="material-symbols-outlined text-[22px] text-blue-600 shrink-0">payments</span>
                                    <div>
                                        <div class="font-bold text-sm">Uang Muka (DP) Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }} Sah &amp; Terverifikasi</div>
                                        <p class="text-[11px] mt-1 text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                            Pembayaran DP telah dinyatakan sah oleh Tim Finance. Anda <strong>tidak perlu mengonfirmasi DP ini lagi</strong>.
                                        </p>
                                        <div class="mt-2 text-rose-600 dark:text-rose-400 font-bold text-xs">
                                            Sisa Tagihan Pelunasan: Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Tombol Kwitansi Resmi DP (Besar & Jelas Terlihat) -->
                                <div class="pt-2.5 border-t border-blue-200/80 dark:border-blue-800/60">
                                    <a href="{{ route('invoices.receipt', $invoice) }}" target="_blank" class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm inline-flex items-center justify-center gap-2.5 shadow-md hover:shadow-lg transition-all active:scale-[0.98] cursor-pointer">
                                        <span class="material-symbols-outlined text-[24px]">receipt_long</span>
                                        <span>Lihat &amp; Cetak Kwitansi Resmi DP (Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }})</span>
                                    </a>
                                </div>
                            </div>
                        @elseif($invoice->status === 'verifying')
                            <div class="p-3.5 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2.5">
                                <span class="material-symbols-outlined text-[18px] text-amber-600 shrink-0">hourglass_top</span>
                                <div>
                                    <span class="font-bold">Sedang Diverifikasi</span>
                                    <p class="text-[11px] mt-0.5">Bukti transfer Anda telah diterima sistem dan sedang diperiksa oleh Tim Finance. Notifikasi WhatsApp akan terkirim segera setelah verifikasi selesai.</p>
                                </div>
                            </div>
                        @endif

                        <!-- Uploaded Proof Preview if Exists -->
                        @if($invoice->payment_proof)
                            <div class="p-3 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30 space-y-2">
                                <span class="text-[11px] font-mono font-bold text-zinc-400 uppercase tracking-wider block">Bukti Transfer Terunggah:</span>
                                @php
                                    $isPdfProof = str_ends_with(strtolower($invoice->payment_proof), '.pdf');
                                @endphp
                                @if($isPdfProof)
                                    <a href="{{ Storage::url($invoice->payment_proof) }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-600 hover:underline">
                                        <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                                        <span>Buka File Bukti PDF</span>
                                    </a>
                                @else
                                    <a href="{{ Storage::url($invoice->payment_proof) }}" target="_blank" class="block group overflow-hidden rounded-lg border border-zinc-300 dark:border-zinc-700">
                                        <img src="{{ Storage::url($invoice->payment_proof) }}" alt="Bukti Transfer" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-200">
                                    </a>
                                @endif
                                @if($invoice->payment_notes)
                                    <div class="text-[11px] text-zinc-600 dark:text-zinc-400 italic">
                                        Catatan: "{{ $invoice->payment_notes }}"
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Payment Section for Client (If not paid) -->
                        @if($invoice->status !== 'paid')
                            @php
                                $dpVal = round($invoice->amount / 2);
                                $fullVal = $invoice->status === 'partially_paid' ? $invoice->balance_due : $invoice->amount;
                            @endphp

                            <div x-data="{ 
                                payType: '{{ $invoice->status === 'partially_paid' ? 'full' : 'dp' }}',
                                channel: 'online_payment',
                                dpAmount: {{ $dpVal }},
                                fullAmount: {{ $fullVal }},
                                get currentAmount() {
                                    return this.payType === 'dp' ? this.dpAmount : this.fullAmount;
                                }
                            }" class="pt-2 border-t border-zinc-200/80 dark:border-zinc-800 space-y-4">

                                @if($invoice->payment_url)
                                    <!-- Banner Active Payment Link -->
                                    <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-[18px] text-emerald-600 dark:text-[#BAFF39]">bolt</span>
                                                Sesi Pembayaran Aktif
                                            </span>
                                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-[#BAFF39]/30 dark:bg-[#BAFF39]/20 text-zinc-900 dark:text-[#BAFF39] font-bold uppercase">
                                                {{ $invoice->payment_reference }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-zinc-600 dark:text-zinc-400">
                                            Anda memiliki tagihan pembayaran online aktif. Silakan selesaikan pembayaran melalui tautan pembayaran resmi di bawah ini:
                                        </p>
                                        <a href="{{ $invoice->payment_url }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-zinc-900 dark:bg-zinc-800 hover:bg-black border border-zinc-700 text-[#BAFF39] text-xs font-bold transition-all shadow-xs cursor-pointer">
                                            <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                                            <span>Lanjutkan Pembayaran Sekarang</span>
                                        </a>
                                    </div>
                                @endif

                                <!-- Form Bayar Otomatis Online -->
                                <form action="{{ route('invoices.pay-xendit', $invoice->id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="payment_type" :value="payType">
                                    <input type="hidden" name="channel" value="online_payment">

                                    <!-- 1. Pilihan Jenis Pembayaran (DP vs Pelunasan) -->
                                    @if($invoice->status !== 'partially_paid')
                                        <div>
                                            <label class="block text-[11px] font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">
                                                1. Pilih Nominal Pembayaran:
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button type="button" @click="payType = 'dp'" 
                                                        :class="payType === 'dp' ? 'border-emerald-600 dark:border-[#BAFF39] bg-emerald-50/70 dark:bg-emerald-950/30 font-bold ring-2 ring-emerald-500/20 dark:ring-[#BAFF39]/20' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400'" 
                                                        class="border rounded-xl p-2.5 text-left transition-all cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs text-zinc-900 dark:text-white">DP (50%)</span>
                                                        <span class="material-symbols-outlined text-[16px] text-emerald-600 dark:text-[#BAFF39]" x-show="payType === 'dp'">check_circle</span>
                                                    </div>
                                                    <div class="text-xs font-bold mt-1 text-zinc-900 dark:text-white">
                                                        Rp {{ number_format($dpVal, 0, ',', '.') }}
                                                    </div>
                                                    <div class="text-[10px] text-zinc-400 font-normal mt-0.5">Mulai pengerjaan</div>
                                                </button>

                                                <button type="button" @click="payType = 'full'" 
                                                        :class="payType === 'full' ? 'border-emerald-600 dark:border-[#BAFF39] bg-emerald-50/70 dark:bg-emerald-950/30 font-bold ring-2 ring-emerald-500/20 dark:ring-[#BAFF39]/20' : 'border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400'" 
                                                        class="border rounded-xl p-2.5 text-left transition-all cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs text-zinc-900 dark:text-white">Lunas (100%)</span>
                                                        <span class="material-symbols-outlined text-[16px] text-emerald-600 dark:text-[#BAFF39]" x-show="payType === 'full'">check_circle</span>
                                                    </div>
                                                    <div class="text-xs font-bold mt-1 text-zinc-900 dark:text-white">
                                                        Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                                    </div>
                                                    <div class="text-[10px] text-zinc-400 font-normal mt-0.5">Bayar penuh langsung</div>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                                            <div>
                                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">Jenis Pembayaran:</div>
                                                <div class="text-xs font-bold text-zinc-900 dark:text-white">Pelunasan Sisa Tagihan Proyek</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">Total Pelunasan:</div>
                                                <div class="text-sm font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Submit Button -->
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl bg-[#BAFF39] hover:bg-[#a6ec27] text-zinc-950 text-xs font-black transition-all shadow-md active:scale-[0.99] cursor-pointer">
                                        <span class="material-symbols-outlined text-[18px]">lock</span>
                                        <span>Bayar Sekarang &mdash; Rp <span x-text="Number(currentAmount).toLocaleString('id-ID')"></span></span>
                                    </button>
                                    <p class="text-[11px] text-center text-zinc-400 flex items-center justify-center gap-1">
                                        <span class="material-symbols-outlined text-[14px] text-emerald-600">verified_user</span>
                                        <span>Transaksi aman, instan &amp; langsung terverifikasi oleh sistem</span>
                                    </p>
                                </form>
                            </div>
                        @endif

                        <!-- Quick WhatsApp Link -->
                        <div class="pt-3 border-t border-zinc-200/80 dark:border-zinc-800 text-center">
                            <p class="text-[11px] text-zinc-400 mb-2">Butuh bantuan proses pembayaran?</p>
                            <a href="https://wa.me/{{ $waPhone }}?text={{ $waShareText }}" target="_blank" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all">
                                <span class="material-symbols-outlined text-[18px] text-emerald-600">send</span>
                                <span>Hubungi Admin via WhatsApp</span>
                            </a>
                        </div>
                    </div>
                @endif

            </div>

        </div>

    </div>
</x-app-layout>

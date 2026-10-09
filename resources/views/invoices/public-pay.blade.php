<!DOCTYPE html>
<html lang="id" class="bg-zinc-50 dark:bg-zinc-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Pembayaran Invoice #{{ $invoice->invoice_number }} &mdash; {{ $settings->brand_name ?? 'VexaHost' }}</title>
    @include('partials.favicon')

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script>
        // Inline theme check to prevent flickering (FOUC)
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased overflow-x-hidden relative min-h-screen flex flex-col justify-between bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 transition-colors duration-300 selection:bg-emerald-500 selection:text-white">

    <!-- Decorative background grid -->
    <div class="fixed inset-0 bg-grid-pattern pointer-events-none opacity-40 dark:opacity-20 z-0"></div>

    @include('landing.header')

    @php
        $badge = $invoice->status_badge;
        $clientName = $invoice->client?->name ?: ($invoice->project?->lead?->nama_kontak ?: ($invoice->subscription?->lead?->nama_kontak ?: ($invoice->project?->lead?->nama_usaha ?: 'Klien Terhormat')));
        $projectName = $invoice->project?->name ?: ($invoice->subscription?->lead?->nama_usaha ?: $invoice->title);
        $clientEmail = $invoice->client?->email ?: ($invoice->project?->lead?->email ?: ($invoice->subscription?->lead?->email ?: '-'));
        $clientPhone = $invoice->client?->phone ?: ($invoice->project?->lead?->kontak_wa ?: ($invoice->subscription?->lead?->kontak_wa ?: '-'));
        $logoUrl = $settings->logo_url ?: asset('images/logo.png');
    @endphp

    <!-- Main Content Area -->
    <main class="flex-1 w-full relative z-10 pt-24 sm:pt-28 pb-12 sm:pb-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto space-y-6">

            <!-- Top Page Bar: Title, Official Security Badge & Quick PDF Action -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-zinc-900 p-4 sm:p-5 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200/80 dark:border-zinc-700/80 flex items-center justify-center p-2 shrink-0">
                        <img src="{{ $logoUrl }}" alt="VexaHost Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm sm:text-base font-black tracking-tight text-zinc-900 dark:text-white">
                                Portal Pembayaran Resmi Vexa<span class="text-[#5c8a0f] dark:text-[#BAFF39]">Host</span>
                            </span>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">
                                <span class="material-symbols-outlined text-[13px] text-emerald-600 dark:text-emerald-400">verified_user</span>
                                <span>256-Bit SSL Secure</span>
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ $settings->company_name ?: 'PT DESTINARA CHAKRAWALA ARTHA' }} &bull; Verifikasi Otomatis Xendit Gateway
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                    <a href="{{ route('invoices.pay.pdf', $invoice->payment_token) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-bold transition-all">
                        <span class="material-symbols-outlined text-[17px] text-rose-500">picture_as_pdf</span>
                        <span>Unduh PDF Invoice</span>
                    </a>
                    @if($invoice->status === 'paid' || $invoice->status === 'partially_paid' || $invoice->paid_amount > 0)
                        <a href="{{ route('invoices.pay.receipt', $invoice->payment_token) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 hover:border-emerald-500 text-emerald-700 dark:text-emerald-300 text-xs font-bold transition-all">
                            <span class="material-symbols-outlined text-[17px] text-emerald-600">receipt_long</span>
                            <span>{{ $invoice->status === 'partially_paid' ? 'Unduh Kwitansi DP' : 'Unduh Kwitansi Lunas' }}</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Alert Notifications -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 rounded-2xl flex items-start gap-3 shadow-xs">
                    <span class="material-symbols-outlined text-[22px] text-emerald-600 shrink-0">check_circle</span>
                    <div class="text-xs">
                        <span class="font-bold block">Berhasil!</span>
                        <span class="mt-0.5 leading-relaxed">{{ session('success') }}</span>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 rounded-2xl flex items-start gap-3 shadow-xs">
                    <span class="material-symbols-outlined text-[22px] text-rose-600 shrink-0">error</span>
                    <div class="text-xs">
                        <span class="font-bold block">Perhatian!</span>
                        <span class="mt-0.5 leading-relaxed">{{ session('error') }}</span>
                    </div>
                </div>
            @endif
            @if(session('info'))
                <div class="p-4 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200 rounded-2xl flex items-start gap-3 shadow-xs">
                    <span class="material-symbols-outlined text-[22px] text-blue-600 shrink-0">info</span>
                    <div class="text-xs">
                        <span class="font-bold block">Informasi</span>
                        <span class="mt-0.5 leading-relaxed">{{ session('info') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main 2-Column Invoice & Checkout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                <!-- ========================================================= -->
                <!-- LEFT COLUMN (7 Cols): Official Invoice Document Sheet     -->
                <!-- ========================================================= -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs overflow-hidden">

                        <!-- Official Company Letterhead with Logo -->
                        <div class="p-6 sm:p-8 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-800/30">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-3">
                                        <x-vh-logo size="md" />
                                        <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded bg-emerald-100 dark:bg-zinc-800 text-emerald-800 dark:text-[#BAFF39] font-bold">
                                            Dokumen Resmi
                                        </span>
                                    </div>
                                    <div class="pt-1">
                                        <div class="font-extrabold text-zinc-900 dark:text-white text-sm sm:text-base tracking-tight leading-tight">
                                            {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}
                                        </div>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed mt-0.5">
                                            {{ $settings->tagline ?? 'Cloud Hosting, Sistem Informasi & Jasa Pembuatan Website' }}<br>
                                            WA: {{ $settings->phone_support ?? '0858-0874-9131' }} &bull; Email: {{ $settings->email_support ?? 'vexahostcloudtech@gmail.com' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="sm:text-right flex sm:flex-col items-center sm:items-end justify-between gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-zinc-200/60 dark:border-zinc-800">
                                    <div>
                                        <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400 font-bold block">Nomor Invoice</span>
                                        <h1 class="text-lg sm:text-xl font-black font-mono text-zinc-900 dark:text-white tracking-tight">
                                            {{ $invoice->invoice_number }}
                                        </h1>
                                    </div>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $badge['class'] }}">
                                        <span class="material-symbols-outlined text-[15px]">{{ $badge['icon'] }}</span>
                                        <span>{{ $badge['label'] }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Bill To & Dates -->
                        <div class="p-6 sm:p-8 space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/40 border border-zinc-200/70 dark:border-zinc-800">
                                <div>
                                    <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400 font-bold block mb-1">Ditagihkan Kepada:</span>
                                    <div class="text-sm font-bold text-zinc-900 dark:text-white">{{ $clientName }}</div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5 space-y-0.5">
                                        @if($clientEmail && $clientEmail !== '-')
                                            <div>{{ $clientEmail }}</div>
                                        @endif
                                        @if($clientPhone && $clientPhone !== '-')
                                            <div class="font-mono text-[11px]">{{ $clientPhone }}</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="sm:text-right">
                                    <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-400 font-bold block mb-1">Informasi Tanggal:</span>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-300">
                                        Terbit: <strong class="text-zinc-900 dark:text-white">{{ $invoice->created_at->translatedFormat('d F Y') }}</strong>
                                    </div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-300 mt-0.5">
                                        Jatuh Tempo: <strong class="text-rose-600 dark:text-rose-400">{{ $invoice->due_date ? $invoice->due_date->translatedFormat('d F Y') : now()->addDays(7)->translatedFormat('d F Y') }}</strong>
                                    </div>
                                    <div class="text-[11px] text-zinc-400 mt-0.5">
                                        Domisili: {{ $settings->domicile_city ?? 'Jakarta' }}, Indonesia
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Line Items & Calculation Table -->
                            <div>
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="text-[11px] font-mono text-zinc-400 uppercase tracking-wider border-b border-zinc-200 dark:border-zinc-800">
                                            <th class="py-2.5">Deskripsi Layanan / Proyek</th>
                                            <th class="py-2.5 text-right">Nominal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/60">
                                        <tr>
                                            <td class="py-4 pr-4">
                                                <div class="font-bold text-zinc-900 dark:text-white text-sm">{{ $projectName }}</div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">{{ $invoice->title }}</div>
                                            </td>
                                            <td class="py-4 text-right font-mono font-bold text-zinc-900 dark:text-white text-sm whitespace-nowrap">
                                                Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        @if($invoice->paid_amount > 0)
                                            <tr>
                                                <td class="py-3 text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                                    <span>Pembayaran Masuk (Uang Muka / DP Terverifikasi)</span>
                                                </td>
                                                <td class="py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap">
                                                    - Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endif
                                        <tr class="border-t-2 border-zinc-900 dark:border-zinc-100">
                                            <td class="pt-4 pb-1">
                                                <span class="text-xs font-black uppercase tracking-wider text-zinc-900 dark:text-white block">
                                                    {{ $invoice->status === 'paid' ? 'Total Terbayar Lunas' : 'Sisa Tagihan (Balance Due)' }}
                                                </span>
                                                <span class="text-[11px] text-zinc-400">
                                                    {{ $invoice->status === 'paid' ? 'Seluruh kewajiban pembayaran telah selesai' : 'Dapat dibayar DP 50% atau Lunas 100% di panel samping' }}
                                                </span>
                                            </td>
                                            <td class="pt-4 pb-1 text-right font-mono text-lg sm:text-xl font-black whitespace-nowrap {{ $invoice->status === 'paid' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                                Rp {{ number_format($invoice->status === 'paid' ? $invoice->amount : ($invoice->balance_due > 0 ? $invoice->balance_due : $invoice->amount), 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Bottom Invoice Sheet Footer -->
                        <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/40 border-t border-zinc-200/80 dark:border-zinc-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                            <span class="text-zinc-500 dark:text-zinc-400 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px] text-emerald-600 dark:text-emerald-400">verified</span>
                                <span>Kwitansi resmi bertanda tangan digital terbit otomatis setelah pembayaran.</span>
                            </span>
                            <a href="https://wa.me/{{ $waPhone }}?text={{ $waHelpText }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-[#BAFF39] hover:underline font-bold shrink-0">
                                <span class="material-symbols-outlined text-[16px]">chat</span>
                                <span>Hubungi Finance via WA</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- RIGHT COLUMN (5 Cols): Action / Payment Checkout Box      -->
                <!-- ========================================================= -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-6 space-y-5">

                        <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                    <span class="material-symbols-outlined text-[18px]">payments</span>
                                </span>
                                <div>
                                    <h2 class="text-sm font-black text-zinc-900 dark:text-white tracking-tight">
                                        {{ $invoice->status === 'paid' ? 'Bukti Pelunasan Resmi' : ($invoice->status === 'partially_paid' ? 'Pelunasan Sisa Tagihan' : 'Checkout Pembayaran Instan') }}
                                    </h2>
                                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Tanpa perlu login akun portal</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 uppercase">
                                INSTAN 24/7
                            </span>
                        </div>

                        <!-- ============================================== -->
                        <!-- STATUS CASE 1: PAID (LUNAS PENUH)              -->
                        <!-- ============================================== -->
                        @if($invoice->status === 'paid')
                            <div class="p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-center space-y-4">
                                <div class="w-14 h-14 rounded-2xl bg-[#BAFF39] text-zinc-950 flex items-center justify-center mx-auto shadow-lg shadow-[#BAFF39]/20">
                                    <span class="material-symbols-outlined text-[32px]">verified</span>
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-zinc-900 dark:text-white">
                                        Tagihan Telah Lunas Penuh!
                                    </h3>
                                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-1 leading-relaxed">
                                        Terima kasih! Seluruh pembayaran Anda telah diterima dan diverifikasi oleh sistem. Dokumen Kwitansi resmi bertanda tangan digital kini aktif.
                                    </p>
                                </div>

                                <div class="space-y-2.5 pt-2">
                                    <a href="{{ route('invoices.pay.receipt', $invoice->payment_token) }}" target="_blank"
                                       class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-[#BAFF39] hover:bg-[#a6ec27] text-zinc-950 text-xs font-black transition-all shadow-md active:scale-[0.99]">
                                        <span class="material-symbols-outlined text-[20px]">receipt_long</span>
                                        <span>Unduh Kwitansi Pelunasan Resmi (PDF)</span>
                                    </a>
                                    <a href="{{ route('invoices.pay.pdf', $invoice->payment_token) }}" target="_blank"
                                       class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-white dark:bg-zinc-800 hover:bg-zinc-100 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700 text-xs font-bold transition-all">
                                        <span class="material-symbols-outlined text-[18px] text-rose-500">picture_as_pdf</span>
                                        <span>Unduh Lembar Invoice (PDF)</span>
                                    </a>
                                </div>
                            </div>

                        <!-- ============================================== -->
                        <!-- STATUS CASE 2: VERIFYING (MENUNGGU VERIFIKASI) -->
                        <!-- ============================================== -->
                        @elseif($invoice->status === 'verifying')
                            <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-center space-y-3">
                                <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center mx-auto shadow-md shadow-amber-500/20">
                                    <span class="material-symbols-outlined text-[28px]">schedule</span>
                                </div>
                                <h3 class="text-sm font-bold text-amber-900 dark:text-amber-200">
                                    Bukti Pembayaran Sedang Diverifikasi
                                </h3>
                                <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                    Berkas bukti transfer Anda telah diterima dan sedang dicocokkan dengan mutasi rekening bank oleh Tim Finance. Notifikasi konfirmasi akan terkirim via WhatsApp segera setelah verifikasi selesai.
                                </p>
                            </div>

                        <!-- ============================================== -->
                        <!-- STATUS CASE 3: UNPAID / PARTIALLY_PAID         -->
                        <!-- ============================================== -->
                        @else

                            @if($invoice->status === 'partially_paid')
                                <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 space-y-2.5">
                                    <div class="flex items-start gap-2.5">
                                        <span class="material-symbols-outlined text-[20px] text-blue-600 dark:text-blue-400 shrink-0">verified</span>
                                        <div class="text-xs">
                                            <div class="font-bold text-blue-900 dark:text-blue-200">
                                                DP Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }} Telah Diterima Sah
                                            </div>
                                            <p class="text-zinc-600 dark:text-zinc-400 mt-0.5 leading-relaxed">
                                                Silakan selesaikan pelunasan sisa tagihan di bawah ini untuk serah terima penuh &amp; Go-Live proyek Anda.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="pt-2 border-t border-blue-200/70 dark:border-blue-800/60">
                                        <a href="{{ route('invoices.pay.receipt', $invoice->payment_token) }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 text-xs text-blue-700 dark:text-blue-300 font-bold hover:underline">
                                            <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                                            <span>Unduh Kwitansi Uang Muka (DP) &rarr;</span>
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <!-- Active Session Banner if already generated -->
                            @if($invoice->payment_url)
                                <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[18px] text-emerald-600 dark:text-[#BAFF39]">bolt</span>
                                            <span>Sesi Checkout Aktif</span>
                                        </span>
                                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-[#BAFF39]/30 dark:bg-[#BAFF39]/20 text-zinc-900 dark:text-[#BAFF39] font-bold uppercase">
                                            SIAP BAYAR
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-zinc-600 dark:text-zinc-400 leading-relaxed">
                                        Tautan pembayaran Xendit Anda sudah aktif. Klik tombol di bawah untuk langsung membuka halaman QRIS / Virtual Account:
                                    </p>
                                    <a href="{{ $invoice->payment_url }}" target="_blank"
                                       class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-zinc-900 dark:bg-zinc-800 hover:bg-black border border-zinc-700 text-[#BAFF39] text-xs font-bold transition-all shadow-xs">
                                        <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                                        <span>Buka Halaman Checkout Xendit Aktif</span>
                                    </a>
                                </div>
                            @endif

                            <!-- Form Pembayaran Otomatis Xendit -->
                            <div x-data="{
                                payType: '{{ $invoice->status === 'partially_paid' ? 'full' : 'dp' }}',
                                dpAmount: {{ $dpVal }},
                                fullAmount: {{ $fullVal }},
                                get currentAmount() {
                                    return this.payType === 'dp' ? this.dpAmount : this.fullAmount;
                                }
                            }" class="space-y-5">

                                <form action="{{ route('invoices.pay.process', $invoice->payment_token) }}" method="POST" class="space-y-5">
                                    @csrf
                                    <input type="hidden" name="payment_type" :value="payType">
                                    <input type="hidden" name="channel" value="online_payment">

                                    <!-- Option 1: Choose Nominal (DP 50% vs Lunas 100%) -->
                                    @if($invoice->status !== 'partially_paid')
                                        <div>
                                            <label class="block text-xs font-bold text-zinc-800 dark:text-zinc-200 mb-2">
                                                1. Pilih Opsi Nominal Pembayaran:
                                            </label>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <button type="button" @click="payType = 'dp'"
                                                        :class="payType === 'dp'
                                                            ? 'border-emerald-600 dark:border-[#BAFF39] bg-emerald-50/70 dark:bg-emerald-950/30 ring-2 ring-emerald-500/20 dark:ring-[#BAFF39]/20'
                                                            : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40 hover:border-zinc-300 dark:hover:border-zinc-700'"
                                                        class="border rounded-xl p-3.5 text-left transition-all cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-bold text-zinc-900 dark:text-white">Uang Muka (DP 50%)</span>
                                                        <span class="material-symbols-outlined text-[18px] text-emerald-600 dark:text-[#BAFF39]" x-show="payType === 'dp'">check_circle</span>
                                                    </div>
                                                    <div class="text-base font-black font-mono mt-1.5 text-zinc-900 dark:text-white">
                                                        Rp {{ number_format($dpVal, 0, ',', '.') }}
                                                    </div>
                                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Mulai pengerjaan proyek</div>
                                                </button>

                                                <button type="button" @click="payType = 'full'"
                                                        :class="payType === 'full'
                                                            ? 'border-emerald-600 dark:border-[#BAFF39] bg-emerald-50/70 dark:bg-emerald-950/30 ring-2 ring-emerald-500/20 dark:ring-[#BAFF39]/20'
                                                            : 'border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40 hover:border-zinc-300 dark:hover:border-zinc-700'"
                                                        class="border rounded-xl p-3.5 text-left transition-all cursor-pointer">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-xs font-bold text-zinc-900 dark:text-white">Lunas Penuh (100%)</span>
                                                        <span class="material-symbols-outlined text-[18px] text-emerald-600 dark:text-[#BAFF39]" x-show="payType === 'full'">check_circle</span>
                                                    </div>
                                                    <div class="text-base font-black font-mono mt-1.5 text-zinc-900 dark:text-white">
                                                        Rp {{ number_format($fullVal, 0, ',', '.') }}
                                                    </div>
                                                    <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">Bayar tuntas sekaligus</div>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 flex items-center justify-between">
                                            <div>
                                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">Jenis Tagihan:</div>
                                                <div class="text-xs font-bold text-zinc-900 dark:text-white">Pelunasan Akhir Proyek (Final Settlement)</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-[11px] text-zinc-500 dark:text-zinc-400">Nominal Pelunasan:</div>
                                                <div class="text-sm font-black font-mono text-rose-600 dark:text-rose-400">Rp {{ number_format($fullVal, 0, ',', '.') }}</div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Summary Nominal Box Before Pay -->
                                    <div class="p-4 rounded-xl bg-zinc-900 dark:bg-zinc-950 border border-zinc-800 text-white">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-zinc-200">Total Dibayarkan Sekarang:</span>
                                            <span class="text-lg font-black font-mono text-[#BAFF39]">
                                                Rp <span x-text="Number(currentAmount).toLocaleString('id-ID')"></span>
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Primary Submit Button (VexaHost Signature Style) -->
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center gap-2.5 px-6 py-4 rounded-xl bg-[#BAFF39] hover:bg-[#a6ec27] text-zinc-950 text-sm font-black transition-all shadow-md shadow-[#BAFF39]/15 active:scale-[0.99] cursor-pointer">
                                        <span class="material-symbols-outlined text-[20px]">lock</span>
                                        <span>Bayar Sekarang &mdash; Rp <span x-text="Number(currentAmount).toLocaleString('id-ID')"></span></span>
                                    </button>

                                    <p class="text-[11px] text-center text-zinc-500 dark:text-zinc-400 flex items-center justify-center gap-1.5">
                                        <span class="material-symbols-outlined text-[15px] text-emerald-600 dark:text-emerald-400">verified_user</span>
                                        <span>Verifikasi otomatis 24 jam &bull; Bebas unggah bukti manual</span>
                                    </p>
                                </form>
                            </div>
                        @endif

                    </div>
                </div>

            </div>

        </div>
    </main>

    @include('landing.footer')

</body>
</html>

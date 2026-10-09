<x-app-layout>
    <div class="space-y-6 w-full max-w-7xl mx-auto pb-12">

        <!-- Title & Info Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-zinc-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                        <span class="material-symbols-outlined text-[24px]">corporate_fare</span>
                    </span>
                    <span>Pengaturan Perusahaan &amp; Pembayaran</span>
                </h1>
                <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mt-1">
                    Kelola identitas resmi PT, nomor rekening bank, barcode QRIS, email kontak, serta nomor WhatsApp alert admin secara terpusat.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="$dispatch('open-quick-snippets')" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-400 border border-emerald-500/30 text-xs font-bold transition-all shadow-2xs">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">content_paste</span>
                    <span>Cek Template Chat WA</span>
                </button>
                <a href="#user-guide" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-300 text-xs font-bold transition-all shadow-2xs">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">menu_book</span>
                    <span>Lihat Panduan (User Guide)</span>
                </a>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 text-emerald-800 dark:text-emerald-300 rounded-xl flex items-start gap-3 shadow-xs">
                <span class="material-symbols-outlined text-[20px] text-emerald-600 dark:text-emerald-400 shrink-0">check_circle</span>
                <div>
                    <span class="font-bold text-xs">Berhasil Disimpan!</span>
                    <p class="text-xs mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif
        @if($errors->any())
            <div class="p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-rose-800 dark:text-rose-300 rounded-xl flex items-start gap-3 shadow-xs">
                <span class="material-symbols-outlined text-[20px] text-rose-600 dark:text-rose-400 shrink-0">error</span>
                <div>
                    <span class="font-bold text-xs">Terdapat Kesalahan Pengisian:</span>
                    <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.settings.company.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Identitas Legal Perusahaan & Penandatangan Dokumen -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-6 space-y-5">
                <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="material-symbols-outlined text-emerald-600">verified</span>
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Identitas Legal &amp; Pengesahan Dokumen</h2>
                        <p class="text-[11px] text-zinc-500">Nama resmi entitas, kota domisili tanda tangan, dan identitas pengesah dokumen faktur/kwitansi.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nama Legal Perusahaan (PT) *</label>
                        <input type="text" name="company_name" value="{{ old('company_name', $settings->company_name) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Tercetak di kop surat seluruh dokumen resmi.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nama Brand / Komersial *</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $settings->brand_name) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Contoh: VexaHost.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Tagline Bisnis</label>
                        <input type="text" name="tagline" value="{{ old('tagline', $settings->tagline) }}"
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Contoh: Software House &amp; Digital Solutions.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Kota Domisili Tanda Tangan *</label>
                        <input type="text" name="domicile_city" value="{{ old('domicile_city', $settings->domicile_city) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-1 block font-medium">Otomatis tertera di atas tanggal TTD: "Jakarta, [Tgl]"</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nama Direktur / Penandatangan *</label>
                        <input type="text" name="director_name" value="{{ old('director_name', $settings->director_name) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Nama yang tertera pada kolom garis tanda tangan.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Jabatan Penandatangan *</label>
                        <input type="text" name="director_title" value="{{ old('director_title', $settings->director_title) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Contoh: Finance &amp; Executive Director.</span>
                    </div>
                </div>
            </div>

            <!-- Section 2: Rekening Bank & Barcode QRIS Resmi -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-emerald-600">payments</span>
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Rekening Bank &amp; Profil Pembayaran Perusahaan</h2>
                            <p class="text-[11px] text-zinc-500">Informasi rekening resmi perusahaan dan arsip identitas visual pembayaran.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                        Automated Gateway: Xendit (.env) Aktif (QRIS, Mandiri VA, BNI VA)
                    </span>
                </div>

                <div class="p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/30 border border-blue-200/60 dark:border-blue-900/50 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-blue-600 dark:text-blue-400 text-[18px] shrink-0 mt-0.5">info</span>
                    <p class="text-[11px] text-blue-700 dark:text-blue-300 leading-relaxed">
                        <strong>Catatan Sistem:</strong> Seluruh transaksi invoice klien kini diproses secara otomatis via <strong>Xendit Payment Gateway</strong> (QRIS Instan, Mandiri VA, BNI VA) yang dikonfigurasi melalui <code>.env</code>. Formulir rekening &amp; gambar QRIS di bawah ini disimpan sebagai profil identitas resmi perusahaan dan cadangan administratif.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nama Bank *</label>
                        <input type="text" name="bank_name" value="{{ old('bank_name', $settings->bank_name) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Contoh: Bank Mandiri.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nomor Rekening *</label>
                        <input type="text" name="bank_account_number" value="{{ old('bank_account_number', $settings->bank_account_number) }}" required
                               class="w-full text-xs font-mono font-bold rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Contoh: 1234567890.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Atas Nama Pemilik Rekening *</label>
                        <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $settings->bank_account_holder) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Contoh: Nama pemilik rekening.</span>
                    </div>
                </div>

                <!-- Preview & Upload Box (QRIS, Logo & Signature) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 pt-2">
                    <!-- Upload QRIS -->
                    <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-950/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px] text-emerald-600">qr_code_2</span>
                                <span>Barcode QRIS Resmi</span>
                            </span>
                            <span class="text-[10px] font-mono text-zinc-400">JPG/PNG (Maks 4MB)</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="p-1.5 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-300 dark:border-zinc-700 shrink-0 shadow-2xs">
                                <img src="{{ $settings->qris_url }}" alt="QRIS Saat Ini" class="w-16 h-16 object-contain rounded">
                            </div>
                            <div class="flex-1 space-y-1.5">
                                <label class="block text-[11px] text-zinc-600 dark:text-zinc-400">Unggah QRIS Baru (Opsional):</label>
                                <input type="file" name="qris_image" accept=".jpg,.jpeg,.png,.webp"
                                       class="block w-full text-xs text-zinc-700 dark:text-zinc-300 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-zinc-200 dark:file:bg-zinc-800 hover:file:bg-zinc-300 cursor-pointer">
                                <p class="text-[10px] text-zinc-500">Tersimpan sebagai arsip identitas visual profil perusahaan. Pembayaran tagihan klien kini diproses otomatis via gateway Xendit.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Logo -->
                    <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-950/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px] text-emerald-600">image</span>
                                <span>Logo Perusahaan (Kop Surat)</span>
                            </span>
                            <span class="text-[10px] font-mono text-zinc-400">PNG/JPG (Maks 4MB)</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="p-2 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-300 dark:border-zinc-700 shrink-0 shadow-2xs flex items-center justify-center w-20 h-16">
                                <img src="{{ $settings->logo_url }}" alt="Logo Saat Ini" class="max-h-12 max-w-full object-contain">
                            </div>
                            <div class="flex-1 space-y-1.5">
                                <label class="block text-[11px] text-zinc-600 dark:text-zinc-400">Unggah Logo Baru (Opsional):</label>
                                <input type="file" name="logo_image" accept=".jpg,.jpeg,.png,.webp"
                                       class="block w-full text-xs text-zinc-700 dark:text-zinc-300 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-zinc-200 dark:file:bg-zinc-800 hover:file:bg-zinc-300 cursor-pointer">
                                <p class="text-[10px] text-zinc-500">Disarankan rasio horizontal dengan latar belakang transparan.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Tanda Tangan Digital -->
                    <div class="p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/60 dark:bg-zinc-950/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-zinc-900 dark:text-white flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px] text-emerald-600">draw</span>
                                <span>Tanda Tangan Digital Direktur</span>
                            </span>
                            <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400 font-bold">PNG Transparan</span>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="p-2 bg-white dark:bg-zinc-900 rounded-lg border border-zinc-300 dark:border-zinc-700 shrink-0 shadow-2xs flex items-center justify-center w-20 h-16">
                                @if($settings->signature_url)
                                    <img src="{{ $settings->signature_url }}" alt="Tanda Tangan" class="max-h-12 max-w-full object-contain">
                                @else
                                    <span class="text-[9px] text-zinc-400 text-center font-medium italic leading-tight">Belum Diunggah</span>
                                @endif
                            </div>
                            <div class="flex-1 space-y-1.5">
                                <label class="block text-[11px] text-zinc-600 dark:text-zinc-400">Unggah TTD Digital (Opsional):</label>
                                <input type="file" name="signature_image" accept=".png,.webp"
                                       class="block w-full text-xs text-zinc-700 dark:text-zinc-300 file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-zinc-200 dark:file:bg-zinc-800 hover:file:bg-zinc-300 cursor-pointer">
                                <p class="text-[10px] text-zinc-500">Format <strong>PNG transparan tanpa background</strong> untuk tercetak di atas tanda tangan dokumen.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Komunikasi, Email & WhatsApp Alert -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-6 space-y-5">
                <div class="flex items-center gap-2.5 pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="material-symbols-outlined text-emerald-600">hub</span>
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Alamat Kontak &amp; Tujuan Notifikasi Admin</h2>
                        <p class="text-[11px] text-zinc-500">Pemisahan email domain untuk tampilan publik dan tujuan email/WhatsApp internal untuk alert pemilik.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Email Support Publik (Domain) *</label>
                        <input type="email" name="email_support" value="{{ old('email_support', $settings->email_support) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-1 block">Tampil di dokumen klien: vexahostcloudtech@gmail.com</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Email Resmi Perusahaan *</label>
                        <input type="email" name="email_company" value="{{ old('email_company', $settings->email_company) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">vexahostcloudtech@gmail.com</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Email Tujuan Alert Internal Admin *</label>
                        <input type="email" name="email_internal_alert" value="{{ old('email_internal_alert', $settings->email_internal_alert) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-1 block font-medium">Tujuan notifikasi berdering di HP: vexahostcloudtech@gmail.com</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nomor WhatsApp Support 1 *</label>
                        <input type="text" name="phone_support" value="{{ old('phone_support', $settings->phone_support) }}" required
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Contoh: 0858-0874-9131</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nomor WhatsApp Support 2</label>
                        <input type="text" name="phone_support_2" value="{{ old('phone_support_2', $settings->phone_support_2) }}"
                               class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Contoh: 0812-3456-7890</span>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nomor WhatsApp Tujuan Alert Admin *</label>
                        <input type="text" name="phone_admin_alerts" value="{{ old('phone_admin_alerts', $settings->phone_admin_alerts) }}" required
                               class="w-full text-xs font-mono rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Pisahkan tanda koma (,) jika lebih dari satu nomor.</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Website Resmi</label>
                    <input type="text" name="website_url" value="{{ old('website_url', $settings->website_url) }}"
                           class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <!-- Section 4: Integrasi VexaHost WA Gateway -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-6 space-y-5">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2.5">
                        <span class="material-symbols-outlined text-emerald-600">settings_remote</span>
                        <div>
                            <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Integrasi VexaHost WA Gateway (Tanpa Butuh .ENV)</h2>
                            <p class="text-[11px] text-zinc-500">Kelola API Key VexaHost WA Gateway langsung dari halaman ini tanpa perlu membuka server atau file .env.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Database Driven (No .ENV)</span>
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- WA Gateway API Endpoint -->
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">WhatsApp Gateway API URL *</label>
                        <input type="text" name="wa_api_url" value="{{ old('wa_api_url', $settings->wa_api_url ?? 'https://wa.vexahostcloud.my.id/api/v1/messages/text') }}" required
                               class="w-full text-xs font-mono rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Endpoint pengiriman pesan teks &amp; dokumen PDF via VexaHost WA Gateway.</span>
                    </div>

                    <!-- WA Gateway API Key -->
                    <div x-data="{ showKey: false }">
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-xs font-bold text-zinc-700 dark:text-zinc-300">VexaHost WA API Key *</label>
                            <button type="button" @click="showKey = !showKey" class="text-[11px] text-emerald-600 hover:underline flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]" x-text="showKey ? 'visibility_off' : 'visibility'">visibility</span>
                                <span x-text="showKey ? 'Sembunyikan' : 'Tampilkan'">Tampilkan</span>
                            </button>
                        </div>
                        <input :type="showKey ? 'text' : 'password'" name="wa_api_key" value="{{ old('wa_api_key', $settings->wa_api_key) }}" required
                               class="w-full text-xs font-mono rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 mt-1 block">Dapat disalin dari dashboard wa.vexahostcloud.my.id ➡️ API Keys.</span>
                    </div>

                    <!-- Connected Sender Phone -->
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Nomor WhatsApp Pengirim Gateway (Terhubung)</label>
                        <input type="text" name="wa_sender_phone" value="{{ old('wa_sender_phone', $settings->wa_sender_phone ?? '0858-0874-9131') }}"
                               class="w-full text-xs font-mono rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                        <span class="text-[10px] text-zinc-400 mt-1 block">Nomor yang sedang ditautkan pada sesi aktif di VexaHost WA Gateway.</span>
                    </div>

                </div>
            </div>

            <!-- Section 5: Syarat & Ketentuan Faktur Tagihan -->
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-xs p-6 space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-zinc-100 dark:border-zinc-800">
                    <span class="material-symbols-outlined text-emerald-600">gavel</span>
                    <div>
                        <h2 class="text-sm font-bold text-zinc-900 dark:text-white">Syarat &amp; Ketentuan Default Dokumen</h2>
                        <p class="text-[11px] text-zinc-500">Poin-poin hukum dan ketentuan penagihan yang tercetak di bagian bawah lembar invoice PDF.</p>
                    </div>
                </div>

                <div>
                    <textarea name="invoice_terms" rows="4"
                              class="w-full text-xs rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white p-3 focus:ring-emerald-500 focus:border-emerald-500 leading-relaxed">{{ old('invoice_terms', $settings->invoice_terms) }}</textarea>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-between p-4 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 shadow-sm">
                <div class="text-xs text-zinc-500 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">sync_saved_locally</span>
                    <span>Perubahan akan langsung berlaku pada seluruh dokumen PDF, WA gateway, dan Client Panel.</span>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs hover:shadow active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>

        <!-- Mini-Form: Uji Coba WhatsApp Gateway Langsung (Test Ping) -->
        <div class="bg-gradient-to-r from-emerald-500/10 via-zinc-50 dark:via-zinc-900 to-transparent p-6 rounded-2xl border border-emerald-500/30 shadow-xs space-y-3">
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-emerald-600 text-white shadow-2xs">
                    <span class="material-symbols-outlined text-[20px]">send_to_mobile</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-zinc-900 dark:text-white">Uji Coba Langsung Koneksi WhatsApp Gateway</h3>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Kirim pesan tes (ping) ke nomor WhatsApp Anda untuk memastikan VexaHost WA Gateway berfungsi normal tanpa perlu buat data uji coba.</p>
                </div>
            </div>

            <form action="{{ route('admin.settings.company.test-wa') }}" method="POST" class="flex flex-col sm:flex-row items-center gap-3 pt-2">
                @csrf
                <div class="relative w-full sm:w-80">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-zinc-400 text-[18px]">phone_android</span>
                    <input type="text" name="test_phone" value="{{ old('test_phone', $settings->phone_support ?: '085808749131') }}" required placeholder="Contoh: 085808749131"
                           class="w-full pl-10 pr-3 py-2 text-xs font-mono rounded-xl border-zinc-200 dark:border-zinc-800 dark:bg-zinc-950 text-zinc-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    <span>Kirim Pesan Uji Coba (Test Ping)</span>
                </button>
            </form>
        </div>

        <!-- ========================================================================= -->
        <!-- USER GUIDE / PANDUAN PENGGUNAAN LENGKAP -->
        <!-- ========================================================================= -->
        <div id="user-guide" class="mt-12 pt-8 border-t border-zinc-200 dark:border-zinc-800 space-y-6">
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                    <span class="material-symbols-outlined text-[24px]">menu_book</span>
                </span>
                <div>
                    <h2 class="text-xl font-extrabold text-zinc-900 dark:text-white tracking-tight">
                        Panduan Pengguna (User Guide) &amp; FAQ
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                        Petunjuk teknis dan tanya jawab operasional untuk memudahkan Anda mengelola data perusahaan, QRIS, dan rekening bank.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                
                <!-- Panduan 1: Upload QRIS -->
                <div class="p-5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs space-y-2.5">
                    <div class="flex items-center gap-2 text-emerald-600 font-bold text-xs">
                        <span class="material-symbols-outlined text-[20px]">qr_code_scanner</span>
                        <span>1. Cara Upload &amp; Menyiapkan Gambar QRIS Baru</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-zinc-600 dark:text-zinc-400 space-y-1.5 leading-relaxed">
                        <li><strong>Resolusi yang Disarankan:</strong> Gunakan gambar persegi (rasio 1:1), dengan ukuran minimal <strong>500 x 500 pixel</strong> hingga 1080 x 1080 pixel agar tajam saat dicetak dalam file PDF.</li>
                        <li><strong>Tips Crop:</strong> Jika Anda mengunduh QRIS dari BCA Merchant, ShopeePay, atau Nobu, <em>crop</em> (potong) hanya bagian kotak barcode hitam-putihnya saja beserta logo QRIS di tengahnya.</li>
                        <li><strong>Format File:</strong> Format yang didukung adalah <code>.JPG</code>, <code>.PNG</code>, atau <code>.WEBP</code> dengan ukuran file di bawah 4MB.</li>
                        <li><strong>Otomatisasi:</strong> Begitu gambar QRIS diunggah dan disimpan, semua dokumen Invoice Proyek, Invoice Maintenance, serta halaman bayar di Client Panel akan seketika memakai QRIS terbaru tanpa perlu setting ulang.</li>
                    </ul>
                </div>

                <!-- Panduan 2: Ganti Rekening Bank -->
                <div class="p-5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs space-y-2.5">
                    <div class="flex items-center gap-2 text-blue-600 font-bold text-xs">
                        <span class="material-symbols-outlined text-[20px]">account_balance</span>
                        <span>2. Tata Cara Mengganti Rekening Pembayaran</span>
                    </div>
                    <ul class="list-disc list-inside text-xs text-zinc-600 dark:text-zinc-400 space-y-1.5 leading-relaxed">
                        <li>Cukup ubah kolom <strong>Nama Bank</strong>, <strong>Nomor Rekening</strong>, dan <strong>Atas Nama Pemilik Rekening</strong> pada form di atas.</li>
                        <li>Data rekening ini langsung otomatis tercetak di:
                            <ol class="list-decimal list-inside pl-4 space-y-0.5 text-zinc-500 mt-1">
                                <li>Lembar PDF Invoice Penagihan Proyek.</li>
                                <li>Lembar PDF Invoice Perpanjangan Maintenance Server.</li>
                                <li>Teks pesan (caption) otomatis yang dikirim ke WhatsApp klien via VexaHost WA Gateway.</li>
                                <li>Box rekening pada Client Panel tempat klien melihat tagihan mereka.</li>
                                <li>Seluruh format Template Chat WhatsApp di menu <strong>Template Chat WA</strong> (Pricelist, Brief, Rekening &amp; DP, Tagihan Pelunasan, Maintenance).</li>
                            </ol>
                        </li>
                    </ul>
                </div>

                <!-- Panduan 3: Nomor WhatsApp Gateway -->
                <div class="p-5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs space-y-2.5">
                    <div class="flex items-center gap-2 text-purple-600 font-bold text-xs">
                        <span class="material-symbols-outlined text-[20px]">mark_chat_read</span>
                        <span>3. Bagaimana Cara Mengganti Nomor WhatsApp Gateway?</span>
                    </div>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        Nomor WhatsApp yang digunakan untuk <em>mengirim</em> pesan ke klien dikendalikan oleh sesi di dashboard <strong>VexaHost WA Gateway</strong> (<a href="https://wa.vexahostcloud.my.id" target="_blank" class="text-emerald-600 underline font-semibold">wa.vexahostcloud.my.id</a>).
                    </p>
                    <div class="p-3 rounded-xl bg-zinc-50 dark:bg-zinc-950 text-xs text-zinc-600 dark:text-zinc-400 space-y-1">
                        <span class="font-bold text-zinc-800 dark:text-zinc-200">Jika suatu saat nomor resmi VexaHost berganti:</span>
                        <p>1. Buka <code>wa.vexahostcloud.my.id</code> ➡️ pilih sesi ➡️ klik <strong>Putuskan Sesi / Relink</strong>.</p>
                        <p>2. Scan kode QR WA Gateway menggunakan nomor WhatsApp baru Anda.</p>
                        <p>3. <strong>Selesai!</strong> Admin Panel & Client Panel otomatis mengirim memakai nomor baru tanpa perlu mengubah kode program apa pun.</p>
                    </div>
                </div>

                <!-- Panduan 4: Email Domain vs Gmail -->
                <div class="p-5 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-xs space-y-2.5">
                    <div class="flex items-center gap-2 text-amber-600 font-bold text-xs">
                        <span class="material-symbols-outlined text-[20px]">alternate_email</span>
                        <span>4. Penjelasan Email Support Domain vs Gmail Notifikasi</span>
                    </div>
                    <div class="text-xs text-zinc-600 dark:text-zinc-400 space-y-2 leading-relaxed">
                        <p>
                            <strong>Email Support Publik:</strong> (<code>vexahostcloudtech@gmail.com</code>) adalah email domain resmi yang dilihat klien pada dokumen invoice dan portal agar citra perusahaan Anda terlihat kredibel dan profesional.
                        </p>
                        <p>
                            <strong>Email Notifikasi Internal:</strong> (<code>vexahostcloudtech@gmail.com</code>) adalah email tujuan untuk sistem mengirimkan salinan alert saat ada tiket bantuan baru atau bukti transfer yang diunggah klien, sehingga smartphone Anda langsung berdering seketika.
                        </p>
                    </div>
                </div>

                <!-- Panduan 5: Bebas dari File .ENV -->
                <div class="md:col-span-2 p-5 rounded-2xl border border-emerald-500/30 bg-emerald-50/20 dark:bg-emerald-950/20 shadow-xs space-y-2.5">
                    <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-bold text-xs">
                        <span class="material-symbols-outlined text-[20px]">phonelink_setup</span>
                        <span>5. Sistem Bebas File .ENV — Semua Terkelola Terpusat di Halaman Web Ini</span>
                    </div>
                    <div class="text-xs text-zinc-600 dark:text-zinc-400 space-y-2 leading-relaxed">
                        <p>
                            Anda <strong>tidak perlu lagi membuka server atau mengedit file <code>.env</code></strong> secara manual setiap kali ingin memperbarui:
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px] font-medium text-zinc-700 dark:text-zinc-300">
                            <div class="p-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
                                ✔ Nomor Rekening &amp; Bank BCA
                            </div>
                            <div class="p-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
                                ✔ Gambar &amp; Barcode Scan QRIS
                            </div>
                            <div class="p-2.5 rounded-xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800">
                                ✔ VexaHost WA Gateway API Key
                            </div>
                        </div>
                        <p class="text-[11px] text-zinc-500">
                            Setiap Anda menekan tombol <strong>"Simpan Pengaturan"</strong>, seluruh perubahan disimpan ke basis data secara permanen dan otomatis dibaca oleh modul pembuatan dokumen PDF, pengirim WhatsApp otomatis, serta Client Panel.
                        </p>
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>

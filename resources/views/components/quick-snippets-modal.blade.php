@php
    $settings = \App\Models\CompanySetting::get();
    $brand = $settings->brand_name ?: 'VexaHost';
    $company = $settings->company_name ?: 'PT DESTINARA CHAKRAWALA ARTHA';
    $bankName = $settings->bank_name ?: '-';
    $bankNo = $settings->bank_account_number ?: '-';
    $bankHolder = $settings->bank_account_holder ?: '-';
    $phone1 = $settings->phone_support ?: '0858-0874-9131';
    $phone2 = $settings->phone_support_2;
    $email = $settings->email_support ?: 'vexahostcloudtech@gmail.com';
    $website = $settings->website_url ?: 'https://vexahostcloud.my.id';
    $city = $settings->domicile_city ?: 'Jakarta';
    $portalUrl = config('app.url', 'https://client.vexahostcloud.my.id');
@endphp

<div x-data="{
    openSnippetsModal: false,
    activeTab: 'pricelist',
    copiedSnippet: null,
    copyToClipboard(text, id) {
        if (!text) return;
        navigator.clipboard.writeText(text.trim()).then(() => {
            this.copiedSnippet = id;
            if (window.showToast) {
                window.showToast('Template teks berhasil disalin ke clipboard!', 'success');
            }
            setTimeout(() => { this.copiedSnippet = null; }, 2000);
        }).catch(err => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text.trim();
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            this.copiedSnippet = id;
            if (window.showToast) {
                window.showToast('Template teks berhasil disalin!', 'success');
            }
            setTimeout(() => { this.copiedSnippet = null; }, 2000);
        });
    }
}"
@open-quick-snippets.window="openSnippetsModal = true">

    <!-- Modal Backdrop & Window -->
    <div x-show="openSnippetsModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-zinc-950/70 backdrop-blur-sm"
         style="display: none;">

        <div @click.away="openSnippetsModal = false"
             class="bg-white dark:bg-zinc-900 w-full max-w-3xl rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl p-5 sm:p-7 space-y-5 max-h-[90vh] flex flex-col">

            <!-- Modal Header -->
            <div class="flex items-start justify-between border-b border-zinc-100 dark:border-zinc-800 pb-4 shrink-0">
                <div class="flex items-start gap-3">
                    <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5">
                        <span class="material-symbols-outlined text-[22px]">content_paste</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-extrabold text-zinc-900 dark:text-white text-base sm:text-lg">Template Chat WhatsApp Resmi</h3>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Sinkron Pengaturan Perusahaan
                            </span>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 leading-relaxed">
                            Format percakapan resmi 1-klik untuk komunikasi instan. Seluruh identitas brand, kontak resmi, dan metode pembayaran otomatis (Xendit Payment Gateway) telah disesuaikan dengan <strong>Pengaturan Perusahaan</strong>.
                        </p>
                    </div>
                </div>
                <button @click="openSnippetsModal = false" class="p-1.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-white rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                    <span class="material-symbols-outlined text-[20px] block">close</span>
                </button>
            </div>

            <!-- Tab Buttons (Horizontal Scrollable) -->
            <div class="flex items-center gap-1.5 border-b border-zinc-100 dark:border-zinc-800 pb-2.5 overflow-x-auto text-xs font-bold shrink-0 custom-scrollbar">
                <button @click="activeTab = 'pricelist'" :class="activeTab === 'pricelist' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'" class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                    <span>💰</span>
                    <span>Pricelist &amp; Paket</span>
                </button>
                <button @click="activeTab = 'brief'" :class="activeTab === 'brief' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'" class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                    <span>📋</span>
                    <span>Brief Kebutuhan</span>
                </button>
                <button @click="activeTab = 'rekening'" :class="activeTab === 'rekening' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'" class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                    <span>⚡</span>
                    <span>Metode Bayar &amp; DP</span>
                </button>
                <button @click="activeTab = 'dp_diterima'" :class="activeTab === 'dp_diterima' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'" class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                    <span>🚀</span>
                    <span>Konfirmasi DP</span>
                </button>
                <button @click="activeTab = 'review'" :class="activeTab === 'review' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'" class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                    <span>🔍</span>
                    <span>Review &amp; Demo</span>
                </button>
                <button @click="activeTab = 'settlement'" :class="activeTab === 'settlement' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'" class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                    <span>💳</span>
                    <span>Tagihan Pelunasan</span>
                </button>
                <button @click="activeTab = 'selesai'" :class="activeTab === 'selesai' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'" class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                    <span>🎉</span>
                    <span>Selesai &amp; Live</span>
                </button>
                <button @click="activeTab = 'maintenance'" :class="activeTab === 'maintenance' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'" class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                    <span>🛡️</span>
                    <span>Maintenance</span>
                </button>
                <button @click="activeTab = 'kontak'" :class="activeTab === 'kontak' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-zinc-200 dark:hover:bg-zinc-700'" class="px-3 py-1.5 rounded-xl transition-all whitespace-nowrap flex items-center gap-1">
                    <span>📞</span>
                    <span>Kontak Resmi</span>
                </button>
            </div>

            <!-- Content Container (Scrollable) -->
            <div class="flex-1 overflow-y-auto custom-scrollbar space-y-4 pr-1">

                <!-- TAB 1: PRICELIST & PAKET -->
                <div x-show="activeTab === 'pricelist'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Template Penawaran &amp; Daftar Harga Paket Layanan</span>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">{{ $brand }}</span>
                    </div>
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 font-mono text-xs text-zinc-800 dark:text-zinc-200 whitespace-pre-line leading-relaxed selection:bg-emerald-500/20">
Halo Kak! Terima kasih sudah menghubungi *{{ $brand }}* 🙏

Berikut pilihan paket pembuatan website profesional & sistem digital kami:

🚀 *1. Landing Page UMKM / Sales Funnel — Rp 499.000*
• 1 Halaman Promosi / Sales Page Konversi Tinggi
• Desain Modern Responsif (Mobile & Desktop)
• Tombol Direct WhatsApp & Integrasi Google Maps
• Gratis Domain .my.id / .biz.id & Cloud Hosting 1 Tahun

💼 *2. Company Profile Bisnis — Rp 999.000*
• Hingga 5 Halaman Lengkap (Beranda, Profil, Layanan, Portofolio/Galeri, Kontak)
• Setup SEO Dasar & Pendaftaran Google Indexing
• Integrasi Form Kontak & Email Bisnis Berdomain Resmi
• Gratis Domain .com & Cloud Hosting 1 Tahun

🛒 *3. Toko Online E-Commerce & POS Kasir — Rp 1.500.000*
• Katalog Produk, Keranjang Belanja & Manajemen Pesanan
• Fitur Cetak Struk Bluetooth & Laporan Penjualan Otomatis
• Notifikasi WhatsApp Transaksi ke Pembeli & Admin
• Panduan & Pelatihan Penggunaan Lengkap

⚡ *4. Custom Web App / Sistem Informasi Khusus*
• Dashboard Admin, Manajemen Database, Multi-User Role, & API Integrasi
• Estimasi biaya disesuaikan dengan modul & kebutuhan sistem

🌐 Portofolio & Info: {{ $website }}
💬 Konsultasi Langsung: {{ $phone1 }}

Ada paket yang paling sesuai dengan target bisnis Kakak saat ini? Silakan ceritakan kebutuhan Anda ya! 😊
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-zinc-400">Klik tombol salin untuk langsung paste di chat WhatsApp klien.</span>
                        <button @click="copyToClipboard($el.closest('.space-y-4').querySelector('.font-mono').innerText, 'pricelist')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="copiedSnippet === 'pricelist' ? 'check' : 'content_copy'"></span>
                            <span x-text="copiedSnippet === 'pricelist' ? 'Tersalin!' : 'Salin Template Teks'"></span>
                        </button>
                    </div>
                </div>

                <!-- TAB 2: BRIEF FORM -->
                <div x-show="activeTab === 'brief'" class="space-y-4" style="display: none;">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Template Form Brief Awal Pengerjaan</span>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">{{ $brand }}</span>
                    </div>
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 font-mono text-xs text-zinc-800 dark:text-zinc-200 whitespace-pre-line leading-relaxed selection:bg-emerald-500/20">
Halo Kak! Untuk memulai proses perancangan dan pengerjaan website, mohon bantu melengkapi data singkat berikut ya:

*FORM BRIEF KEBUTUHAN PROYEK — {{ strtoupper($brand) }}*
1. Nama Usaha / Brand:
2. Bidang Usaha / Industri:
3. Paket Website / Sistem yang Dipilih:
4. Pilihan Nama Domain yang Diinginkan (cth: namabisnis.com):
5. Referensi Website yang Disukai (jika ada contoh/benchmark):
6. Pilihan Warna Dominan / Ciri Khas Brand:
7. Kontak Resmi (No. WhatsApp / Email / Alamat Bisnis):
8. Materi Logo & Foto Produk (bisa kirim via chat ini atau link Google Drive):

Jika ada pertanyaan atau butuh panduan dalam pengisian, tim kami siap membantu via WhatsApp ({{ $phone1 }}). Terima kasih banyak! 🙏
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-zinc-400">Membantu calon klien memberikan spesifikasi lengkap.</span>
                        <button @click="copyToClipboard($el.closest('.space-y-4').querySelector('.font-mono').innerText, 'brief')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="copiedSnippet === 'brief' ? 'check' : 'content_copy'"></span>
                            <span x-text="copiedSnippet === 'brief' ? 'Tersalin!' : 'Salin Template Teks'"></span>
                        </button>
                    </div>
                </div>

                <!-- TAB 3: METODE PEMBAYARAN & DP (OTOMATIS XENDIT) -->
                <div x-show="activeTab === 'rekening'" class="space-y-4" style="display: none;">
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
                            <span class="material-symbols-outlined text-[18px]">verified</span>
                            <span>Payment Gateway Resmi: <strong>QRIS Semua Bank &amp; Virtual Account Mandiri / BNI</strong> (Verifikasi Otomatis 24 Jam)</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 font-mono text-xs text-zinc-800 dark:text-zinc-200 whitespace-pre-line leading-relaxed selection:bg-emerald-500/20">
Halo Kak, untuk pembayaran pengerjaan proyek di *{{ $brand }}* ({{ $company }}), seluruh transaksi dilakukan secara resmi dan otomatis melalui *Link Pembayaran Invoice Resmi* kami:

✨ *Metode Pembayaran Otomatis (Aktif 24 Jam):*
• *QRIS Instan:* Mendukung seluruh M-Banking & E-Wallet (BCA, Livin' Mandiri, BRImo, BNI, GoPay, OVO, Dana, ShopeePay)
• *Virtual Account Bank:* Bank Mandiri & Bank BNI

📌 *Ketentuan Pembayaran:*
• Pembayaran Uang Muka (DP) minimal 50% (atau Lunas 100%) langsung melalui link tagihan resmi untuk memulai tahap pengerjaan.
• Pelunasan 50% diselesaikan setelah pengerjaan selesai direview dan sistem siap diluncurkan online (*LIVE*).
• Verifikasi pembayaran berlangsung *otomatis real-time* tanpa perlu kirim bukti transfer manual, dan *Kwitansi Resmi Digital* langsung terbit seketika.

Jika membutuhkan bantuan terkait link pembayaran, silakan hubungi WhatsApp kami ({{ $phone1 }}). Terima kasih banyak atas kepercayaannya! 🙏✨
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-zinc-400">Template informasi metode pembayaran online resmi ke klien.</span>
                        <button @click="copyToClipboard($el.closest('.space-y-4').querySelector('.font-mono').innerText, 'rekening')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="copiedSnippet === 'rekening' ? 'check' : 'content_copy'"></span>
                            <span x-text="copiedSnippet === 'rekening' ? 'Tersalin!' : 'Salin Template Teks'"></span>
                        </button>
                    </div>
                </div>

                <!-- TAB 4: KONFIRMASI DP DITERIMA & PENGERJAAN DIMULAI -->
                <div x-show="activeTab === 'dp_diterima'" class="space-y-4" style="display: none;">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Template Konfirmasi DP Masuk &amp; Mulai Antrean Pengerjaan</span>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">{{ $brand }}</span>
                    </div>
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 font-mono text-xs text-zinc-800 dark:text-zinc-200 whitespace-pre-line leading-relaxed selection:bg-emerald-500/20">
Halo Kak [Nama Kontak], pembayaran DP untuk project *[Nama Paket]* ([Nama Project]) sudah kami terima dan terverifikasi otomatis oleh sistem. 🙏

Project langsung kami masukkan ke antrean pengerjaan teknis ya. Estimasi selesai pengerjaan sekitar *[7-14 hari kerja]* ke depan.

Nanti setiap progres penting akan kami update berkala. Kakak juga dapat memantau progres tahapan pengerjaan secara langsung melalui *Client Panel*:
🔗 {{ $portalUrl }}

Terima kasih banyak atas kepercayaannya bersama *{{ $brand }}*! ✨
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-zinc-400">Kirim setelah pembayaran DP terverifikasi di sistem.</span>
                        <button @click="copyToClipboard($el.closest('.space-y-4').querySelector('.font-mono').innerText, 'dp_diterima')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="copiedSnippet === 'dp_diterima' ? 'check' : 'content_copy'"></span>
                            <span x-text="copiedSnippet === 'dp_diterima' ? 'Tersalin!' : 'Salin Template Teks'"></span>
                        </button>
                    </div>
                </div>

                <!-- TAB 5: REVIEW & DEMO PRATINJAU -->
                <div x-show="activeTab === 'review'" class="space-y-4" style="display: none;">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Template Uji Coba Pratinjau Demo Klien</span>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">{{ $brand }}</span>
                    </div>
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 font-mono text-xs text-zinc-800 dark:text-zinc-200 whitespace-pre-line leading-relaxed selection:bg-emerald-500/20">
Halo Kak [Nama Kontak]! ✨

Kabar baik, pengerjaan proyek *[Nama Project]* telah memasuki tahap *Review Klien / Uji Coba Pratinjau Demo*!
🌐 Link Pratinjau Demo: [Link Website / Preview]

Silakan dicoba dan ditinjau secara menyeluruh sistem dan tampilannya. Apabila ada masukan, revisi, atau penyesuaian detail, silakan sampaikan melalui chat WhatsApp ini atau via *Client Panel*:
🔗 {{ $portalUrl }}

Tim {{ $brand }} siap menyempurnakan sebelum website resmi diluncurkan (*LIVE*). Terima kasih! 🚀
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-zinc-400">Gunakan saat status proyek diubah ke tahap Review.</span>
                        <button @click="copyToClipboard($el.closest('.space-y-4').querySelector('.font-mono').innerText, 'review')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="copiedSnippet === 'review' ? 'check' : 'content_copy'"></span>
                            <span x-text="copiedSnippet === 'review' ? 'Tersalin!' : 'Salin Template Teks'"></span>
                        </button>
                    </div>
                </div>

                <!-- TAB 6: TAGIHAN PELUNASAN (SETTLEMENT) -->
                <div x-show="activeTab === 'settlement'" class="space-y-4" style="display: none;">
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
                            <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                            <span>Metode Pelunasan: <strong>Link Pembayaran Langsung (QRIS &amp; Virtual Account Mandiri / BNI)</strong></span>
                        </div>
                    </div>
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 font-mono text-xs text-zinc-800 dark:text-zinc-200 whitespace-pre-line leading-relaxed selection:bg-emerald-500/20">
Halo Kak [Nama Kontak]! ✨

Pengerjaan proyek *[Nama Project]* saat ini telah berada pada tahap akhir (*Review Klien*) dan siap diluncurkan resmi (*LIVE*).

🌐 Link Pratinjau Demo: [Link Preview]

📌 *Rincian Tagihan Pelunasan Proyek:*
• Total Nilai Proyek: Rp [Total Nilai]
• Uang Muka (DP) Diterima: Rp [DP Diterima]
• *Sisa Tagihan Pelunasan: Rp [Sisa Tagihan]*

⚡ *Link Pelunasan Langsung (Tanpa Perlu Login):*
👉 [Link Pembayaran Langsung /pay/...]

✨ *Metode Pembayaran Otomatis (Instan 24 Jam):*
• QRIS Semua Bank & E-Wallet (BCA, Livin, GoPay, OVO, Dana)
• Virtual Account Bank Mandiri & BNI

Setelah pelunasan diselesaikan, sistem akan memverifikasi otomatis dan menerbitkan *Kwitansi Resmi Lunas* seketika, lalu tim kami langsung melakukan peluncuran resmi (*Go-Live*) ke domain utama. Terima kasih banyak atas kerjasamanya bersama *{{ $brand }}*! 🙏🚀
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-zinc-400">Kirim sebelum proses Go-Live domain utama dan penyerahan akun.</span>
                        <button @click="copyToClipboard($el.closest('.space-y-4').querySelector('.font-mono').innerText, 'settlement')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="copiedSnippet === 'settlement' ? 'check' : 'content_copy'"></span>
                            <span x-text="copiedSnippet === 'settlement' ? 'Tersalin!' : 'Salin Template Teks'"></span>
                        </button>
                    </div>
                </div>

                <!-- TAB 7: SELESAI & LIVE -->
                <div x-show="activeTab === 'selesai'" class="space-y-4" style="display: none;">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Template Serah Terima Proyek Selesai &amp; Online</span>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">{{ $brand }}</span>
                    </div>
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 font-mono text-xs text-zinc-800 dark:text-zinc-200 whitespace-pre-line leading-relaxed selection:bg-emerald-500/20">
Halo Kak [Nama Kontak]! 🎉

Kabar gembira, website untuk *[Nama Project]* sudah selesai dan *LIVE* bisa diakses publik!

🌐 Link Website Resmi: [Link Website Klien]

Semoga websitenya berkontribusi menaikkan omzet, kepercayaan, dan kredibilitas usaha Kakak ya. 🚀

💡 *Info Pendampingan & Maintenance:*
Biar website tetap aman, cepat, dan kalau butuh update promo/banner tanpa ribet coding, kami ada paket pendampingan bulanan mulai *Rp 150.000/bulan*. Tinggal chat kami saja jika ingin diaktifkan ya Kak.

Jika butuh bantuan operasional kapan saja, silakan hubungi tim kami via WhatsApp: {{ $phone1 }}. Terima kasih banyak atas kepercayaannya bersama *{{ $brand }}*! 🙏✨
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-zinc-400">Kirim saat website telah online di domain utama klien.</span>
                        <button @click="copyToClipboard($el.closest('.space-y-4').querySelector('.font-mono').innerText, 'selesai')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="copiedSnippet === 'selesai' ? 'check' : 'content_copy'"></span>
                            <span x-text="copiedSnippet === 'selesai' ? 'Tersalin!' : 'Salin Template Teks'"></span>
                        </button>
                    </div>
                </div>

                <!-- TAB 8: MAINTENANCE -->
                <div x-show="activeTab === 'maintenance'" class="space-y-4" style="display: none;">
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300">
                            <span class="material-symbols-outlined text-[18px]">verified_user</span>
                            <span>Tagihan Maintenance Otomatis: <strong>Link Pembayaran Online (QRIS &amp; Virtual Account Mandiri / BNI)</strong></span>
                        </div>
                    </div>
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 font-mono text-xs text-zinc-800 dark:text-zinc-200 whitespace-pre-line leading-relaxed selection:bg-emerald-500/20">
Halo Kak [Nama Kontak], pengingat santai untuk tagihan layanan pemeliharaan & maintenance website bulan ini sebesar *Rp [Nominal]* dengan jatuh tempo tanggal *[Tanggal Jatuh Tempo]*.

🛡️ *Fasilitas Layanan Mencakup:*
✅ Auto-Backup Database & Berkas Website Rutin ke Cloud Storage Aman
✅ Pemantauan Uptime Server 24/7 & Proteksi Keamanan (Anti-Malware & SSL Guard)
✅ Bantuan Update Teks, Foto Produk, Banner Promo & Penyesuaian Konten
✅ Jalur Bantuan Teknis & Dukungan Prioritas via WhatsApp

⚡ *Link Pembayaran Langsung (Tanpa Perlu Login):*
👉 [Link Pembayaran Langsung /pay/...]

✨ *Metode Pembayaran Otomatis (24 Jam):*
• QRIS Semua Bank & E-Wallet (BCA, Livin, GoPay, OVO, Dana)
• Virtual Account Bank Mandiri & BNI

Kwitansi pelunasan resmi akan terbit otomatis setelah pembayaran berhasil tanpa perlu konfirmasi bukti manual. Terima kasih banyak atas kerjasamanya bersama *{{ $brand }}*! 🙏✨
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-zinc-400">Template pengingat jatuh tempo langganan maintenance bulanan (H-3).</span>
                        <button @click="copyToClipboard($el.closest('.space-y-4').querySelector('.font-mono').innerText, 'maintenance')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="copiedSnippet === 'maintenance' ? 'check' : 'content_copy'"></span>
                            <span x-text="copiedSnippet === 'maintenance' ? 'Tersalin!' : 'Salin Template Teks'"></span>
                        </button>
                    </div>
                </div>

                <!-- TAB 9: KONTAK RESMI & SUPPORT -->
                <div x-show="activeTab === 'kontak'" class="space-y-4" style="display: none;">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Template Saluran Kontak &amp; Legalitas Resmi</span>
                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-mono">{{ $brand }}</span>
                    </div>
                    <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-950 border border-zinc-200 dark:border-zinc-800 font-mono text-xs text-zinc-800 dark:text-zinc-200 whitespace-pre-line leading-relaxed selection:bg-emerald-500/20">
Halo Kak! Berikut adalah saluran kontak dan informasi resmi *{{ $brand }}* ({{ $company }}):

📞 *Layanan WhatsApp & Konsultasi:*
• WhatsApp Layanan 1: {{ $phone1 }}
@if($phone2)
• WhatsApp Layanan 2: {{ $phone2 }}
@endif
✉️ *Email Resmi:* {{ $email }}
🌐 *Website Resmi:* {{ $website }}
📍 *Domisili Operasional:* {{ $city }}

🕒 Jam Layanan: Senin – Sabtu (09.00 – 21.00 WIB)
Ada yang bisa kami bantu seputar kebutuhan website, sistem aplikasi, atau kendala teknis Anda hari ini? Silakan sampaikan ya Kak. Terima kasih! 😊
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <span class="text-[11px] text-zinc-400">Kirimkan jika klien meminta alamat email, kontak alternatif, atau info resmi kantor.</span>
                        <button @click="copyToClipboard($el.closest('.space-y-4').querySelector('.font-mono').innerText, 'kontak')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[16px]" x-text="copiedSnippet === 'kontak' ? 'check' : 'content_copy'"></span>
                            <span x-text="copiedSnippet === 'kontak' ? 'Tersalin!' : 'Salin Template Teks'"></span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Modal Footer Note -->
            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[11px] text-zinc-500 shrink-0">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-emerald-600">sync</span>
                    <span>Seluruh data rekening &amp; brand terhubung langsung dengan Pengaturan Perusahaan.</span>
                </span>
                @role('admin')
                <a href="{{ route('admin.settings.company.edit') }}" class="font-bold text-emerald-600 dark:text-emerald-400 hover:underline">
                    Edit Pengaturan Perusahaan &rarr;
                </a>
                @endrole
            </div>

        </div>
    </div>
</div>

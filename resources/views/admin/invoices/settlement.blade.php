<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Pelunasan {{ $settlementNumber }} - {{ $project->nama_project }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #111111;
            background-color: {{ ($isPdf ?? false) ? '#ffffff' : '#f4f4f5' }};
            margin: 0;
            padding: {{ ($isPdf ?? false) ? '0' : '20px' }};
        }
        .no-print {
            {{ ($isPdf ?? false) ? 'display: none !important;' : '' }}
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #ffffff !important;
                padding: 0 !important;
            }
            .settlement-wrapper {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
        }
        .action-bar {
            max-width: 800px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid #e4e4e7;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #18181b;
            background: #18181b;
            color: #ffffff;
        }
        .action-btn.secondary {
            background: #ffffff;
            color: #18181b;
            border: 1px solid #d4d4d8;
        }
        .action-btn.secondary:hover {
            background: #f4f4f5;
        }
        .flash-alert {
            max-width: 800px;
            margin: 0 auto 16px auto;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid #000000;
            background: #f4f4f5;
            color: #000000;
        }

        /* Settlement Container */
        .settlement-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: {{ ($isPdf ?? false) ? '0' : '40px 45px' }};
            border: {{ ($isPdf ?? false) ? 'none' : '1px solid #e4e4e7' }};
            box-shadow: {{ ($isPdf ?? false) ? 'none' : '0 4px 15px rgba(0,0,0,0.06)' }};
            border-radius: {{ ($isPdf ?? false) ? '0' : '4px' }};
        }

        /* Tables & Structure */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            padding-bottom: 15px;
        }
        .divider {
            border-top: 2px solid #000000;
            margin: 5px 0 18px 0;
        }
        .meta-table td {
            vertical-align: top;
            padding: 4px 0;
            font-size: 10.5pt;
        }
        .items-table {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f4f4f5;
            color: #000000;
            font-weight: 700;
            font-size: 9.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            text-align: left;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e4e4e7;
            font-size: 10pt;
            vertical-align: top;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .summary-table {
            width: 100%;
            margin-top: 10px;
        }
        .summary-table td {
            padding: 4px 0;
            font-size: 10.5pt;
        }
        .total-due {
            border-top: 1.5px solid #000000;
            border-bottom: 2px solid #000000;
            padding-top: 6px !important;
            padding-bottom: 6px !important;
            font-size: 11.5pt !important;
            font-weight: bold;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border: 1.5px solid #d97706;
            color: #b45309;
            background: #fffbeb;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-radius: 4px;
        }
        .payment-box {
            border: 1px solid #000000;
            padding: 12px;
            margin-top: 15px;
            background: #fafafa;
        }
        .qris-img {
            width: 95px;
            height: 95px;
            border: 1px solid #000000;
            padding: 2px;
            background: #ffffff;
        }
        .footer-note {
            font-size: 8.5pt;
            color: #444444;
            line-height: 1.4;
            margin-top: 15px;
            border-top: 1px dashed #cccccc;
            padding-top: 10px;
        }
        .sign-table {
            margin-top: 25px;
            width: 100%;
        }
        .sign-table td {
            vertical-align: top;
            font-size: 9.5pt;
        }
    </style>
</head>
<body>

    @php
        $logoSrc = !empty($logoBase64) ? $logoBase64 : ($settings->logo_base64 ?? asset('images/logo.png'));
    @endphp

    @if(!($isPdf ?? false))
    <!-- Action Bar (Web Only) -->
    <div class="action-bar no-print">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('admin.projects.show', $project->id) }}" class="action-btn secondary">
                &larr; Kembali ke Proyek
            </a>
            <div style="display: flex; align-items: center; gap: 8px; padding-left: 12px; border-left: 1px solid #e4e4e7;">
                <img src="{{ asset('images/logo.png') }}" alt="VexaHost" style="height: 26px; width: auto;">
                <span style="font-size: 13px; font-weight: 800; color: #18181b;">Vexa<span style="color: #059669;">Host</span></span>
            </div>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            <button onclick="window.print()" class="action-btn secondary">
                Cetak / Print
            </button>
            <a href="{{ route('admin.invoices.settlement', [$project->id, 'format' => 'pdf']) }}" class="action-btn">
                Download PDF
            </a>
            <a href="{{ route('admin.invoices.settlement', [$project->id, 'format' => 'word']) }}" class="action-btn secondary">
                Download Word
            </a>
            @php
                $brandName = $settings->brand_name ?? 'VexaHost';
                $invoiceModel = $project->latestInvoice ?: $project->invoices()->first();
                $directPayUrl = $invoiceModel?->payment_token ? route('invoices.pay', $invoiceModel->payment_token) : null;
                $settleNo = $settlementNumber ?? ('INV-SETTLE/' . ($project->created_at ? $project->created_at->format('Ym') : now()->format('Ym')) . '/' . str_pad($project->id, 4, '0', STR_PAD_LEFT));
                $payMsg = $directPayUrl ? "\n\n⚡ Link Pelunasan Langsung (Tanpa Login):\n👉 {$directPayUrl}\n\n✨ Metode Otomatis: QRIS Semua Bank/E-Wallet & Virtual Account Mandiri/BNI (24 Jam Instan)" : "";
                $waShareText = rawurlencode("Halo Kak " . ($lead?->nama_kontak ?: $lead?->nama_usaha) . ",\n\nBerikut rincian Invoice Tagihan Pelunasan resmi untuk proyek *{$project->nama_project}*:\n• Nomor Dokumen: {$settleNo}\n• Total Nilai Proyek: Rp " . number_format($project->harga, 0, ',', '.') . "\n• DP Diterima: Rp " . number_format($project->total_paid, 0, ',', '.') . "\n• Sisa Tagihan Pelunasan: Rp " . number_format($project->remaining_balance, 0, ',', '.') . "{$payMsg}\n\nKwitansi lunas bertanda tangan digital terbit seketika tanpa perlu konfirmasi manual. Terima kasih banyak!\n- {$brandName}");
                $waPhone = preg_replace('/[^0-9]/', '', $lead?->kontak_wa ?? '');
            @endphp
            @if(!empty($waPhone))
            <a href="https://wa.me/{{ $waPhone }}?text={{ $waShareText }}" target="_blank" class="action-btn secondary">
                Teks WA
            </a>
            @endif
            @if(!empty($directPayUrl))
            <a href="{{ $directPayUrl }}" target="_blank" class="action-btn secondary" style="color: #059669; border-color: #6ee7b7; background: #ecfdf5;">
                ⚡ Buka Halaman Bayar
            </a>
            @endif
            @if($lead && !empty($lead->kontak_wa))
            <form id="formSendWaSettlement" action="{{ route('admin.invoices.settlement.send-wa', $project->id) }}" method="POST" style="margin: 0;">
                @csrf
                <button type="button" class="action-btn" style="background: #059669; border-color: #059669;" onclick="Swal.fire({title:'Konfirmasi WhatsApp',text:'Kirim dokumen PDF Tagihan Pelunasan dan tautan pembayaran langsung ke WhatsApp klien ({{ $lead->kontak_wa }})?',icon:'question',showCancelButton:true,confirmButtonText:'Ya, Kirim WA',cancelButtonText:'Batal',confirmButtonColor:'#059669',cancelButtonColor:'#71717a',reverseButtons:true}).then(r=>{if(r.isConfirmed)document.getElementById('formSendWaSettlement').submit()})">
                    Kirim via WhatsApp (PDF)
                </button>
            </form>
            @endif
            @if($lead && !empty($lead->email))
            <form id="formSendEmailSettlement" action="{{ route('admin.invoices.settlement.send-email', $project->id) }}" method="POST" style="margin: 0;">
                @csrf
                <button type="button" class="action-btn" style="background: #0284c7; border-color: #0284c7;" onclick="Swal.fire({title:'Konfirmasi Email',text:'Kirim dokumen PDF Tagihan Pelunasan dan link pembayaran langsung ke email klien ({{ $lead->email }})?',icon:'question',showCancelButton:true,confirmButtonText:'Ya, Kirim Email',cancelButtonText:'Batal',confirmButtonColor:'#0284c7',cancelButtonColor:'#71717a',reverseButtons:true}).then(r=>{if(r.isConfirmed)document.getElementById('formSendEmailSettlement').submit()})">
                    Kirim via Email (PDF)
                </button>
            </form>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="flash-alert no-print" style="background: #ecfdf5; border-color: #059669; color: #065f46;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="flash-alert no-print" style="background: #fef2f2; border-color: #dc2626; color: #991b1b;">
        {{ session('error') }}
    </div>
    @endif
    @endif

    <div class="settlement-wrapper">
        <!-- Letterhead (strictly from CompanySetting) -->
        <table class="header-table">
            <tr>
                <td style="width: 58%;">
                    <table style="width: 100%;">
                        <tr>
                            @if(!empty($logoSrc))
                                <td style="width: 65px; vertical-align: middle;">
                                    <img src="{{ $logoSrc }}" alt="VexaHost Logo" style="height: 55px; width: auto;" />
                                </td>
                            @endif
                            <td style="vertical-align: middle; padding-left: 8px;">
                                <div style="font-size: 13pt; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                                    {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}
                                </div>
                                <div style="font-size: 8.5pt; font-weight: 600; color: #444444; text-transform: uppercase; letter-spacing: 0.5px;">
                                    {{ $settings->tagline ?? ($settings->brand_name ?? 'Software House & Digital Solutions') }}
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div style="font-size: 8.5pt; color: #333333; margin-top: 8px; line-height: 1.4;">
                        Email: {{ $settings->email_support ?? 'vexahostcloudtech@gmail.com' }} | {{ $settings->email_company ?? 'vexahostcloudtech@gmail.com' }}<br>
                        Website: {{ $settings->website_url ?? 'https://vexahostcloud.my.id' }}<br>
                        WhatsApp: {{ $settings->phone_support ?? '0858-0874-9131' }}{{ !empty($settings->phone_support_2) ? ' / ' . $settings->phone_support_2 : '' }}
                    </div>
                </td>
                <td style="width: 42%; text-align: right;">
                    <div style="font-size: 16pt; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: #18181b;">
                        INVOICE PELUNASAN
                    </div>
                    <div style="font-size: 8.5pt; font-weight: 700; color: #d97706; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">
                        FINAL SETTLEMENT INVOICE
                    </div>
                    <div style="margin-top: 6px;">
                        <span class="status-badge">Menunggu Pelunasan</span>
                    </div>
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        <!-- Metadata Information -->
        <table class="meta-table">
            <tr>
                <td style="width: 55%;">
                    <div style="font-size: 8.5pt; text-transform: uppercase; font-weight: 700; color: #666666; margin-bottom: 3px;">
                        DITAGIHKAN KEPADA:
                    </div>
                    <div style="font-size: 11pt; font-weight: bold;">
                        {{ $lead->nama_kontak ?: $lead->nama_usaha }}
                    </div>
                    @if($lead->nama_usaha && $lead->nama_usaha !== $lead->nama_kontak)
                    <div style="font-size: 10pt; color: #333333; font-weight: 500;">
                        {{ $lead->nama_usaha }}
                    </div>
                    @endif
                    <div style="font-size: 9.5pt; color: #555555; margin-top: 2px;">
                        WhatsApp: {{ $lead->kontak_wa ?: '-' }}
                        @if(!empty($lead->email))
                        <br>Email: {{ $lead->email }}
                        @endif
                    </div>
                </td>
                <td style="width: 45%;">
                    <table style="width: 100%;">
                        <tr>
                            <td style="width: 45%; font-size: 9.5pt; color: #555555;">No. Dokumen:</td>
                            <td style="width: 55%; font-size: 9.5pt; font-weight: bold; font-family: monospace;">{{ $settlementNumber }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 9.5pt; color: #555555;">Tanggal Tagihan:</td>
                            <td style="font-size: 9.5pt; font-weight: 600;">{{ $settlementDate }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 9.5pt; color: #555555;">Jatuh Tempo:</td>
                            <td style="font-size: 9.5pt; font-weight: 600; color: #dc2626;">{{ $dueDate }}</td>
                        </tr>
                        <tr>
                            <td style="font-size: 9.5pt; color: #555555;">Status Proyek:</td>
                            <td style="font-size: 9.5pt; font-weight: bold; color: #d97706;">Review Selesai (Siap Live)</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Line Items Breakdown Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 6%;" class="text-center">No</th>
                    <th style="width: 54%;">Rincian Tagihan Pengerjaan</th>
                    <th style="width: 15%;" class="text-center">Status</th>
                    <th style="width: 25%;" class="text-right">Nominal (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center" style="font-weight: 600;">1</td>
                    <td>
                        <div style="font-weight: bold; font-size: 10.5pt;">{{ $project->nama_project }}</div>
                        <div style="font-size: 9pt; color: #555555; margin-top: 2px;">
                            Paket Layanan: {{ $project->paket_label ?? 'Paket Layanan Software & Digital Solutions' }}
                        </div>
                        <div style="font-size: 8.5pt; color: #059669; margin-top: 2px;">
                            &bull; Tahap pengerjaan teknis &amp; uji coba pratinjau (Review Klien) telah selesai disetujui.
                        </div>
                    </td>
                    <td class="text-center" style="font-size: 9pt; font-weight: 600;">Kontrak Awal</td>
                    <td class="text-right" style="font-weight: bold; font-family: monospace;">
                        Rp {{ number_format($project->harga, 0, ',', '.') }}
                    </td>
                </tr>
                @if($project->total_paid > 0)
                <tr style="background-color: #fafafa;">
                    <td class="text-center" style="color: #666666;">-</td>
                    <td>
                        <div style="font-weight: 600; font-size: 9.5pt; color: #059669;">
                            Pembayaran Uang Muka (DP) Diterima
                        </div>
                        <div style="font-size: 8.5pt; color: #666666;">
                            Telah diverifikasi dan dipotongkan dari total nilai kontrak
                        </div>
                    </td>
                    <td class="text-center" style="font-size: 8.5pt; font-weight: bold; color: #059669;">LUNAS (DP)</td>
                    <td class="text-right" style="font-weight: bold; font-family: monospace; color: #059669;">
                        - Rp {{ number_format($project->total_paid, 0, ',', '.') }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Summary & Totals -->
        <table style="width: 100%;">
            <tr>
                <td style="width: 48%; vertical-align: top; padding-right: 15px;">
                    <div style="font-size: 9pt; font-weight: bold; text-transform: uppercase; color: #555555; margin-bottom: 4px;">
                        TERBILANG SISA PELUNASAN:
                    </div>
                    <div style="font-size: 9.5pt; font-style: italic; font-weight: 600; color: #18181b; background: #fafafa; border: 1px dashed #d4d4d8; padding: 8px 10px; border-radius: 4px;">
                        "{{ $terbilang }}"
                    </div>

                    <div style="font-size: 8.5pt; color: #666666; margin-top: 10px; line-height: 1.4;">
                        <strong>Catatan Pelunasan:</strong><br>
                        Pelunasan sisa tagihan ini merupakan tahapan final sebelum penyerahan hak akses penuh (Go-Live) dan domain resmi. Kwitansi resmi lunas bertanda tangan digital akan otomatis diterbitkan setelah dana diterima.
                    </div>
                </td>
                <td style="width: 52%; vertical-align: top;">
                    <table class="summary-table">
                        <tr>
                            <td style="width: 50%; color: #555555;">Total Nilai Proyek:</td>
                            <td style="width: 50%; text-align: right; font-weight: 600; font-family: monospace;">
                                Rp {{ number_format($project->harga, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr>
                            <td style="color: #059669;">Uang Muka (DP) Terbayar:</td>
                            <td style="text-align: right; font-weight: 600; font-family: monospace; color: #059669;">
                                - Rp {{ number_format($project->total_paid, 0, ',', '.') }}
                            </td>
                        </tr>
                        <tr class="total-due">
                            <td style="color: #b45309; font-size: 11pt;">SISA PELUNASAN:</td>
                            <td style="text-align: right; color: #b45309; font-size: 12pt; font-family: monospace;">
                                Rp {{ number_format($project->remaining_balance, 0, ',', '.') }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Terms and Signatures (strictly from CompanySetting) -->
        <table class="sign-table">
            <tr>
                <td style="width: 60%; vertical-align: bottom;">
                    <div class="footer-note">
                        <strong>Syarat &amp; Ketentuan Penagihan:</strong><br>
                        {!! nl2br(e($settings->invoice_terms ?? "1. Pembayaran resmi hanya sah apabila ditransfer ke rekening resmi VexaHost yang tercantum pada dokumen ini.\n2. Kwitansi resmi lunas bertanda tangan digital akan diterbitkan otomatis setelah pembayaran diverifikasi.\n3. Untuk bantuan teknis atau administrasi penagihan, hubungi WhatsApp: 0858-0874-9131.")) !!}
                    </div>
                </td>
                <td style="width: 40%; text-align: center; vertical-align: bottom;">
                    <div style="font-size: 9pt; color: #444444;">
                        {{ $settings->domicile_city ?? 'Jakarta' }}, {{ $settlementDate }}
                    </div>
                    <div style="font-size: 9pt; font-weight: bold; text-transform: uppercase; margin-top: 3px;">
                        {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}
                    </div>
                    <div style="height: 55px; margin: 4px 0; text-align: center;">
                        @if(!empty($signatureBase64))
                            <img src="{{ $signatureBase64 }}" alt="Tanda Tangan" style="max-height: 55px; width: auto; max-width: 140px; display: inline-block;">
                        @endif
                    </div>
                    <div style="font-size: 9.5pt; font-weight: bold; text-decoration: underline;">
                        {{ $settings->director_name ?? 'Manajemen VexaHost' }}
                    </div>
                    <div style="font-size: 8pt; color: #555555; text-transform: uppercase;">
                        {{ $settings->director_title ?? 'Finance & Executive Director' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
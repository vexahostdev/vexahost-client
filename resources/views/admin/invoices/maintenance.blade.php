<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Maintenance {{ $invoiceNumber }} - {{ $lead?->nama_usaha }}</title>
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
            .invoice-wrapper {
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

        /* Invoice Container */
        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: {{ ($isPdf ?? false) ? '0' : '40px 45px' }};
            border: {{ ($isPdf ?? false) ? 'none' : '1px solid #e4e4e7' }};
            box-shadow: {{ ($isPdf ?? false) ? 'none' : '0 4px 15px rgba(0,0,0,0.06)' }};
            border-radius: {{ ($isPdf ?? false) ? '0' : '4px' }};
        }

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
            border: 1px solid #000000;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
    <!-- Action Bar & Notifications (Web View Only) -->
    @if(session('success'))
        <div class="flash-alert no-print" style="background: #ecfdf5; border-color: #059669; color: #065f46;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-alert no-print" style="border-color: #dc2626; background: #fef2f2; color: #991b1b;">
            {{ session('error') }}
        </div>
    @endif

    <div class="action-bar no-print">
        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('admin.maintenance.index') }}" class="action-btn secondary">
                &larr; Kembali ke Data Maintenance
            </a>
            <div style="display: flex; align-items: center; gap: 8px; padding-left: 12px; border-left: 1px solid #e4e4e7;">
                <img src="{{ asset('images/logo.png') }}" alt="VexaHost" style="height: 26px; width: auto;">
                <span style="font-size: 13px; font-weight: 800; color: #18181b;">Vexa<span style="color: #059669;">Host</span></span>
            </div>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @php
                $cleanMntNo = str_replace('/', '-', $invoiceNumber);
                $brandName = $settings->brand_name ?? 'VexaHost';
                $invoiceObj = $subscription->latestInvoice ?: $subscription->getOrCreateInvoice();
                if (empty($invoiceObj->payment_token)) {
                    $invoiceObj->payment_token = \Illuminate\Support\Str::random(40);
                    $invoiceObj->saveQuietly();
                }
                $directPayUrl = route('invoices.pay', $invoiceObj->payment_token);
                $waShareText = rawurlencode("Halo Kak " . ($lead?->nama_kontak ?: $lead?->nama_usaha) . ",\n\nBerikut rincian Invoice Pemeliharaan (Maintenance) Website & Server untuk *{$lead?->nama_usaha}*:\n• Nomor: {$invoiceNumber}\n• Biaya: Rp " . number_format($subscription->harga_bulanan, 0, ',', '.') . " / Bulan\n• Jatuh Tempo: {$dueDate}\n\n⚡ *Link Pembayaran Langsung (Tanpa Perlu Login):*\n{$directPayUrl}\n\n(Mendukung QRIS instan semua bank/e-wallet & Virtual Account Mandiri / BNI)\n\nTerima kasih atas kepercayaannya!\n- {$brandName}");
                $waPhone = preg_replace('/[^0-9]/', '', $lead?->kontak_wa ?? '');
            @endphp
            @if($subscription->id)
                <form id="formSendWaMaintenance" action="{{ route('admin.invoices.maintenance.send-wa', $subscription->id) }}" method="POST" style="display: inline; margin: 0;">
                    @csrf
                    <button type="button" class="action-btn" style="background: #059669; border-color: #059669;" onclick="Swal.fire({title:'Konfirmasi',text:'Kirim dokumen PDF Invoice Maintenance ini langsung ke nomor WhatsApp {{ $lead?->kontak_wa }} via VexaHost WA Gateway?',icon:'question',showCancelButton:true,confirmButtonText:'Ya, Kirim',cancelButtonText:'Batal',confirmButtonColor:'#059669',cancelButtonColor:'#71717a',reverseButtons:true}).then(r=>{if(r.isConfirmed)document.getElementById('formSendWaMaintenance').submit()})">
                        Kirim PDF via WhatsApp
                    </button>
                </form>
                @if(!empty($lead?->email))
                    <form id="formSendEmailMaintenance" action="{{ route('admin.invoices.maintenance.send-email', $subscription->id) }}" method="POST" style="display: inline; margin: 0;">
                        @csrf
                        <button type="button" class="action-btn" style="background: #0284c7; border-color: #0284c7;" onclick="Swal.fire({title:'Kirim Invoice via Email?',text:'Kirim dokumen PDF Invoice Maintenance beserta link pembayaran instan ke email {{ $lead?->email }}?',icon:'question',showCancelButton:true,confirmButtonText:'Ya, Kirim Email',cancelButtonText:'Batal',confirmButtonColor:'#0284c7',cancelButtonColor:'#71717a',reverseButtons:true}).then(r=>{if(r.isConfirmed)document.getElementById('formSendEmailMaintenance').submit()})">
                            Kirim Email
                        </button>
                    </form>
                @endif
            @endif
            <a href="https://wa.me/{{ $waPhone }}?text={{ $waShareText }}" target="_blank" class="action-btn secondary">
                Teks WA
            </a>
            <a href="{{ $directPayUrl }}" target="_blank" class="action-btn secondary" style="color: #059669; border-color: #6ee7b7; background: #ecfdf5;">
                ⚡ Buka Halaman Bayar
            </a>
            <a href="{{ route('admin.invoices.maintenance', ['subscription' => $subscription->id ?? 1, 'format' => 'pdf']) }}" class="action-btn secondary">
                Download PDF
            </a>
            <a href="{{ route('admin.invoices.maintenance', ['subscription' => $subscription->id ?? 1, 'format' => 'word']) }}" class="action-btn secondary">
                Download Word
            </a>
            <button onclick="window.print()" class="action-btn secondary">
                Cetak
            </button>
        </div>
    </div>
@endif

<!-- Enterprise Maintenance Invoice Sheet -->
<div class="invoice-wrapper">

    <!-- Header: Company Info & Logo -->
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
                                {{ $settings->tagline ?? 'Software House & Digital Solutions' }}
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
            <td style="width: 42%; text-align: right; vertical-align: middle;">
                <div style="font-size: 17pt; font-weight: 900; letter-spacing: 0.5px; text-transform: uppercase;">
                    INVOICE MAINTENANCE
                </div>
                <div style="font-size: 8.5pt; font-weight: 600; color: #555555; text-transform: uppercase;">
                    Pemeliharaan Sistem &amp; Cloud Server
                </div>
                <div style="font-size: 10.5pt; font-weight: bold; margin-top: 4px;">
                    No: {{ $invoiceNumber }}
                </div>
                <div style="margin-top: 6px;">
                    <span class="status-badge">
                        TAGIHAN BERJALAN
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Metadata: Bill To & Dates -->
    <table class="meta-table" style="margin-bottom: 10px;">
        <tr>
            <td style="width: 58%;">
                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #555555;">
                    TAGIHAN KEPADA (BILL TO):
                </div>
                <div style="font-size: 12pt; font-weight: bold; margin-top: 3px;">
                    {{ $lead?->nama_usaha ?? 'Klien VexaHost' }}
                </div>
                <div style="font-size: 9.5pt; color: #222222; margin-top: 2px;">
                    Kontak: <strong>{{ $lead?->nama_kontak ?? '-' }}</strong>
                </div>
                <div style="font-size: 9pt; color: #444444;">
                    WhatsApp: {{ $lead?->kontak_wa ?? '-' }}<br>
                    Website Aset: {{ $project?->link_website ?? ($project?->nama_project ?? '-') }}
                </div>
            </td>
            <td style="width: 42%; text-align: right;">
                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; color: #555555;">
                    DETAIL PERIODE:
                </div>
                <table style="width: 100%; margin-top: 3px; font-size: 9.5pt;">
                    <tr>
                        <td style="text-align: right; color: #555555; padding: 2px 0;">Tanggal Terbit:</td>
                        <td style="text-align: right; font-weight: bold; width: 110px; padding: 2px 0;">{{ $invoiceDate }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right; color: #555555; padding: 2px 0;">Jatuh Tempo:</td>
                        <td style="text-align: right; font-weight: bold; padding: 2px 0;">{{ $dueDate }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: right; color: #555555; padding: 2px 0;">Siklus:</td>
                        <td style="text-align: right; font-weight: bold; padding: 2px 0;">BULANAN (RECURRING)</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Line Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No.</th>
                <th style="width: 55%;">Deskripsi Layanan Pemeliharaan</th>
                <th style="width: 15%; text-align: center;">Periode</th>
                <th style="width: 25%; text-align: right;">Total Biaya (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center" style="font-weight: bold;">1</td>
                <td>
                    <div style="font-weight: bold; font-size: 10.5pt;">Paket Pemeliharaan Website, Server &amp; Security Shield</div>
                    <div style="font-size: 8.5pt; color: #444444; margin-top: 3px;">
                        &bull; Auto Backup Database &amp; Asset Mingguan ke Cloud Storage<br>
                        &bull; Monitoring Server Uptime, SSL Security &amp; Malware Protection<br>
                        &bull; Bantuan Update Konten / Banner &amp; Dukungan Teknis Prioritas via WhatsApp
                    </div>
                </td>
                <td class="text-center">1 Bulan</td>
                <td class="text-right" style="font-weight: bold;">
                    {{ number_format($subscription->harga_bulanan, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Calculation & Payment Breakdown -->
    <table style="width: 100%; margin-top: 5px;">
        <tr>
            <!-- Left Spacer -->
            <td style="width: 55%; vertical-align: top; padding-right: 15px;"></td>

            <!-- Right: Total Bill -->
            <td style="width: 45%; vertical-align: top;">
                <table class="summary-table">
                    <tr class="total-due">
                        <td>TOTAL TAGIHAN:</td>
                        <td style="text-align: right;">
                            Rp {{ number_format($subscription->harga_bulanan, 0, ',', '.') }}
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 12px; text-align: right; font-size: 8pt; color: #555555;">
                    Status Siklus: <strong>Aktif Berlangganan</strong><br>
                    Harap transfer sebelum tanggal <strong>{{ $dueDate }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Terms and Signatures -->
    <table class="sign-table">
        <tr>
            <td style="width: 60%; vertical-align: bottom;">
                <div class="footer-note">
                    <strong>Ketentuan Layanan Pemeliharaan:</strong><br>
                    1. Pembayaran resmi hanya sah melalui rekening {{ $settings->bank_name ?? 'BCA' }} atas nama <strong>{{ $settings->bank_account_holder ?? '-' }}</strong> atau <strong>QRIS resmi</strong>.<br>
                    2. Layanan pemeliharaan server, backup cloud, dan proteksi keamanan sistem akan otomatis diperpanjang setelah bukti pembayaran diverifikasi.<br>
                    3. Layanan bantuan support teknis 24/7 via WhatsApp: <strong>{{ $settings->phone_support ?? '0858-0874-9131' }}</strong>.
                </div>
            </td>
            <td style="width: 40%; text-align: center; vertical-align: bottom;">
                <div style="font-size: 9pt; color: #444444;">
                    {{ $settings->domicile_city ?? 'Jakarta' }}, {{ $invoiceDate }}
                </div>
                <div style="font-size: 9pt; font-weight: bold; text-transform: uppercase; margin-top: 3px;">
                    {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}
                </div>
                <div style="height: 55px; margin: 4px 0; text-align: center;">
                    @if(!empty($signatureBase64))
                        <img src="{{ $signatureBase64 }}" alt="Tanda Tangan" style="max-height: 55px; width: auto; max-width: 140px; display: inline-block;" />
                    @endif
                </div>
                <div style="font-size: 9.5pt; font-weight: bold; text-decoration: underline;">
                    {{ $settings->director_name ?? 'Manajemen VexaHost' }}
                </div>
                <div style="font-size: 8pt; color: #555555; text-transform: uppercase;">
                    {{ $settings->director_title ?? 'Finance & Infrastructure Lead' }}
                </div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>

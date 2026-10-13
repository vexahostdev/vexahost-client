<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi Pembayaran - {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 15mm 12mm 15mm;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.35;
            color: #111111;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-bottom: 6px;
        }
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .company-name {
            font-size: 13pt;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .company-tagline {
            font-size: 8pt;
            color: #333333;
            font-weight: 500;
        }
        .company-contact {
            font-size: 7.5pt;
            color: #555555;
            margin-top: 3px;
        }
        .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #000000;
            text-align: right;
            margin-bottom: 2px;
        }
        .report-subtitle {
            font-size: 8pt;
            color: #444444;
            text-transform: uppercase;
            text-align: right;
        }
        .report-meta {
            font-size: 7.5pt;
            color: #333333;
            text-align: right;
            margin-top: 4px;
        }
        .divider-double {
            border-top: 2.5px solid #000000;
            border-bottom: 1px solid #000000;
            height: 2px;
            margin: 8px 0 14px 0;
        }

        /* Summary Metrics Box */
        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .metrics-table td {
            border: 1px solid #d4d4d8;
            background: #fafafa;
            padding: 8px 12px;
            width: 25%;
        }
        .metric-title {
            font-size: 7pt;
            text-transform: uppercase;
            font-weight: bold;
            color: #555555;
        }
        .metric-value {
            font-size: 11pt;
            font-weight: bold;
            font-family: 'Courier New', Courier, monospace;
            color: #000000;
            margin-top: 2px;
        }

        /* Main Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .data-table th {
            background-color: #f4f4f5;
            color: #000000;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border-top: 1.5px solid #000000;
            border-bottom: 1.5px solid #000000;
            font-size: 7.5pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e4e4e7;
            vertical-align: top;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-mono {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 1.5px 6px;
            font-size: 7pt;
            font-weight: bold;
            border: 1px solid #000000;
            background: #ffffff;
            color: #000000;
            border-radius: 2px;
            text-transform: uppercase;
        }
        .badge-active {
            background: #18181b;
            color: #ffffff;
            border-color: #18181b;
        }

        /* Signature & Footer Section */
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 24px;
            page-break-inside: avoid;
        }
        .sign-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
        .audit-note {
            font-size: 7.5pt;
            color: #555555;
            line-height: 1.4;
            max-width: 480px;
        }
    </style>
</head>
<body>

    <!-- Enterprise Letterhead -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                @if(!empty($logoBase64))
                    <div style="margin-bottom: 5px;">
                        <img src="{{ $logoBase64 }}" alt="Logo" style="max-height: 42px; max-width: 180px; object-fit: contain;" />
                    </div>
                @endif
                <div class="company-name">{{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}</div>
                <div class="company-tagline">{{ $settings->brand_name ?? 'VexaHost' }} &bull; {{ $settings->tagline ?? 'Software House & Digital Solutions' }}</div>
                <div class="company-contact">
                    Domisili: {{ $settings->domicile_city ?? 'Jakarta' }} &bull; 
                    Email: {{ $settings->email_support ?? \App\Support\MailIdentity::contactEmail() }} &bull; 
                    WhatsApp: {{ $settings->phone_support ?? '0858-0874-9131' }} &bull; 
                    Web: {{ $settings->website_url ?? 'https://vexahostcloud.my.id' }}
                </div>
            </td>
            <td style="width: 45%;">
                <div class="report-title">LAPORAN TRANSAKSI PEMBAYARAN</div>
                <div class="report-subtitle">Cashflow Audit &amp; Settlement Reconciliation</div>
                <div class="report-meta">
                    Tanggal Cetak: <strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong><br>
                    Total Data: <strong>{{ $payments->count() }} Transaksi</strong> &bull; Rekening: <strong>{{ $settings->bank_name ?? 'BCA' }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider-double"></div>

    <!-- Summary Metrics -->
    @php
        $totalDanaMasuk = $payments->where('status', 'lunas')->sum('jumlah');
        $totalPending = $payments->where('status', 'pending')->sum('jumlah');
        $totalTransaksi = $payments->count();
    @endphp
    <table class="metrics-table">
        <tr>
            <td>
                <div class="metric-title">Total Transaksi Tercatat</div>
                <div class="metric-value">{{ $totalTransaksi }} Rekod</div>
            </td>
            <td>
                <div class="metric-title">Kas Masuk Terverifikasi</div>
                <div class="metric-value">Rp {{ number_format($totalDanaMasuk, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="metric-title">Menunggu Verifikasi</div>
                <div class="metric-value">Rp {{ number_format($totalPending, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="metric-title">Rekening Bank Resmi</div>
                <div class="metric-value" style="font-size: 8.5pt;">{{ $settings->bank_account_number }} ({{ $settings->bank_account_holder }})</div>
            </td>
        </tr>
    </table>

    <!-- Main Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Proyek &amp; Klien</th>
                <th>Jenis Pembayaran</th>
                <th class="text-right">Nominal (Rp)</th>
                <th class="text-center" style="width: 80px;">Status</th>
                <th>Tanggal Transaksi</th>
                <th>Keterangan / Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $idx => $payment)
                <tr>
                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                    <td>
                        <strong style="color: #000000;">{{ $payment->project?->nama_project ?? '-' }}</strong>
                        <div style="font-size: 7.5pt; color: #555555;">{{ $payment->project?->lead?->nama_usaha }} &bull; {{ $payment->project?->lead?->nama_kontak }}</div>
                    </td>
                    <td>{{ $payment->jenis_label }}</td>
                    <td class="text-right font-mono">
                        Rp {{ number_format($payment->jumlah, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $payment->status === 'lunas' ? 'badge-active' : '' }}">
                            {{ $payment->status_label }}
                        </span>
                    </td>
                    <td>
                        {{ $payment->tanggal ? $payment->tanggal->translatedFormat('d F Y') : '-' }}
                    </td>
                    <td>
                        {{ $payment->catatan ?: 'Pembayaran sah melalui transfer perbankan / QRIS.' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 25px; color: #71717a; font-style: italic;">
                        Tidak ada transaksi pembayaran yang ditemukan dalam basis data sistem.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($payments->count() > 0)
            <tfoot>
                <tr style="background-color: #f4f4f5; font-weight: bold; border-top: 1.5px solid #000000; border-bottom: 1.5px solid #000000;">
                    <td colspan="3" class="text-right" style="text-transform: uppercase; font-size: 7.5pt;">Total Kas Masuk Terverifikasi:</td>
                    <td class="text-right font-mono">Rp {{ number_format($totalDanaMasuk, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Signature Block -->
    <table class="sign-table">
        <tr>
            <td style="width: 65%;">
                <div class="audit-note">
                    <strong>Catatan Dokumen &amp; Legalitas:</strong><br>
                    Laporan keuangan dan mutasi transaksi ini diekspor secara sistematis dari platform internal CRM {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}. Seluruh pembayaran telah terekam secara sah sesuai dengan rekening resmi perusahaan dan berlaku sebagai bukti audit akuntansi.
                </div>
            </td>
            <td style="width: 35%; text-align: center;">
                <div style="font-size: 8.5pt; color: #333333;">
                    {{ $settings->domicile_city ?? 'Jakarta' }}, {{ now()->translatedFormat('d F Y') }}
                </div>
                <div style="font-size: 8.5pt; font-weight: bold; text-transform: uppercase; margin-top: 2px;">
                    {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}
                </div>
                <div style="height: 50px; margin: 4px 0; text-align: center;">
                    @if(!empty($signatureBase64))
                        <img src="{{ $signatureBase64 }}" alt="Tanda Tangan" style="max-height: 50px; width: auto; max-width: 140px; display: inline-block;" />
                    @endif
                </div>
                <div style="font-size: 9pt; font-weight: bold; text-decoration: underline;">
                    {{ $settings->director_name ?? 'Manajemen VexaHost' }}
                </div>
                <div style="font-size: 7.5pt; color: #555555; text-transform: uppercase;">
                    {{ $settings->director_title ?? 'Finance & Executive Director' }}
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-top: 20px; border-top: 1px solid #e4e4e7; padding-top: 6px; font-size: 7.5pt; color: #71717a;">
        <tr>
            <td style="border: none; padding: 0;">
                Dokumen Resmi PT DESTINARA CHAKRAWALA ARTHA &bull; Sistem Terpadu VexaHost Admin
            </td>
            <td style="border: none; padding: 0; text-align: right;">
                Halaman 1 / 1 &bull; Dicetak: {{ now()->format('d/m/Y H:i') }} WIB
            </td>
        </tr>
    </table>

</body>
</html>

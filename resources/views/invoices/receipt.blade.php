<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi {{ $receiptNumber }} - {{ $project?->nama_project }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
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
            .receipt-wrapper {
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

        /* Receipt Wrapper */
        .receipt-wrapper {
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
            margin: 5px 0 20px 0;
        }
        .content-table {
            width: 100%;
            margin-top: 5px;
        }
        .content-table td {
            padding: 9px 0;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .label-cell {
            width: 28%;
            color: #555555;
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .value-cell {
            width: 72%;
            font-size: 10.5pt;
            color: #111111;
        }
        .amount-box {
            border: 2px solid #000000;
            background: #fafafa;
            padding: 10px 16px;
            display: inline-block;
            margin: 4px 0;
        }
        .amount-text {
            font-size: 15pt;
            font-weight: 900;
            letter-spacing: 0.5px;
        }
        .terbilang-box {
            font-style: italic;
            font-weight: bold;
            color: #222222;
            padding: 6px 12px;
            background: #f4f4f5;
            border-left: 3px solid #000000;
            font-size: 10pt;
            margin-top: 4px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border: 1.5px solid #000000;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .balance-table {
            margin-top: 20px;
            border-top: 1px solid #e4e4e7;
            border-bottom: 1px solid #e4e4e7;
            padding: 10px 0;
        }
        .balance-table td {
            padding: 5px 0;
            font-size: 9.5pt;
        }
        .sign-table {
            margin-top: 30px;
            width: 100%;
        }
        .sign-table td {
            vertical-align: top;
            font-size: 9.5pt;
        }
        .footer-note {
            font-size: 8pt;
            color: #666666;
            margin-top: 20px;
            border-top: 1px dashed #cccccc;
            padding-top: 8px;
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
            <a href="{{ isset($invoice) ? route('invoices.show', $invoice->id) : route('invoices.index') }}" class="action-btn secondary">
                &larr; Kembali ke Detail Tagihan
            </a>
            <div style="display: flex; align-items: center; gap: 8px; padding-left: 12px; border-left: 1px solid #e4e4e7;">
                <img src="{{ asset('images/logo.png') }}" alt="VexaHost" style="height: 26px; width: auto;">
                <span style="font-size: 13px; font-weight: 800; color: #18181b;">Vexa<span style="color: #059669;">Host</span></span>
            </div>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
            @php
                $cleanReceiptNo = str_replace('/', '-', $receiptNumber);
                $waShareText = rawurlencode("Halo Kak " . ($lead?->nama_kontak ?: $lead?->nama_usaha) . ",\n\nTerima kasih! Pembayaran {$payment->jenis_label} untuk proyek *{$project?->nama_project}* telah terverifikasi sah.\n\nNomor Kwitansi: {$receiptNumber}\nJumlah: Rp " . number_format($payment->jumlah, 0, ',', '.') . "\nStatus: LUNAS & TERVERIFIKASI\n\nSalam,\nVexaHost");
                $waPhone = preg_replace('/[^0-9]/', '', $lead?->kontak_wa ?? '');
            @endphp
            @if(!empty($waPhone))
                <a href="https://wa.me/{{ $waPhone }}?text={{ $waShareText }}" target="_blank" class="action-btn secondary">
                    Bagikan via WA
                </a>
            @endif
            <a href="{{ route('invoices.receipt', ['invoice' => $invoice->id ?? $payment->id, 'format' => 'pdf']) }}" class="action-btn secondary">
                Download PDF
            </a>
            <a href="{{ route('invoices.receipt', ['invoice' => $invoice->id ?? $payment->id, 'format' => 'word']) }}" class="action-btn secondary">
                Download Word
            </a>
            <button onclick="window.print()" class="action-btn" style="background: #059669; border-color: #059669;">
                Cetak Kwitansi
            </button>
        </div>
    </div>
@endif

<!-- Enterprise Kwitansi Sheet -->
<div class="receipt-wrapper">

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
                <div style="font-size: 18pt; font-weight: 900; letter-spacing: 1px; text-transform: uppercase;">
                    KWITANSI RESMI
                </div>
                <div style="font-size: 8.5pt; font-weight: 600; color: #555555; text-transform: uppercase;">
                    Official Payment Receipt
                </div>
                <div style="font-size: 10.5pt; font-weight: bold; margin-top: 4px;">
                    No: {{ $receiptNumber }}
                </div>
                <div style="margin-top: 6px;">
                    <span class="status-badge">
                        LUNAS / VERIFIED
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Receipt Details Form -->
    <table class="content-table">
        <tr>
            <td class="label-cell">Telah Terima Dari</td>
            <td class="value-cell">
                <div style="font-size: 12pt; font-weight: bold;">
                    {{ $lead?->nama_usaha ?? 'Klien VexaHost' }}
                </div>
                <div style="font-size: 9.5pt; color: #333333; margin-top: 2px;">
                    U.p: <strong>{{ $lead?->nama_kontak ?? '-' }}</strong> &bull; {{ $lead?->kontak_wa ?? '-' }}
                </div>
            </td>
        </tr>
        <tr>
            <td class="label-cell">Uang Sejumlah</td>
            <td class="value-cell">
                <div class="amount-box">
                    <span class="amount-text">Rp {{ number_format($payment->jumlah, 0, ',', '.') }},-</span>
                </div>
                <div class="terbilang-box">
                    # {{ strtoupper($terbilang) }} #
                </div>
            </td>
        </tr>
        <tr>
            <td class="label-cell">Untuk Pembayaran</td>
            <td class="value-cell">
                <div style="font-weight: bold;">
                    Pembayaran {{ $payment->jenis_label ?? 'Layanan' }} &mdash; Proyek "{{ $project?->nama_project ?? '-' }}"
                </div>
                <div style="font-size: 9pt; color: #555555; margin-top: 3px;">
                    Catatan: {{ $payment->catatan ?: 'Pembayaran sah melalui transfer / QRIS terverifikasi sistem.' }}
                </div>
            </td>
        </tr>
        <tr>
            <td class="label-cell">Metode Penerimaan</td>
            <td class="value-cell">
                <div style="font-size: 9.5pt;">
                    {{ $settings->bank_info_string }} / QRIS Resmi
                </div>
            </td>
        </tr>
    </table>

    <!-- Project Balance Summary -->
    <div style="margin-top: 15px;">
        <table class="balance-table">
            <tr>
                <td style="width: 33%; text-align: left;">
                    <span style="color: #666666; font-size: 8.5pt; text-transform: uppercase;">Total Nilai Proyek:</span><br>
                    <strong style="font-size: 10.5pt;">Rp {{ number_format($project?->harga ?? 0, 0, ',', '.') }}</strong>
                </td>
                <td style="width: 33%; text-align: center;">
                    <span style="color: #666666; font-size: 8.5pt; text-transform: uppercase;">Total Telah Diterima:</span><br>
                    <strong style="font-size: 10.5pt;">Rp {{ number_format($project?->total_terbayar ?? 0, 0, ',', '.') }}</strong>
                </td>
                <td style="width: 34%; text-align: right;">
                    <span style="color: #666666; font-size: 8.5pt; text-transform: uppercase;">Sisa Tagihan Proyek:</span><br>
                    <strong style="font-size: 10.5pt; {{ ($project?->sisa_tagihan ?? 0) <= 0 ? 'color: #000000;' : '' }}">
                        @if(($project?->sisa_tagihan ?? 0) <= 0)
                            Rp 0 (LUNAS PENUH)
                        @else
                            Rp {{ number_format($project?->sisa_tagihan ?? 0, 0, ',', '.') }}
                        @endif
                    </strong>
                </td>
            </tr>
        </table>
    </div>

    <!-- Signatures -->
    <table class="sign-table">
        <tr>
            <td style="width: 60%; vertical-align: bottom;">
                <div class="footer-note">
                    <strong>Catatan Bukti Pembayaran:</strong><br>
                    1. Kwitansi ini merupakan bukti pembayaran resmi yang sah dan diterbitkan secara digital oleh sistem {{ $settings->company_name ?? 'VexaHost' }}.<br>
                    2. Dokumen ini tidak memerlukan tanda tangan basah dan diakui secara sah oleh manajemen {{ $settings->company_name ?? 'VexaHost' }}.<br>
                    3. Pertanyaan administrasi: <strong>{{ $settings->email_support ?? 'vexahostcloudtech@gmail.com' }}</strong> / WA: <strong>{{ $settings->phone_support ?? '0858-0874-9131' }}</strong>.
                </div>
            </td>
            <td style="width: 40%; text-align: center; vertical-align: bottom;">
                <div style="font-size: 9pt; color: #444444;">
                    {{ $settings->domicile_city ?? 'Jakarta' }}, {{ $receiptDate }}
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
                    {{ $settings->director_title ?? 'Finance & Executive Director' }}
                </div>
            </td>
        </tr>
    </table>

</div>

</body>
</html>

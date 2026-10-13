<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Proyek - {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}</title>
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
        .footer-bar {
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px solid #e4e4e7;
            font-size: 7.5pt;
            color: #71717a;
            display: flex;
            justify-content: space-between;
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
                <div class="report-title">LAPORAN REKAPITULASI PROYEK</div>
                <div class="report-subtitle">Official Project Portfolio &amp; Financial Summary</div>
                <div class="report-meta">
                    Tanggal Cetak: <strong>{{ now()->translatedFormat('d F Y, H:i') }} WIB</strong><br>
                    Total Data: <strong>{{ $projects->count() }} Proyek</strong> &bull; Filter: <strong>Semua Status</strong>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider-double"></div>

    <!-- Summary Metrics -->
    @php
        $totalNilai = $projects->sum('harga');
        $totalTerbayar = $projects->sum('total_terbayar');
        $totalSisa = $projects->sum('sisa_tagihan');
    @endphp
    <table class="metrics-table">
        <tr>
            <td>
                <div class="metric-title">Total Portofolio Proyek</div>
                <div class="metric-value">{{ $projects->count() }} Kontrak</div>
            </td>
            <td>
                <div class="metric-title">Akumulasi Nilai Kontrak</div>
                <div class="metric-value">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="metric-title">Kas Masuk (Terbayar)</div>
                <div class="metric-value">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="metric-title">Sisa Tagihan (Piutang)</div>
                <div class="metric-value">Rp {{ number_format($totalSisa, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Main Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Nama Proyek &amp; URL</th>
                <th>Klien / Instansi</th>
                <th>Paket Layanan</th>
                <th class="text-right">Nilai Kontrak</th>
                <th class="text-right">Terbayar</th>
                <th class="text-right">Sisa Tagihan</th>
                <th class="text-center" style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($projects as $idx => $project)
                <tr>
                    <td class="text-center font-mono">{{ $idx + 1 }}</td>
                    <td>
                        <strong style="color: #000000;">{{ $project->nama_project }}</strong>
                        @if($project->link_website)
                            <div style="font-size: 7.5pt; color: #444444; margin-top: 1px;">{{ $project->link_website }}</div>
                        @endif
                    </td>
                    <td>
                        <div>{{ $project->lead?->nama_usaha ?? '-' }}</div>
                        <div style="font-size: 7.5pt; color: #555555;">{{ $project->lead?->nama_kontak }} &bull; {{ $project->lead?->kontak_wa }}</div>
                    </td>
                    <td>{{ $project->paket_label }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($project->harga, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($project->total_terbayar, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">
                        Rp {{ number_format($project->sisa_tagihan, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ in_array($project->status, ['selesai', 'dikerjakan']) ? 'badge-active' : '' }}">
                            {{ $project->status_label }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 25px; color: #71717a; font-style: italic;">
                        Tidak ada catatan proyek yang ditemukan dalam basis data sistem.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($projects->count() > 0)
            <tfoot>
                <tr style="background-color: #f4f4f5; font-weight: bold; border-top: 1.5px solid #000000; border-bottom: 1.5px solid #000000;">
                    <td colspan="4" class="text-right" style="text-transform: uppercase; font-size: 7.5pt;">Total Akumulasi:</td>
                    <td class="text-right font-mono">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($totalTerbayar, 0, ',', '.') }}</td>
                    <td class="text-right font-mono">Rp {{ number_format($totalSisa, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Signature & Validation Block -->
    <table class="sign-table">
        <tr>
            <td style="width: 65%;">
                <div class="audit-note">
                    <strong>Catatan Dokumen &amp; Legalitas:</strong><br>
                    Dokumen rekapitulasi ini diekspor secara sistematis dari platform internal CRM {{ $settings->company_name ?? 'PT DESTINARA CHAKRAWALA ARTHA' }}. Seluruh data finansial dan status pengerjaan telah melalui proses rekonsiliasi berkala dan sah digunakan sebagai arsip operasional dan perpajakan resmi perusahaan.
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

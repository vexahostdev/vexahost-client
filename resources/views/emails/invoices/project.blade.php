<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan Invoice Proyek</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #18181b;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f5; padding: 30px 15px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #e4e4e7;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #18181b; padding: 25px 30px; text-align: left;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding-right: 12px; vertical-align: middle;">
                                                    <img src="{{ $settings->logo_url ?: asset('images/logo.png') }}" alt="VexaHost" style="height: 36px; width: auto; display: block;" />
                                                </td>
                                                <td style="vertical-align: middle;">
                                                    <div style="font-size: 20px; font-weight: 900; color: #ffffff; letter-spacing: -0.5px;">
                                                        Vexa<span style="color: #BAFF39;">Host</span>
                                                    </div>
                                                    <div style="font-size: 11px; color: #a1a1aa; margin-top: 2px;">
                                                        {{ $settings->company_name ?: 'PT DESTINARA CHAKRAWALA ARTHA' }}
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td align="right">
                                        <span style="display: inline-block; padding: 4px 10px; background-color: #27272a; color: #BAFF39; font-size: 11px; font-weight: bold; border-radius: 9999px; border: 1px solid #5a8215;">
                                            Invoice Resmi
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 30px 30px 20px 30px;">
                            <h2 style="font-size: 18px; font-weight: 800; color: #09090b; margin: 0 0 12px 0;">
                                Halo Kak {{ $project->lead?->nama_kontak ?: 'Klien Terhormat' }},
                            </h2>
                            <p style="font-size: 13px; line-height: 1.6; color: #52525b; margin: 0 0 20px 0;">
                                Terlampir dokumen tagihan resmi (*Invoice*) untuk pengerjaan proyek <strong>{{ $project->nama_project }}</strong>. Anda dapat melakukan pembayaran langsung secara instan tanpa perlu repot login melalui tautan di bawah ini:
                            </p>

                            <!-- Invoice Details Table -->
                            <table width="100%" cellpadding="10" cellspacing="0" style="background-color: #fafafa; border: 1px solid #e4e4e7; border-radius: 12px; margin-bottom: 25px; font-size: 12px;">
                                <tr style="border-bottom: 1px solid #e4e4e7;">
                                    <td style="color: #71717a; width: 40%;">Nomor Dokumen:</td>
                                    <td style="font-weight: bold; color: #09090b;">{{ $invoice->invoice_number }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e4e4e7;">
                                    <td style="color: #71717a;">Proyek / Layanan:</td>
                                    <td style="font-weight: bold; color: #09090b;">{{ $project->nama_project }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #e4e4e7;">
                                    <td style="color: #71717a;">Total Nilai Proyek:</td>
                                    <td style="font-weight: bold; color: #09090b;">Rp {{ number_format($project->harga, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #71717a;">Sisa Tagihan:</td>
                                    <td style="font-weight: 900; color: #e11d48; font-size: 14px;">Rp {{ number_format($project->remaining_balance, 0, ',', '.') }}</td>
                                </tr>
                            </table>

                            <!-- Direct Pay CTA Button -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ $directPaymentUrl }}" target="_blank" style="display: inline-block; background-color: #09090b; color: #BAFF39; text-decoration: none; font-size: 13px; font-weight: 800; padding: 14px 32px; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.12);">
                                    ⚡ Bayar Tagihan Sekarang (Tanpa Login) &rarr;
                                </a>
                            </div>

                            <p style="font-size: 12px; color: #71717a; line-height: 1.5; margin: 20px 0 0 0;">
                                💡 <em>Kwitansi pelunasan resmi bertanda tangan digital akan terbit otomatis seketika setelah pembayaran Anda berhasil. Dokumen PDF lengkap juga terlampir pada email ini.</em>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #fafafa; border-top: 1px solid #e4e4e7; padding: 20px 30px; font-size: 11px; color: #71717a; text-align: center;">
                            <div>&copy; {{ date('Y') }} {{ $settings->company_name ?: 'PT DESTINARA CHAKRAWALA ARTHA' }}. Seluruh hak cipta dilindungi.</div>
                            <div style="margin-top: 4px;">WhatsApp Tim Finance: {{ $settings->phone_support ?: '0858-0874-9131' }} &bull; Email: {{ $settings->email_support ?: 'vexahostcloudtech@gmail.com' }}</div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

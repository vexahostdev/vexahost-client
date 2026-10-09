<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Resmi Pembayaran</title>
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
                                            LUNAS &amp; SAH
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
                                Terima kasih atas pembayaran Anda!
                            </h2>
                            <p style="font-size: 13px; line-height: 1.6; color: #52525b; margin: 0 0 20px 0;">
                                Pembayaran untuk <strong>{{ $payment->jenis_label ?? 'Layanan' }}</strong> telah kami terima dan diverifikasi. Terlampir dokumen resmi <strong>Kwitansi Pembayaran</strong> bertanda tangan digital.
                            </p>

                            <!-- Breakdown Table -->
                            <table width="100%" cellpadding="10" cellspacing="0" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; margin-bottom: 25px; font-size: 12px;">
                                <tr style="border-bottom: 1px solid #dcfce7;">
                                    <td style="color: #166534; width: 40%;">Nomor Kwitansi:</td>
                                    <td style="font-weight: bold; color: #14532d;">{{ $receiptNumber }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #dcfce7;">
                                    <td style="color: #166534;">Untuk Pembayaran:</td>
                                    <td style="font-weight: bold; color: #14532d;">{{ $payment->project?->nama_project ?? '-' }} ({{ $payment->jenis_label }})</td>
                                </tr>
                                <tr style="border-bottom: 1px solid #dcfce7;">
                                    <td style="color: #166534;">Tanggal Verifikasi:</td>
                                    <td style="font-weight: bold; color: #14532d;">{{ \Carbon\Carbon::parse($payment->tanggal)->translatedFormat('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="color: #166534;">Jumlah Diterima:</td>
                                    <td style="font-weight: 900; color: #15803d; font-size: 15px;">Rp {{ number_format($payment->jumlah, 0, ',', '.') }}</td>
                                </tr>
                            </table>

                            @if($receiptUrl)
                                <div style="text-align: center; margin: 25px 0;">
                                    <a href="{{ $receiptUrl }}" target="_blank" style="display: inline-block; background-color: #059669; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 800; padding: 12px 28px; border-radius: 12px; box-shadow: 0 4px 8px rgba(5,150,105,0.2);">
                                        📄 Buka Lembar Tagihan &amp; Kwitansi Online &rarr;
                                    </a>
                                </div>
                            @endif

                            <p style="font-size: 12px; color: #71717a; line-height: 1.5; margin: 20px 0 0 0;">
                                Dokumen PDF Kwitansi resmi terlampir pada email ini dan dapat disimpan sebagai bukti pembukuan sah Anda.
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\CompanySetting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    /**
     * Display the company & payment settings form.
     */
    public function edit()
    {
        $settings = CompanySetting::get();

        return view('admin.settings.company', compact('settings'));
    }

    /**
     * Update the company & payment settings.
     */
    public function update(Request $request)
    {
        $settings = CompanySetting::get();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'brand_name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'domicile_city' => 'required|string|max:100',
            'email_support' => 'nullable|email|max:255',
            'mail_support_name' => 'nullable|string|max:255',
            'mail_noreply_address' => 'nullable|email|max:255',
            'mail_noreply_name' => 'nullable|string|max:255',
            'mail_reply_to_address' => 'nullable|email|max:255',
            'email_company' => 'required|email|max:255',
            'email_internal_alert' => 'nullable|string|max:1000',
            'website_url' => 'nullable|string|max:255',
            'phone_support' => 'required|string|max:50',
            'phone_support_2' => 'nullable|string|max:50',
            'phone_admin_alerts' => 'required|string|max:255',
            'bank_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_account_holder' => 'required|string|max:255',
            'director_name' => 'nullable|string|max:255',
            'director_title' => 'nullable|string|max:255',
            'invoice_terms' => 'nullable|string',
            'qris_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'logo_image' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:4096',
            'signature_image' => 'nullable|file|mimes:png,webp|max:3072',
            'wa_api_url' => 'nullable|string|max:255',
            'wa_api_key' => 'nullable|string|max:500',
            'wa_sender_phone' => 'nullable|string|max:50',
        ], [
            'company_name.required' => 'Nama legal perusahaan wajib diisi.',
            'domicile_city.required' => 'Kota domisili dokumen wajib diisi.',
            'email_company.required' => 'Email perusahaan wajib diisi.',
            'bank_name.required' => 'Nama bank wajib diisi.',
            'bank_account_number.required' => 'Nomor rekening bank wajib diisi.',
            'bank_account_holder.required' => 'Nama pemilik rekening wajib diisi.',
            'qris_image.mimes' => 'Format file QRIS harus JPG, PNG, atau WEBP.',
            'qris_image.max' => 'Ukuran file QRIS maksimal 4MB.',
            'logo_image.mimes' => 'Format file logo harus JPG, PNG, atau WEBP.',
            'logo_image.max' => 'Ukuran file logo maksimal 4MB.',
            'signature_image.mimes' => 'Format file tanda tangan harus PNG atau WEBP transparan.',
            'signature_image.max' => 'Ukuran file tanda tangan maksimal 3MB.',
        ]);

        $allowedDomains = \App\Support\MailIdentity::allowedSenderDomains();
        $allowedStr = implode(', ', $allowedDomains);

        $validator->after(function ($validator) use ($request, $allowedDomains, $allowedStr) {
            if ($noreply = $request->input('mail_noreply_address')) {
                $domain = strtolower((string) substr(strrchr($noreply, '@'), 1));
                if (!in_array($domain, $allowedDomains, true)) {
                    $validator->errors()->add('mail_noreply_address', "Alamat pengirim harus memakai domain terverifikasi: {$allowedStr}");
                }
            }

            if ($support = $request->input('email_support')) {
                $domain = strtolower((string) substr(strrchr($support, '@'), 1));
                if (!in_array($domain, $allowedDomains, true)) {
                    $validator->errors()->add('email_support', "Alamat pengirim harus memakai domain terverifikasi: {$allowedStr}");
                }
            }

            if ($adminAlerts = $request->input('email_internal_alert')) {
                $parts = preg_split('/[,\r\n]+/', (string) $adminAlerts);
                $validEmails = [];
                foreach ($parts as $part) {
                    $trimmed = trim($part);
                    if ($trimmed === '') {
                        continue;
                    }
                    if (!filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                        $validator->errors()->add('email_internal_alert', "Alamat email tidak valid: {$trimmed}");
                    } else {
                        $validEmails[] = $trimmed;
                    }
                }
                if (empty($validEmails) && trim($adminAlerts) !== '') {
                    $validator->errors()->add('email_internal_alert', 'Minimal satu alamat email admin yang valid harus diisi.');
                }
            }
        });

        $validated = $validator->validate();

        // Handle QRIS Image Upload
        if ($request->hasFile('qris_image')) {
            $file = $request->file('qris_image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'qris_' . time() . '.' . $extension;
            $path = $file->storeAs('company_assets', $filename, 'public');


            $validated['qris_image_path'] = $path;
        }

        // Handle Logo Image Upload
        if ($request->hasFile('logo_image')) {
            $file = $request->file('logo_image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'logo_' . time() . '.' . $extension;
            $path = $file->storeAs('company_assets', $filename, 'public');


            $validated['logo_image_path'] = $path;
        }

        // Handle Signature Image Upload (Transparent PNG)
        if ($request->hasFile('signature_image')) {
            $file = $request->file('signature_image');
            $extension = $file->getClientOriginalExtension();
            $filename = 'signature_' . time() . '.' . $extension;
            $path = $file->storeAs('company_assets', $filename, 'public');


            $validated['signature_image_path'] = $path;
        }

        $settings->update($validated);
        \App\Support\MailIdentity::flushCache();

        ActivityLogger::log('update_settings', 'Memperbarui profil perusahaan, rekening bank, barcode QRIS, tanda tangan digital, identitas email, dan integrasi WhatsApp Gateway', 'CompanySetting', $settings->id);

        return back()->with('success', 'Pengaturan profil perusahaan, rekening pembayaran, QRIS, tanda tangan digital, email pengirim, dan WhatsApp Gateway berhasil diperbarui!');
    }

    /**
     * Send a test email synchronously to verify SMTP delivery and MailIdentity sender.
     */
    public function testMail(Request $request)
    {
        $request->validate([
            'type' => 'required|in:noreply,support',
            'target_email' => 'required|email|max:255',
        ], [
            'type.required' => 'Pilih jenis identitas pengirim email tes.',
            'target_email.required' => 'Alamat email penerima tes wajib diisi.',
            'target_email.email' => 'Format email penerima tes tidak valid.',
        ]);

        $type = $request->input('type');
        $to = $request->input('target_email');

        $identity = $type === 'support' ? \App\Support\MailIdentity::support() : \App\Support\MailIdentity::noreply();
        $replyTo = \App\Support\MailIdentity::replyTo();

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Ini adalah email uji coba sinkron dari VexaHost Build ({$identity['name']}).\n\n"
                . "Tipe: {$type}\n"
                . "Dari: {$identity['name']} <{$identity['address']}>\n"
                . "Reply-To: {$replyTo['name']} <{$replyTo['address']}>\n"
                . "Tujuan: {$to}\n"
                . "Waktu: " . now()->format('Y-m-d H:i:s') . "\n\n"
                . "Jika Anda menerima email ini, konfigurasi Brevo SMTP dan pengirim domain Anda berfungsi dengan baik.",
                function ($m) use ($identity, $replyTo, $to, $type) {
                    $m->to($to)
                      ->from($identity['address'], $identity['name'])
                      ->replyTo($replyTo['address'], $replyTo['name'])
                      ->subject("[TEST] Uji Coba Pengiriman Email ({$type}) - " . config('app.name'));
                }
            );

            ActivityLogger::log('test_email', "Uji kirim email ({$type}) ke {$to} berhasil", 'CompanySetting');

            return back()->with('success', "Email tes ({$type}) berhasil dikirim ke {$to} dari {$identity['address']}!");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Gagal mengirim email tes: " . $e->getMessage());

            return back()->with('error', "Gagal mengirim email tes: Terjadi kendala koneksi ke server email relay (SMTP). Pastikan jaringan dan kredensial mailer valid.");
        }
    }

    /**
     * Send a test WhatsApp message to verify VexaHost WA Gateway connection.
     */
    public function testWhatsApp(Request $request, \App\Services\WhatsApp\WhatsAppService $waService)
    {
        $settings = CompanySetting::get();
        $targetPhone = $request->input('test_phone');
        if (empty($targetPhone)) {
            $targetPhone = $settings->phone_support ?: '085808749131';
        }

        $now = now()->translatedFormat('d F Y, H:i:s');
        $company = $settings->company_name ?: 'PT DESTINARA CHAKRAWALA ARTHA';
        $brand = $settings->brand_name ?: 'VexaHost';
        $message = "🤖 *UJI KONEKSI WHATSAPP GATEWAY (VEXAHOST WA)*\n\n"
                 . "Halo! Ini adalah pesan uji coba integrasi dari sistem *{$company}* ({$brand}).\n"
                 . "Waktu: {$now} WIB\n\n"
                 . "Status: *Koneksi WhatsApp Gateway BERHASIL terhubung dan aktif!* ✅🚀";

        $res = $waService->sendWhatsApp($targetPhone, $message, null, 'test_ping');

        if ($res['success'] ?? false) {
            ActivityLogger::log('test_wa_gateway', "Uji kirim pesan WA Gateway ke {$targetPhone} berhasil", 'CompanySetting');
            return back()->with('success', "Pesan uji coba berhasil dikirim ke WhatsApp {$targetPhone}! Integrasi WhatsApp Gateway berjalan lancar.");
        }

        return back()->with('error', "Gagal mengirim pesan uji coba WhatsApp ke {$targetPhone}: " . ($res['message'] ?? 'Periksa API Key atau sesi VexaHost WA Anda.'));
    }
}

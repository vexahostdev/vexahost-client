<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'brand_name',
        'tagline',
        'domicile_city',
        'email_support',
        'email_company',
        'email_internal_alert',
        'mail_noreply_address',
        'mail_noreply_name',
        'mail_support_name',
        'mail_reply_to_address',
        'website_url',
        'phone_support',
        'phone_support_2',
        'phone_admin_alerts',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'qris_image_path',
        'logo_image_path',
        'signature_image_path',
        'director_name',
        'director_title',
        'invoice_terms',
        'wa_api_url',
        'wa_api_key',
        'wa_sender_phone',
    ];

    /**
     * Get or create the singleton instance of company settings.
     */
    public static function get(): self
    {
        $setting = static::first();

        if (! $setting) {
            $setting = static::create([
                'company_name' => 'PT DESTINARA CHAKRAWALA ARTHA',
                'brand_name' => 'VexaHost',
                'tagline' => 'Cloud Hosting & Jasa Pembuatan Website',
                'domicile_city' => 'Jakarta',
                'email_support' => 'support@vexahostcloud.my.id',
                'email_company' => 'support@vexahostcloud.my.id',
                'email_internal_alert' => 'support@vexahostcloud.my.id,vexahostcloudtech@gmail.com',
                'website_url' => 'https://vexahostcloud.my.id',
                'phone_support' => '0858-0874-9131',
                'phone_admin_alerts' => '085808749131',
                'logo_image_path' => 'images/logo.png',
                'director_title' => 'Direktur',
                'invoice_terms' => "1. Pembayaran resmi hanya sah apabila ditransfer ke rekening resmi VexaHost yang tercantum pada invoice ini.\n2. Kwitansi resmi lunas bertanda tangan digital akan diterbitkan otomatis setelah pembayaran diverifikasi.\n3. Untuk bantuan teknis atau administrasi penagihan, hubungi WhatsApp: 0858-0874-9131.",
                'wa_api_url' => config('whatsapp.api_url', 'https://wa.vexahostcloud.my.id/api/v1/messages/text'),
                'wa_api_key' => config('whatsapp.api_key') ?: null,
                'wa_sender_phone' => '0858-0874-9131',
            ]);
        } elseif (empty($setting->wa_api_url)) {
            $setting->update(['wa_api_url' => config('whatsapp.api_url', 'https://wa.vexahostcloud.my.id/api/v1/messages/text')]);
        }

        return $setting;
    }

    /**
     * Get standard bank info string for WA captions and footer texts.
     */
    public function getBankInfoStringAttribute(): string
    {
        if (empty($this->bank_account_number)) {
            return 'Rekening pembayaran belum diatur';
        }

        return trim("{$this->bank_name} {$this->bank_account_number} a.n {$this->bank_account_holder}");
    }

    /**
     * Resolve an image path (storage/public) into a base64 data URI for PDF rendering.
     */
    protected function imageToBase64(?string $path, ?string $fallback = null, string $defaultMime = 'image/png'): ?string
    {
        foreach (array_filter([$path, $fallback]) as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                $mime = Storage::disk('public')->mimeType($candidate) ?: $defaultMime;

                return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('public')->get($candidate));
            }

            $publicPath = public_path($candidate);
            if (file_exists($publicPath)) {
                $mime = mime_content_type($publicPath) ?: $defaultMime;

                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($publicPath));
            }
        }

        return null;
    }

    /**
     * Resolve an image path (storage/public) into a web URL.
     */
    protected function imageToUrl(?string $path, ?string $fallback = null): ?string
    {
        foreach (array_filter([$path, $fallback]) as $candidate) {
            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->url($candidate);
            }
            if (file_exists(public_path($candidate))) {
                return asset($candidate);
            }
        }

        return null;
    }

    public function getQrisBase64Attribute(): ?string
    {
        return $this->imageToBase64($this->qris_image_path, null, 'image/jpeg');
    }

    public function getLogoBase64Attribute(): ?string
    {
        return $this->imageToBase64($this->logo_image_path, 'images/logo.png');
    }

    public function getSignatureBase64Attribute(): ?string
    {
        return $this->imageToBase64($this->signature_image_path);
    }

    public function getQrisUrlAttribute(): ?string
    {
        return $this->imageToUrl($this->qris_image_path);
    }

    public function getLogoUrlAttribute(): string
    {
        return $this->imageToUrl($this->logo_image_path, 'images/logo.png') ?? asset('images/logo.png');
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->imageToUrl($this->signature_image_path);
    }

    /**
     * Get array of admin alert phone numbers.
     */
    public function getAdminAlertPhonesArrayAttribute(): array
    {
        $phones = explode(',', (string) $this->phone_admin_alerts);

        return array_values(array_filter(array_map('trim', $phones)));
    }
}

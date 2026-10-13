<?php

namespace App\Support;

use App\Models\CompanySetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Service resolver identitas pengirim email untuk VexaHost Build (Client Portal & CRM).
 *
 * Mengikuti spesifikasi migrasi domain:
 *  - noreply: pengirim default notifikasi dan invoice/receipt otomatis
 *  - support: pengirim email percakapan (tiket)
 *  - replyTo: Reply-To untuk semua email (selalu mengarah ke support)
 *  - adminRecipients: daftar penerima email alert admin (array)
 *  - contactEmail: alamat email support untuk ditampilkan ke klien
 *
 * Prioritas resolusi:
 *  DB (company_settings) -> config/env -> default hardcode aman
 */
class MailIdentity
{
    public const CACHE_KEY = 'mail_identity';
    public const CACHE_TTL = 300; // 5 menit

    /**
     * Identitas pengirim default (noreply).
     *
     * @return array{address: string, name: string, source: string}
     */
    public static function noreply(): array
    {
        $data = static::getResolvedData();

        return [
            'address' => $data['noreply_address'],
            'name' => $data['noreply_name'],
            'source' => $data['sources']['noreply'] ?? 'config',
        ];
    }

    /**
     * Identitas pengirim dukungan/percakapan (support).
     *
     * @return array{address: string, name: string, source: string}
     */
    public static function support(): array
    {
        $data = static::getResolvedData();

        return [
            'address' => $data['support_address'],
            'name' => $data['support_name'],
            'source' => $data['sources']['support'] ?? 'config',
        ];
    }

    /**
     * Identitas Reply-To untuk seluruh email keluar.
     *
     * @return array{address: string, name: string, source: string}
     */
    public static function replyTo(): array
    {
        $data = static::getResolvedData();

        return [
            'address' => $data['reply_to_address'],
            'name' => $data['reply_to_name'],
            'source' => $data['sources']['reply_to'] ?? 'config',
        ];
    }

    /**
     * Daftar alamat email penerima notifikasi admin / internal.
     *
     * @return array<int, string>
     */
    public static function adminRecipients(): array
    {
        $data = static::getResolvedData();

        return $data['admin_recipients'];
    }

    /**
     * Alamat email kontak yang aman ditampilkan ke klien.
     */
    public static function contactEmail(): string
    {
        return static::support()['address'];
    }

    /**
     * Daftar domain pengirim yang diizinkan.
     *
     * @return array<int, string>
     */
    public static function allowedSenderDomains(): array
    {
        $domains = (array) config('mail.allowed_sender_domains', ['vexahostcloud.my.id']);

        return !empty($domains) ? $domains : ['vexahostcloud.my.id'];
    }

    /**
     * Validasi domain pengirim terhadap daftar domain yang diizinkan di Brevo.
     */
    public static function isDomainAllowed(string $address, ?array $allowedDomains = null): bool
    {
        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($allowedDomains === null) {
            $allowedDomains = static::allowedSenderDomains();
        }

        if (empty($allowedDomains)) {
            $allowedDomains = ['vexahostcloud.my.id'];
        }

        $parts = explode('@', $address);
        $domain = strtolower(end($parts));

        foreach ($allowedDomains as $allowed) {
            $allowed = strtolower(trim((string) $allowed));
            if ($allowed === '') {
                continue;
            }
            if ($domain === $allowed || str_ends_with($domain, '.' . $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hapus cache identitas surel.
     */
    public static function flushCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }

    /**
     * Resolusi identitas secara defensif dengan prioritas: DB -> config/env -> default aman.
     */
    protected static function getResolvedData(): array
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, function () {
            return static::resolveFreshData();
        });
    }

    public static function resolveFreshData(): array
    {
        $allowedDomains = static::allowedSenderDomains();

        $configNoreplyAddress = config('mail.from.address') ?: 'noreply@vexahostcloud.my.id';
        $configNoreplyName = config('mail.from.name') ?: 'VexaHost Build';

        $configSupportAddress = config('mail.support.address') ?: 'support@vexahostcloud.my.id';
        $configSupportName = config('mail.support.name') ?: 'VexaHost Build Support';

        $configReplyToAddress = config('mail.reply_to.address') ?: $configSupportAddress;
        $configReplyToName = config('mail.reply_to.name') ?: $configSupportName;

        $dbNoreplyAddress = null;
        $dbNoreplyName = null;
        $dbSupportAddress = null;
        $dbSupportName = null;
        $dbReplyToAddress = null;
        $dbAdminAddresses = [];

        try {
            if (Schema::hasTable('company_settings')) {
                $setting = CompanySetting::first();
                if ($setting) {
                    if (isset($setting->mail_noreply_address)) {
                        $dbNoreplyAddress = trim((string) $setting->mail_noreply_address);
                    }
                    if (isset($setting->mail_noreply_name)) {
                        $dbNoreplyName = trim((string) $setting->mail_noreply_name);
                    }
                    if (isset($setting->email_support)) {
                        $dbSupportAddress = trim((string) $setting->email_support);
                    }
                    if (isset($setting->mail_support_name)) {
                        $dbSupportName = trim((string) $setting->mail_support_name);
                    }
                    if (isset($setting->mail_reply_to_address)) {
                        $dbReplyToAddress = trim((string) $setting->mail_reply_to_address);
                    }
                    if (!empty($setting->email_internal_alert)) {
                        $dbAdminAddresses = preg_split('/[\r\n,;]+/', (string) $setting->email_internal_alert) ?: [];
                    }
                }
            }
        } catch (\Throwable) {
            // Defensif: jika DB bermasalah / belum migrasi, lanjutkan dengan config
        }

        $sources = [
            'noreply' => 'config',
            'support' => 'config',
            'reply_to' => 'config',
            'admin' => 'config',
        ];

        // Resolusi Noreply
        if ($dbNoreplyAddress !== null && $dbNoreplyAddress !== '') {
            if (static::isDomainAllowed($dbNoreplyAddress, $allowedDomains)) {
                $resolvedNoreplyAddress = $dbNoreplyAddress;
                $sources['noreply'] = 'db';
            } else {
                Log::warning("Alamat noreply dari DB [{$dbNoreplyAddress}] diabaikan karena domain tidak terverifikasi. Fallback ke konfigurasi.");
                $resolvedNoreplyAddress = $configNoreplyAddress;
            }
        } else {
            $resolvedNoreplyAddress = $configNoreplyAddress;
        }

        if ($dbNoreplyName !== null && $dbNoreplyName !== '') {
            $resolvedNoreplyName = $dbNoreplyName;
            $sources['noreply'] = 'db';
        } else {
            $resolvedNoreplyName = $configNoreplyName;
        }

        // Resolusi Support
        if ($dbSupportAddress !== null && $dbSupportAddress !== '') {
            if (static::isDomainAllowed($dbSupportAddress, $allowedDomains)) {
                $resolvedSupportAddress = $dbSupportAddress;
                $sources['support'] = 'db';
            } else {
                Log::warning("Alamat support dari DB [{$dbSupportAddress}] diabaikan karena domain tidak terverifikasi. Fallback ke konfigurasi.");
                $resolvedSupportAddress = $configSupportAddress;
            }
        } else {
            $resolvedSupportAddress = $configSupportAddress;
        }

        if ($dbSupportName !== null && $dbSupportName !== '') {
            $resolvedSupportName = $dbSupportName;
            $sources['support'] = 'db';
        } else {
            $resolvedSupportName = $configSupportName;
        }

        // Resolusi Reply-To
        if ($dbReplyToAddress !== null && $dbReplyToAddress !== '') {
            if (filter_var($dbReplyToAddress, FILTER_VALIDATE_EMAIL)) {
                $resolvedReplyToAddress = $dbReplyToAddress;
                $sources['reply_to'] = 'db';
            } else {
                $resolvedReplyToAddress = $configReplyToAddress;
            }
        } else {
            $resolvedReplyToAddress = $configReplyToAddress;
        }
        $resolvedReplyToName = $resolvedSupportName;

        // Resolusi Admin Recipients:
        // 1. Jika DB tidak kosong setelah dibersihkan, gunakan HANYA DB.
        // 2. Jika kosong, gunakan HANYA config('mail.admin_addresses').
        // 3. Jika itu juga kosong, gunakan fallback hardcode [support@, gmail].
        $cleanDbAdmins = [];
        foreach ($dbAdminAddresses as $item) {
            $email = trim((string) $item);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $cleanDbAdmins[] = strtolower($email);
            }
        }
        $cleanDbAdmins = array_values(array_unique($cleanDbAdmins));

        if (!empty($cleanDbAdmins)) {
            $cleanAdmins = $cleanDbAdmins;
            $sources['admin'] = 'db';
        } else {
            $configAdmins = [];
            if (!empty(config('mail.admin_addresses'))) {
                foreach ((array) config('mail.admin_addresses') as $item) {
                    $email = trim((string) $item);
                    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $configAdmins[] = strtolower($email);
                    }
                }
                $configAdmins = array_values(array_unique($configAdmins));
            }

            if (!empty($configAdmins)) {
                $cleanAdmins = $configAdmins;
                $sources['admin'] = 'config';
            } else {
                $cleanAdmins = ['support@vexahostcloud.my.id', 'vexahostcloudtech@gmail.com'];
                $sources['admin'] = 'hardcode';
            }
        }

        return [
            'noreply_address' => $resolvedNoreplyAddress,
            'noreply_name' => $resolvedNoreplyName,
            'support_address' => $resolvedSupportAddress,
            'support_name' => $resolvedSupportName,
            'reply_to_address' => $resolvedReplyToAddress,
            'reply_to_name' => $resolvedReplyToName,
            'admin_recipients' => $cleanAdmins,
            'sources' => $sources,
        ];
    }
}

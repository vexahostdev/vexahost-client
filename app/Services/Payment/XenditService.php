<?php

namespace App\Services\Payment;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    protected const BASE_URL = 'https://api.xendit.co';

    public static function getSecretKey(): ?string
    {
        return config('services.xendit.secret_key') ?: env('XENDIT_SECRET_KEY');
    }

    public static function getWebhookToken(): ?string
    {
        return config('services.xendit.webhook_token') ?: env('XENDIT_WEBHOOK_TOKEN');
    }

    public static function isConfigured(): bool
    {
        return !empty(self::getSecretKey());
    }

    /**
     * Map channel pembayaran khusus BuildClient (Hanya 3 channel yang diizinkan):
     * 1. QRIS
     * 2. Mandiri VA
     * 3. BNI VA
     */
    public static function mapChannel(?string $channel = null): array
    {
        return match (strtolower((string) $channel)) {
            'qris'                  => ['QRIS'],
            'mandiri', 'mandiri_va' => ['MANDIRI'],
            'bni', 'bni_va'         => ['BNI'],
            default                 => ['QRIS', 'MANDIRI', 'BNI'],
        };
    }

    /**
     * Buat invoice pembayaran di Xendit.
     * Menggunakan prefix BC- pada external_id untuk routing sentral VexaHost.
     */
    public static function createInvoice(Invoice $invoice, string $paymentType = 'full', string $channel = 'online_payment'): array
    {
        $secretKey = self::getSecretKey();
        if (empty($secretKey)) {
            return [
                'success' => false,
                'error'   => 'Kredensial Xendit belum dikonfigurasi pada Client Portal.',
            ];
        }

        $paymentMethods = self::mapChannel($channel);

        if (empty($invoice->payment_token)) {
            $invoice->payment_token = \Illuminate\Support\Str::random(40);
            $invoice->saveQuietly();
        }

        $projectName = $invoice->project?->name
            ?: ($invoice->subscription?->lead?->nama_usaha ?: ($invoice->title ?: 'Layanan VexaHost'));

        // Hitung nominal berdasarkan tipe: DP (50%) atau Pelunasan (balance_due)
        if ($paymentType === 'dp') {
            $nominal = round((float) $invoice->amount / 2);
            $desc = "Pembayaran Uang Muka (DP 50%) Invoice #{$invoice->invoice_number} ({$projectName})";
        } else {
            $nominal = (float) ($invoice->balance_due > 0 ? $invoice->balance_due : $invoice->amount);
            $desc = "Pembayaran Invoice #{$invoice->invoice_number} ({$projectName})";
        }

        $externalId = "BC-INV-{$invoice->id}-{$paymentType}-" . time();
        $redirectUrl = route('invoices.pay', $invoice->payment_token);

        $clientName = $invoice->client?->name
            ?: ($invoice->project?->lead?->nama_kontak ?: ($invoice->subscription?->lead?->nama_kontak ?: 'Klien'));
        $clientEmail = $invoice->client?->email
            ?: ($invoice->project?->lead?->email ?: ($invoice->subscription?->lead?->email ?: 'finance@vexahost.id'));
        $rawPhone = $invoice->client?->phone
            ?: ($invoice->project?->lead?->kontak_wa ?: ($invoice->subscription?->lead?->kontak_wa ?: null));
        $clientPhone = $rawPhone ? preg_replace('/[^0-9]/', '', $rawPhone) : null;
        if ($clientPhone && strlen($clientPhone) < 10) {
            $clientPhone = null;
        }

        $payload = [
            'external_id'          => $externalId,
            'amount'               => $nominal,
            'description'          => $desc,
            'invoice_duration'     => 86400, // 24 jam
            'payment_methods'      => $paymentMethods,
            'currency'             => 'IDR',
            'payer_email'          => $clientEmail,
            'success_redirect_url' => $redirectUrl,
            'failure_redirect_url' => $redirectUrl,
            'customer' => array_filter([
                'given_names'   => $clientName,
                'email'         => $clientEmail,
                'mobile_number' => !empty($clientPhone) ? (string) $clientPhone : null,
            ]),
            'items' => [
                [
                    'name'     => $desc,
                    'quantity' => 1,
                    'price'    => $nominal,
                    'category' => 'Software & Web Development',
                ]
            ],
            'metadata' => [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'payment_token'  => $invoice->payment_token,
                'payment_type'   => $paymentType,
                'channel'        => $channel,
            ],
        ];

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->timeout(15)
                ->post(self::BASE_URL . '/v2/invoices', $payload);

            if (! $response->successful()) {
                Log::error('Xendit BuildClient create invoice failed', [
                    'status'   => $response->status(),
                    'response' => $response->json(),
                    'invoice'  => $invoice->invoice_number,
                ]);

                return [
                    'success' => false,
                    'error'   => $response->json()['message'] ?? 'Gagal membuat tagihan Xendit.',
                ];
            }

            $data = $response->json();
            $invoiceUrl = $data['invoice_url'] ?? '';
            $invoiceId = $data['id'] ?? '';

            $invoice->update([
                'payment_method'             => $channel,
                'payment_type'               => $paymentType,
                'payment_amount_transferred' => $nominal,
                'payment_url'                => $invoiceUrl,
                'payment_reference'          => $invoiceId,
                'payment_payload'            => $data,
            ]);

            Log::info("Xendit invoice created for BuildClient {$invoice->invoice_number}", [
                'external_id' => $externalId,
                'invoice_url' => $invoiceUrl,
            ]);

            return [
                'success'     => true,
                'invoice_url' => $invoiceUrl,
                'invoice_id'  => $invoiceId,
            ];
        } catch (\Throwable $e) {
            Log::error("Xendit BuildClient exception: " . $e->getMessage(), [
                'invoice' => $invoice->invoice_number,
            ]);

            return [
                'success' => false,
                'error'   => 'Terjadi kesalahan menghubungi server Xendit: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sinkronisasi status pembayaran langsung dari Xendit API saat klien kembali ke halaman invoice.
     */
    public static function checkAndSyncStatus(Invoice $invoice): bool
    {
        if ($invoice->status === 'paid' || empty($invoice->payment_reference)) {
            return false;
        }

        $secretKey = self::getSecretKey();
        if (empty($secretKey)) {
            return false;
        }

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->timeout(8)
                ->get(self::BASE_URL . '/v2/invoices/' . $invoice->payment_reference);

            if (! $response->successful()) {
                return false;
            }

            $data = $response->json();
            $status = strtoupper((string) ($data['status'] ?? ''));

            if (in_array($status, ['PAID', 'SETTLED'], true)) {
                app(\App\Http\Controllers\Api\XenditWebhookController::class)->settleInvoice($invoice, $data);
                $invoice->refresh();
                return true;
            }
        } catch (\Throwable $e) {
            Log::warning('Xendit BuildClient checkAndSyncStatus failed: ' . $e->getMessage());
        }

        return false;
    }
}

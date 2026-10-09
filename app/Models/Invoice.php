<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    protected $fillable = [
        'project_id',
        'subscription_id',
        'client_id',
        'invoice_number',
        'payment_token',
        'title',
        'amount',
        'paid_amount',
        'balance_due',
        'status',
        'due_date',
        'payment_method',
        'payment_url',
        'payment_reference',
        'payment_payload',
        'payment_type',
        'payment_amount_transferred',
        'payment_proof',
        'payment_notes',
        'payment_proof_uploaded_at',
        'verified_at',
        'verified_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->payment_token)) {
                $invoice->payment_token = bin2hex(random_bytes(20));
            }
        });
    }

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'payment_amount_transferred' => 'decimal:2',
        'due_date' => 'date',
        'payment_payload' => 'array',
        'payment_proof_uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(MaintenanceSubscription::class, 'subscription_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get or generate secure public payment URL for this invoice (No Login Required).
     */
    public function getDirectPaymentUrl(): string
    {
        if (empty($this->payment_token)) {
            $this->payment_token = bin2hex(random_bytes(20));
            $this->saveQuietly();
        }

        return route('invoices.pay', $this->payment_token);
    }

    /**
     * Get human-readable status badge info.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'paid' => [
                'label' => 'Lunas',
                'class' => 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                'icon' => 'check_circle',
            ],
            'partially_paid' => [
                'label' => 'DP Diterima',
                'class' => 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                'icon' => 'payments',
            ],
            'verifying' => [
                'label' => 'Menunggu Verifikasi',
                'class' => 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-800',
                'icon' => 'schedule',
            ],
            default => [
                'label' => 'Menunggu Pembayaran',
                'class' => 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 border-rose-200 dark:border-rose-800',
                'icon' => 'pending',
            ],
        };
    }

    /**
     * URL to access payment proof.
     */
    public function getPaymentProofUrlAttribute(): ?string
    {
        if (empty($this->payment_proof)) {
            return null;
        }

        return Storage::url($this->payment_proof);
    }
}

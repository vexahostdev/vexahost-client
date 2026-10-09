<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'project_id',
        'harga_bulanan',
        'status',
        'tanggal_mulai',
        'tanggal_jatuh_tempo_berikutnya',
        'terakhir_diingatkan_at',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'harga_bulanan' => 'integer',
            'tanggal_mulai' => 'date',
            'tanggal_jatuh_tempo_berikutnya' => 'date',
            'terakhir_diingatkan_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Invoice::class, 'subscription_id');
    }

    public function latestInvoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Invoice::class, 'subscription_id')->latestOfMany();
    }

    /**
     * Dapatkan atau buat tagihan invoice aktif untuk periode maintenance ini.
     */
    public function getOrCreateInvoice(): Invoice
    {
        $invoiceNumber = 'INV-MNT/' . now()->format('Ym') . '/' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
        $dueDate = $this->tanggal_jatuh_tempo_berikutnya ?: now()->addDays(7);
        $clientId = $this->project?->client_id ?? $this->lead?->user_id;

        if (!$clientId) {
            $clientId = \App\Models\User::role('client')->first()?->id ?? 1;
        }

        $invoice = Invoice::where('subscription_id', $this->id)
            ->where('invoice_number', $invoiceNumber)
            ->first();

        if ($invoice) {
            return $invoice;
        }

        return Invoice::create([
            'project_id'      => $this->project_id,
            'subscription_id' => $this->id,
            'client_id'       => $clientId,
            'invoice_number'  => $invoiceNumber,
            'title'           => 'Invoice Pemeliharaan & Server - ' . ($this->lead?->nama_usaha ?: ($this->project?->name ?: 'Website')),
            'amount'          => $this->harga_bulanan,
            'paid_amount'     => 0,
            'balance_due'     => $this->harga_bulanan,
            'status'          => 'unpaid',
            'due_date'        => $dueDate,
            'payment_token'   => \Illuminate\Support\Str::random(40),
        ]);
    }

    /**
     * Check if reminder is due (H-3 before next due date).
     */
    public function isReminderDue(): bool
    {
        if ($this->status !== 'aktif' || !$this->tanggal_jatuh_tempo_berikutnya) {
            return false;
        }

        $reminderDate = $this->tanggal_jatuh_tempo_berikutnya->copy()->subDays(3);
        $today = now()->startOfDay();

        // If today is on or after H-3 and before/on due date, and hasn't been reminded today
        if ($today->greaterThanOrEqualTo($reminderDate->startOfDay())) {
            if (!$this->terakhir_diingatkan_at || $this->terakhir_diingatkan_at->diffInDays(now()) >= 20) {
                return true;
            }
        }

        return false;
    }
}

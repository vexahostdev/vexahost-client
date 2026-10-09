<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\WhatsApp\PortalWhatsAppTemplates;
use App\Services\WhatsApp\WhatsAppService;
use App\Services\WhatsApp\WhatsAppTemplates;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Penghubung internal Admin Panel (CRM) ↔ Client Panel.
 *
 * Menggantikan sinkronisasi HTTP lama antara dua aplikasi terpisah:
 * - provisionClient()     : proyek deal di CRM → akun klien + tugas Kanban + invoice
 * - refreshInvoice()      : pembayaran CRM → angka invoice klien
 * - recordInvoicePayment(): verifikasi bukti bayar klien → pembayaran CRM
 * - applyKanbanStatus()   : status Kanban → status proyek CRM + notifikasi WA
 */
class ProjectLifecycleService
{
    public function __construct(protected WhatsAppService $waService)
    {
    }

    /**
     * Nomor invoice standar satu proyek.
     */
    public function invoiceNumberFor(Project $project): string
    {
        return 'INV/' . ($project->created_at ?? now())->format('Ym') . '/' . str_pad((string) $project->id, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat / tautkan akun klien untuk proyek, siapkan tugas Kanban & invoice,
     * lalu kirim undangan login via WhatsApp.
     */
    public function provisionClient(Project $project, bool $sendWaInvite = true): array
    {
        $lead = $project->lead;
        if (! $lead) {
            return [
                'success' => false,
                'message' => 'Data prospek/lead tidak ditemukan untuk proyek ini.',
            ];
        }

        // 1. Email klien (dibuatkan otomatis bila kosong)
        $clientEmail = $lead->email;
        if (empty($clientEmail)) {
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $lead->nama_usaha ?: 'klien'));
            $clientEmail = $cleanName . $lead->id . '@client.vexahostcloud.my.id';
            $lead->update(['email' => $clientEmail]);
        }

        $normalizedPhone = ! empty($lead->kontak_wa)
            ? $this->waService->normalizePhoneNumber($lead->kontak_wa)
            : null;

        // 2. Akun klien
        $user = $project->client ?: User::where('email', $clientEmail)->first();
        $isNewUser = false;
        $rawPassword = null;

        if ($user) {
            if (empty($user->phone) && $normalizedPhone) {
                $user->update(['phone' => $normalizedPhone]);
            }
        } else {
            $isNewUser = true;
            $rawPassword = 'VH' . random_int(100000, 999999);

            $user = User::create([
                'name' => $lead->nama_kontak ?: $lead->nama_usaha,
                'email' => $clientEmail,
                'phone' => $normalizedPhone,
                'password' => Hash::make($rawPassword),
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        if (! $user->hasRole('client')) {
            $user->assignRole('client');
        }

        $project->forceFill([
            'client_id' => $user->id,
            'client_provisioned_at' => now(),
            'description' => $project->description
                ?: "Paket: {$project->paket_label} — Nilai: Rp " . number_format($project->harga, 0, ',', '.'),
        ])->save();

        // 3. Tugas Kanban utama
        if (! $project->tasks()->exists()) {
            Task::create([
                'project_id' => $project->id,
                'name' => 'Pengerjaan ' . $project->name,
                'description' => 'Tugas utama pengerjaan website.',
                'status' => $this->kanbanStatusFor($project->status),
                'priority' => 'medium',
                'assignee_id' => $project->manager_id,
            ]);
        }

        // 4. Invoice klien
        $invoice = $this->refreshInvoice($project->fresh(), true);

        // 5. Undangan WhatsApp
        $waSent = false;
        if ($sendWaInvite && ! empty($user->phone)) {
            $directPayUrl = ($invoice && $invoice->payment_token) ? route('invoices.pay', $invoice->payment_token) : null;
            $message = $isNewUser && $rawPassword
                ? PortalWhatsAppTemplates::welcomeClientAccount(
                    name: $user->name,
                    projectName: $project->name,
                    email: $user->email,
                    rawPassword: $rawPassword,
                    directPayUrl: $directPayUrl,
                )
                : PortalWhatsAppTemplates::existingClientNewProject(
                    name: $user->name,
                    projectName: $project->name,
                    directPayUrl: $directPayUrl,
                );

            $res = $this->waService->sendWhatsApp($user->phone, $message, $lead, 'client_invite');
            $waSent = $res['success'] ?? false;
        }

        ActivityLogger::log(
            'client_provisioned',
            "Akun klien {$user->email} " . ($isNewUser ? 'dibuat' : 'ditautkan') . " untuk proyek {$project->name}",
            'Project',
            $project->id
        );

        return [
            'success' => true,
            'message' => $isNewUser
                ? "Akun klien baru dibuat ({$user->email}) dan proyek sudah tampil di Client Panel."
                : "Proyek sudah ditautkan ke akun klien {$user->email}.",
            'data' => [
                'user_id' => $user->id,
                'client_email' => $user->email,
                'is_new_user' => $isNewUser,
                'default_password' => $rawPassword,
                'invoice_id' => $invoice?->id,
                'wa_sent' => $waSent,
            ],
        ];
    }

    /**
     * Pastikan proyek memiliki data Invoice aktif (dan payment_token) untuk pembayaran langsung.
     */
    public function syncInvoiceForProject(Project $project): ?Invoice
    {
        return $this->refreshInvoice($project, true);
    }

    /**
     * Hitung ulang invoice klien dari harga & pembayaran lunas di CRM.
     */
    public function refreshInvoice(Project $project, bool $createIfMissing = false): ?Invoice
    {
        if ($project->harga <= 0) {
            return null;
        }

        $invoice = $project->invoices()->oldest()->first();
        if (! $invoice && ! $createIfMissing) {
            return null;
        }

        $amount = (float) $project->harga;
        $paid = min($amount, (float) $project->total_paid);
        $balance = max(0, $amount - $paid);

        $currentStatus = $invoice?->status;
        if ($paid >= $amount) {
            $status = 'paid';
        } elseif ($currentStatus === 'verifying') {
            $status = 'verifying';
        } else {
            $status = $paid > 0 ? 'partially_paid' : 'unpaid';
        }

        $data = [
            'client_id' => $project->client_id,
            'title' => 'Invoice Pengerjaan ' . $project->name,
            'amount' => $amount,
            'paid_amount' => $paid,
            'balance_due' => $balance,
            'status' => $status,
        ];

        if ($invoice) {
            if (empty($invoice->payment_token)) {
                $data['payment_token'] = \Illuminate\Support\Str::random(40);
            }
            if ((float) $invoice->amount !== $amount || (float) $invoice->balance_due !== $balance) {
                $data['payment_url'] = null;
                $data['payment_reference'] = null;
            }
            $invoice->update($data);

            return $invoice;
        }

        return Invoice::create($data + [
            'project_id' => $project->id,
            'invoice_number' => $this->invoiceNumberFor($project),
            'payment_token' => \Illuminate\Support\Str::random(40),
            'due_date' => now()->addDays(7)->toDateString(),
        ]);
    }

    /**
     * Catat pembayaran CRM dari verifikasi bukti transfer klien.
     */
    public function recordInvoicePayment(Invoice $invoice, string $jenis, float $amount, ?string $verifiedBy = null): ?Payment
    {
        $project = $invoice->project;
        if (! $project || $amount <= 0) {
            return null;
        }

        $jenis = $jenis === 'dp' ? 'dp' : 'pelunasan';

        $payment = Payment::create([
            'project_id' => $project->id,
            'jenis' => $jenis,
            'jumlah' => (int) round($amount),
            'status' => 'lunas',
            'tanggal' => now()->toDateString(),
            'catatan' => $invoice->payment_notes
                ?: "Diverifikasi dari bukti transfer klien ({$invoice->invoice_number})" . ($verifiedBy ? " oleh {$verifiedBy}" : ''),
        ]);

        if ($jenis === 'dp' && $project->status === 'draft') {
            $project->update(['status' => 'dp_diterima']);
        }

        ActivityLogger::log(
            'payment_client_verified',
            "Pembayaran {$jenis} Rp " . number_format($amount, 0, ',', '.') . " dari bukti transfer klien diverifikasi.",
            'Project',
            $project->id
        );

        return $payment;
    }

    /**
     * Terapkan perubahan kolom Kanban ke status proyek CRM & kirim WA ke klien.
     */
    public function applyKanbanStatus(Task $task, ?string $linkWebsite = null, bool $sendWa = true): array
    {
        $project = $task->project;
        $kanbanStatus = $task->status;
        $oldStatus = $project->status;

        $newStatus = $oldStatus;
        if ($kanbanStatus === 'done') {
            $hasRemaining = $project->tasks()
                ->where('id', '!=', $task->id)
                ->where('status', '!=', 'done')
                ->exists();
            if (! $hasRemaining) {
                $newStatus = 'selesai';
            }
        } elseif ($kanbanStatus === 'in_progress') {
            $newStatus = 'dikerjakan';
        } elseif ($kanbanStatus === 'review') {
            $newStatus = 'review';
        } elseif ($kanbanStatus === 'todo' && in_array($oldStatus, ['dikerjakan', 'review', 'selesai'])) {
            $hasActive = $project->tasks()
                ->where('id', '!=', $task->id)
                ->whereIn('status', ['in_progress', 'review', 'done'])
                ->exists();
            if (! $hasActive) {
                $newStatus = $project->total_paid > 0 ? 'dp_diterima' : 'draft';
            }
        }

        if (! empty($linkWebsite)) {
            $project->link_website = $linkWebsite;
        }
        if ($newStatus === 'selesai' && ! $project->end_date) {
            $project->end_date = now()->toDateString();
        }
        $project->status = $newStatus;
        $project->save();

        $statusChanged = $oldStatus !== $newStatus;
        if ($statusChanged) {
            ActivityLogger::log(
                'project_kanban_sync',
                "Status proyek #{$project->id} berubah dari Kanban: [{$oldStatus} -> {$newStatus}]",
                'Project',
                $project->id
            );
        }

        // Notifikasi WhatsApp ke klien
        $waSent = false;
        $waError = null;
        $lead = $project->lead;
        $phone = $lead?->kontak_wa ?: $project->client?->phone;

        if ($sendWa && $statusChanged && $phone && $lead) {
            $msg = match ($newStatus) {
                'dikerjakan' => WhatsAppTemplates::projectInProgress($lead, $project),
                'review' => WhatsAppTemplates::projectReview($lead, $project),
                'selesai' => WhatsAppTemplates::projectCompleted($lead, $project, $project->link_website),
                'draft', 'dp_diterima' => WhatsAppTemplates::projectStatusUpdated($lead, $project),
                default => null,
            };

            if ($msg) {
                try {
                    $res = $this->waService->sendWhatsApp($phone, $msg, $lead, 'project_status_' . $newStatus);
                    $waSent = $res['success'] ?? false;
                    if (! $waSent) {
                        $waError = $res['message'] ?? 'Gagal mengirim pesan via WhatsApp Gateway';
                    }
                } catch (\Throwable $e) {
                    $waError = $e->getMessage();
                    Log::error('Gagal mengirim WA dari perubahan Kanban: ' . $e->getMessage());
                }
            }
        }

        return [
            'crm_status' => $project->status,
            'status_label' => $project->status_label,
            'link_website' => $project->link_website,
            'wa_sent' => $waSent,
            'wa_error' => $waError,
        ];
    }

    /**
     * Status kolom Kanban awal berdasarkan status proyek CRM.
     */
    public function kanbanStatusFor(?string $status): string
    {
        return match ($status) {
            'dikerjakan' => 'in_progress',
            'review' => 'review',
            'selesai' => 'done',
            default => 'todo',
        };
    }

    /**
     * Samakan kolom Kanban dengan status proyek saat status diubah dari Admin Panel.
     */
    public function syncTasksFromStatus(Project $project): void
    {
        if (in_array($project->status, ['draft', 'dp_diterima', 'dibatalkan'])) {
            return;
        }

        $project->tasks()->update(['status' => $this->kanbanStatusFor($project->status)]);
    }
}

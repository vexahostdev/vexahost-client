<?php

namespace App\Services\WhatsApp;

use App\Models\Ticket;

class PortalWhatsAppTemplates
{
    /**
     * Template: Welcome message with credentials when new client account is synced from CRM.
     */
    public static function welcomeClientAccount(
        string $name,
        string $projectName,
        string $email,
        string $rawPassword,
        ?string $portalUrl = null,
        ?string $directPayUrl = null
    ): string {
        $url = $portalUrl ?: config('app.url', 'https://client.vexahostcloud.my.id');
        $paySection = $directPayUrl 
            ? "⚡ *Link Pembayaran Tagihan (Tanpa Login):*\n👉 {$directPayUrl}\n(Tersedia QRIS instan semua bank & Virtual Account Mandiri / BNI)\n\n"
            : "";

        return "Halo Kak *{$name}*, selamat datang di Client Panel VexaHost! ✨\n\n"
            . "Proyek Anda *{$projectName}* telah berhasil didaftarkan ke sistem kerja kami. Sekarang Kakak bisa memantau progres tugas, jadwal pengerjaan, dan tagihan secara langsung:\n\n"
            . $paySection
            . "🌐 *Akses Portal Klien:*\n"
            . "• URL: {$url}\n"
            . "• Email Login: {$email}\n"
            . "• Password Sementara: `{$rawPassword}`\n\n"
            . "💡 *Saran:* Silakan login dan ganti password Anda di menu profil demi keamanan akun. Terima kasih atas kepercayaannya bersama VexaHost! 🚀";
    }

    /**
     * Template: Notification for existing client when an additional project is synced.
     */
    public static function existingClientNewProject(
        string $name,
        string $projectName,
        ?string $portalUrl = null,
        ?string $directPayUrl = null
    ): string {
        $url = $portalUrl ?: config('app.url', 'https://client.vexahostcloud.my.id');
        $paySection = $directPayUrl 
            ? "⚡ *Link Pembayaran Tagihan (Tanpa Login):*\n👉 {$directPayUrl}\n(Tersedia QRIS instan semua bank & Virtual Account Mandiri / BNI)\n\n"
            : "";

        return "Halo Kak *{$name}*! ✨\n\n"
            . "Proyek baru Anda *{$projectName}* sudah aktif dan ditambahkan ke akun Client Panel Anda.\n\n"
            . $paySection
            . "Silakan login untuk memantau progres dan roadmap pengerjaannya:\n"
            . "👉 {$url}/projects\n\n"
            . "Terima kasih banyak atas kerjasamanya! 🙏🚀";
    }

    /**
     * Template: Notification to Staff / Technician when a new ticket is submitted.
     */
    public static function ticketCreatedForStaff(Ticket $ticket, string $clientName, ?string $portalUrl = null): string
    {
        $url = $portalUrl ?: config('app.url', 'https://client.vexahostcloud.my.id');
        $priority = strtoupper($ticket->priority);

        return "🔔 *[TIKET SUPPORT BARU]*\n\n"
            . "👤 Klien: *{$clientName}*\n"
            . "🏷️ Judul: *{$ticket->title}*\n"
            . "⚡ Prioritas: *{$priority}*\n\n"
            . "Mohon tim teknis segera merespons sesuai SLA target:\n"
            . "👉 {$url}/technician/tickets";
    }

    /**
     * Template: Notification to Client when ticket status is updated or resolved.
     */
    public static function ticketStatusUpdatedForClient(
        Ticket $ticket,
        string $clientName,
        string $statusLabel,
        ?string $portalUrl = null
    ): string {
        $url = $portalUrl ?: config('app.url', 'https://client.vexahostcloud.my.id');

        return "Halo Kak *{$clientName}*, ada pembaruan pada tiket Anda *#{$ticket->id}* ({$ticket->title}) 🙏\n\n"
            . "📌 Status Terbaru: *{$statusLabel}*\n\n"
            . "Silakan cek catatan teknisi dan konfirmasi hasilnya melalui link berikut:\n"
            . "👉 {$url}/tickets\n\n"
            . "Tim VexaHost siap membantu kebutuhan Anda. ✨";
    }
}

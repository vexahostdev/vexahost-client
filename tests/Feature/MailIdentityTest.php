<?php

namespace Tests\Feature;

use App\Mail\MaintenanceInvoiceMail;
use App\Mail\PaymentReceiptMail;
use App\Mail\ProjectInvoiceMail;
use App\Mail\SettlementInvoiceMail;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\MaintenanceSubscription;
use App\Models\Payment;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Support\MailIdentity;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MailIdentityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $client;
    private CompanySetting $settings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->seed(RoleAndPermissionSeeder::class);

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp-relay.brevo.com',
            'mail.from.address' => 'noreply@vexahostcloud.my.id',
            'mail.from.name' => 'VexaHost Build',
            'mail.support.address' => 'support@vexahostcloud.my.id',
            'mail.support.name' => 'VexaHost Build Support',
            'mail.reply_to.address' => 'support@vexahostcloud.my.id',
            'mail.reply_to.name' => 'VexaHost Build Support',
            'mail.admin_addresses' => ['support@vexahostcloud.my.id', 'vexahostcloudtech@gmail.com'],
            'mail.allowed_sender_domains' => ['vexahostcloud.my.id'],
        ]);

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@vexahostcloud.my.id'],
            ['name' => 'Admin VexaHost', 'password' => bcrypt('password123')]
        );
        $this->admin->assignRole('admin');

        $this->client = User::create([
            'name' => 'Klien Contoh',
            'email' => 'client@contoh.id',
            'password' => bcrypt('password123'),
        ]);
        $this->client->assignRole('client');

        $this->settings = CompanySetting::get();
        $this->settings->update([
            'company_name' => 'PT DESTINARA CHAKRAWALA ARTHA',
            'brand_name' => 'VexaHost',
            'domicile_city' => 'Jakarta',
            'email_company' => 'company@vexahostcloud.my.id',
            'email_support' => 'support@vexahostcloud.my.id',
            'mail_noreply_address' => 'noreply@vexahostcloud.my.id',
            'mail_noreply_name' => 'VexaHost Build',
            'mail_support_name' => 'VexaHost Build Support',
            'mail_reply_to_address' => 'support@vexahostcloud.my.id',
            'email_internal_alert' => "support@vexahostcloud.my.id,vexahostcloudtech@gmail.com",
            'phone_support' => '0858-0874-9131',
            'phone_admin_alerts' => '0858-0874-9131',
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'PT DESTINARA CHAKRAWALA ARTHA',
        ]);

        MailIdentity::flushCache();
    }

    public function test_support_emails_have_support_from_and_reply_to(): void
    {
        $ticket = Ticket::create([
            'title' => 'Kendala Server',
            'description' => 'Server tidak bisa diakses',
            'priority' => 'high',
            'status' => 'open',
            'client_id' => $this->client->id,
        ]);

        $notification = new TicketStatusUpdatedNotification($ticket);
        $mailMessage = $notification->toMail($this->client);

        $this->assertSame('support@vexahostcloud.my.id', $mailMessage->from[0]);
        $this->assertSame('VexaHost Build Support', $mailMessage->from[1]);
        $this->assertNotEmpty($mailMessage->replyTo);
        $this->assertSame('support@vexahostcloud.my.id', $mailMessage->replyTo[0][0]);
    }

    public function test_noreply_emails_have_noreply_from_and_support_reply_to(): void
    {
        $lead = Lead::create([
            'nama_usaha' => 'Bisnis Hebat',
            'nama_kontak' => 'Budi',
            'kontak_wa' => '081234567890',
            'status' => 'deal',
        ]);

        $project = Project::create([
            'lead_id' => $lead->id,
            'client_id' => $this->client->id,
            'nama_project' => 'Website Profil',
            'paket' => 'company_profile',
            'harga' => 2500000,
            'status' => 'draft',
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'title' => 'Tagihan Pembayaran DP',
            'project_id' => $project->id,
            'client_id' => $this->client->id,
            'type' => 'project_dp',
            'status' => 'unpaid',
            'amount' => 1250000,
            'due_date' => now()->addDays(3),
        ]);

        $payment = Payment::create([
            'project_id' => $project->id,
            'jenis' => 'dp',
            'jumlah' => 1250000,
            'status' => 'lunas',
            'tanggal' => now()->toDateString(),
            'catatan' => 'Pembayaran DP Proyek',
        ]);

        $sub = MaintenanceSubscription::create([
            'lead_id' => $lead->id,
            'project_id' => $project->id,
            'harga_bulanan' => 500000,
            'status' => 'aktif',
            'tanggal_mulai' => now()->toDateString(),
            'tanggal_jatuh_tempo_berikutnya' => now()->addMonth()->toDateString(),
        ]);

        $ticket = Ticket::create([
            'title' => 'Tiket Baru',
            'description' => 'Deskripsi tiket baru',
            'priority' => 'medium',
            'status' => 'open',
            'client_id' => $this->client->id,
        ]);

        // Sample 1: TicketCreatedNotification
        $notif = new TicketCreatedNotification($ticket);
        $mail1 = $notif->toMail($this->client);
        $this->assertSame('noreply@vexahostcloud.my.id', $mail1->from[0]);
        $this->assertSame('VexaHost Build', $mail1->from[1]);
        $this->assertSame('support@vexahostcloud.my.id', $mail1->replyTo[0][0]);

        // Sample 2: ProjectInvoiceMail
        $mail2 = new ProjectInvoiceMail($project, $invoice, $this->settings, 'dummy-pdf', 'inv.pdf', 'https://example.com/pay');
        $env2 = $mail2->envelope();
        $this->assertSame('noreply@vexahostcloud.my.id', $env2->from->address);
        $this->assertSame('VexaHost Build', $env2->from->name);
        $this->assertSame('support@vexahostcloud.my.id', $env2->replyTo[0]->address);

        // Sample 3: PaymentReceiptMail
        $mail3 = new PaymentReceiptMail($payment, $invoice, $this->settings, 'dummy-pdf', 'receipt.pdf', 'REC-001');
        $env3 = $mail3->envelope();
        $this->assertSame('noreply@vexahostcloud.my.id', $env3->from->address);
        $this->assertSame('VexaHost Build', $env3->from->name);
        $this->assertSame('support@vexahostcloud.my.id', $env3->replyTo[0]->address);

        // Sample 4: MaintenanceInvoiceMail
        $mail4 = new MaintenanceInvoiceMail($sub, $invoice, $this->settings, 'dummy-pdf', 'maint.pdf', 'https://example.com/maint', 'INV-MAINT-001', '2026-11-01');
        $env4 = $mail4->envelope();
        $this->assertSame('noreply@vexahostcloud.my.id', $env4->from->address);
        $this->assertSame('VexaHost Build', $env4->from->name);
        $this->assertSame('support@vexahostcloud.my.id', $env4->replyTo[0]->address);

        // Sample 5: SettlementInvoiceMail
        $mail5 = new SettlementInvoiceMail($project, $invoice, $this->settings, 'dummy-pdf', 'settle.pdf', 'https://example.com/settle', 'INV-SETTLE-001');
        $env5 = $mail5->envelope();
        $this->assertSame('noreply@vexahostcloud.my.id', $env5->from->address);
        $this->assertSame('VexaHost Build', $env5->from->name);
        $this->assertSame('support@vexahostcloud.my.id', $env5->replyTo[0]->address);
    }

    public function test_admin_alerts_sent_to_all_admin_recipients(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->client)->post(route('tickets.store'), [
            'title' => 'Pertanyaan Layanan',
            'description' => 'Mohon bantuan terkait tagihan.',
            'priority' => 'medium',
        ]);

        $response->assertRedirect(route('tickets.index'));

        Notification::assertSentOnDemand(
            TicketCreatedNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'support@vexahostcloud.my.id';
            }
        );

        Notification::assertSentOnDemand(
            TicketCreatedNotification::class,
            function ($notification, $channels, $notifiable) {
                return $notifiable->routes['mail'] === 'vexahostcloudtech@gmail.com';
            }
        );
    }

    public function test_db_override_used_when_set_and_fallback_to_env_when_empty(): void
    {
        $this->settings->update([
            'mail_noreply_name' => 'Custom Build System',
            'mail_noreply_address' => 'custom-noreply@vexahostcloud.my.id',
        ]);
        MailIdentity::flushCache();

        $this->assertSame('Custom Build System', MailIdentity::noreply()['name']);
        $this->assertSame('custom-noreply@vexahostcloud.my.id', MailIdentity::noreply()['address']);
        $this->assertSame('db', MailIdentity::noreply()['source']);

        $this->settings->update([
            'mail_noreply_name' => null,
            'mail_noreply_address' => null,
        ]);
        MailIdentity::flushCache();

        $this->assertSame('VexaHost Build', MailIdentity::noreply()['name']);
        $this->assertSame('noreply@vexahostcloud.my.id', MailIdentity::noreply()['address']);
        $this->assertSame('config', MailIdentity::noreply()['source']);
    }

    public function test_db_override_with_external_domain_is_ignored(): void
    {
        $this->settings->update([
            'mail_noreply_name' => null,
            'mail_noreply_address' => 'phishing@unauthorizeddomain.com',
        ]);
        MailIdentity::flushCache();

        $this->assertSame('noreply@vexahostcloud.my.id', MailIdentity::noreply()['address']);
        $this->assertSame('config', MailIdentity::noreply()['source']);
    }

    public function test_admin_form_rejects_external_sender_domain(): void
    {
        $payload = [
            'company_name' => 'PT DESTINARA CHAKRAWALA ARTHA',
            'brand_name' => 'VexaHost',
            'domicile_city' => 'Jakarta',
            'email_company' => 'company@vexahostcloud.my.id',
            'mail_noreply_address' => 'bad@unauthorizeddomain.com',
            'email_support' => 'support@vexahostcloud.my.id',
            'phone_support' => '0858-0874-9131',
            'phone_admin_alerts' => '0858-0874-9131',
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'PT DESTINARA CHAKRAWALA ARTHA',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.settings.company.update'), $payload);

        $response->assertSessionHasErrors(['mail_noreply_address']);
        $this->assertStringContainsString('Alamat pengirim harus memakai domain terverifikasi', session('errors')->first('mail_noreply_address'));
    }

    public function test_cache_is_flushed_after_admin_saves_mail_settings(): void
    {
        // Pre-warm cache
        MailIdentity::noreply();
        $this->assertTrue(Cache::has(MailIdentity::CACHE_KEY));

        $payload = [
            'company_name' => 'PT DESTINARA CHAKRAWALA ARTHA',
            'brand_name' => 'VexaHost',
            'domicile_city' => 'Jakarta',
            'email_company' => 'company@vexahostcloud.my.id',
            'mail_noreply_name' => 'New Noreply Brand',
            'mail_noreply_address' => 'noreply@vexahostcloud.my.id',
            'mail_support_name' => 'New Support Brand',
            'email_support' => 'support@vexahostcloud.my.id',
            'mail_reply_to_address' => 'support@vexahostcloud.my.id',
            'email_internal_alert' => "support@vexahostcloud.my.id,admin2@contoh.id",
            'phone_support' => '0858-0874-9131',
            'phone_admin_alerts' => '0858-0874-9131',
            'bank_name' => 'Bank Mandiri',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'PT DESTINARA CHAKRAWALA ARTHA',
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.settings.company.update'), $payload);

        $response->assertSessionHasNoErrors();
        $this->assertFalse(Cache::has(MailIdentity::CACHE_KEY));

        $this->assertSame('New Noreply Brand', MailIdentity::noreply()['name']);
        $this->assertContains('admin2@contoh.id', MailIdentity::adminRecipients());
    }

    public function test_admin_can_send_synchronous_test_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.settings.company.test-mail'), [
            'type' => 'noreply',
            'target_email' => 'testing@example.com',
        ]);

        $response->assertSessionHas('success');
    }

    public function test_artisan_mail_test_identity_command(): void
    {
        Mail::fake();

        $this->artisan('mail:test-identity user@example.com --type=noreply')
            ->expectsOutputToContain('UJI IDENTITAS EMAIL DOMAIN')
            ->expectsOutputToContain('noreply@vexahostcloud.my.id')
            ->assertExitCode(0);

        $this->artisan('mail:test-identity user@example.com --type=support')
            ->expectsOutputToContain('support@vexahostcloud.my.id')
            ->assertExitCode(0);

        $this->artisan('mail:test-identity --type=admin')
            ->expectsOutputToContain('support@vexahostcloud.my.id')
            ->expectsOutputToContain('vexahostcloudtech@gmail.com')
            ->assertExitCode(0);
    }
}

<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TechnicianTicketController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman Utama = Login + Landing Page
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->to(auth()->user()->homeUrl());
    }

    return view('auth.login');
})->name('home');

// Redirect umum setelah login / verifikasi email → panel sesuai role
Route::get('/dashboard', function () {
    return redirect()->to(auth()->user()->homeUrl());
})->middleware(['auth', 'verified'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Webhook VexaHost WA Gateway & Xendit (CSRF dikecualikan di bootstrap/app.php)
|--------------------------------------------------------------------------
*/

Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle'])->name('webhook.whatsapp');
Route::post('/api/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle']);
Route::match(['get', 'post'], '/api/payment/xendit/callback', [\App\Http\Controllers\Api\XenditWebhookController::class, 'handle'])
    ->name('api.payment.xendit.callback');
Route::match(['get', 'post'], '/api/payment/xendit/webhook', [\App\Http\Controllers\Api\XenditWebhookController::class, 'handle'])
    ->name('api.payment.xendit.webhook');
Route::match(['get', 'post'], '/api/webhooks/xendit', [\App\Http\Controllers\Api\XenditWebhookController::class, 'handle'])
    ->name('api.webhooks.xendit');
Route::match(['get', 'post'], '/payment/xendit/webhook', [\App\Http\Controllers\Api\XenditWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| Public Invoice Payment Routes (Tanpa Perlu Login / Guest Checkout)
|--------------------------------------------------------------------------
*/
Route::get('/pay/{token}', [\App\Http\Controllers\PublicInvoicePaymentController::class, 'show'])->name('invoices.pay');
Route::post('/pay/{token}/process', [\App\Http\Controllers\PublicInvoicePaymentController::class, 'process'])->name('invoices.pay.process');
Route::get('/pay/{token}/pdf', [\App\Http\Controllers\PublicInvoicePaymentController::class, 'downloadPdf'])->name('invoices.pay.pdf');
Route::get('/pay/{token}/receipt', [\App\Http\Controllers\PublicInvoicePaymentController::class, 'downloadReceipt'])->name('invoices.pay.receipt');
Route::post('/pay/{token}/upload-proof', [\App\Http\Controllers\PublicInvoicePaymentController::class, 'uploadProof'])->name('invoices.pay.upload-proof');



/*
|--------------------------------------------------------------------------
| CLIENT PANEL
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'panel:client'])->prefix('client')->name('client.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| ADMIN PANEL
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'panel:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));

    // Ringkasan
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/operasional', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')->name('operations');

    // Leads & Pipeline
    Route::middleware('permission:leads.manage')->group(function () {
        Route::get('/leads', [Admin\LeadController::class, 'index'])->name('leads.index');
        Route::post('/leads', [Admin\LeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{lead}', [Admin\LeadController::class, 'show'])->name('leads.show');
        Route::put('/leads/{lead}', [Admin\LeadController::class, 'update'])->name('leads.update');
        Route::delete('/leads/{lead}', [Admin\LeadController::class, 'destroy'])->name('leads.destroy');
        Route::post('/leads/{lead}/convert-deal', [Admin\LeadController::class, 'convertToDeal'])->name('leads.convert-deal');
        Route::post('/leads/{lead}/kanban-status', [Admin\LeadController::class, 'updateStatusAjax'])->name('leads.kanban-status');
        Route::post('/leads/{lead}/quick-followup', [Admin\LeadController::class, 'quickFollowUp'])->name('leads.quick-followup');
    });

    // Proyek Website (CRM) & Dokumen Tagihan
    Route::middleware('permission:crm.projects.manage')->group(function () {
        Route::get('/projects', [Admin\ProjectController::class, 'index'])->name('projects.index');
        Route::post('/projects', [Admin\ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}', [Admin\ProjectController::class, 'show'])->name('projects.show');
        Route::put('/projects/{project}', [Admin\ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [Admin\ProjectController::class, 'destroy'])->name('projects.destroy');
        Route::post('/projects/{project}/status', [Admin\ProjectController::class, 'updateStatus'])->name('projects.update-status');
        Route::post('/projects/{project}/send-website-wa', [Admin\ProjectController::class, 'sendWebsiteWa'])->name('projects.send-website-wa');
        Route::post('/projects/{project}/send-settlement-wa', [Admin\ProjectController::class, 'sendSettlementWa'])->name('projects.send-settlement-wa');
        Route::post('/projects/{project}/provision-client', [Admin\ProjectController::class, 'provisionClient'])->name('projects.provision-client');

        Route::get('/invoices/project/{project}', [Admin\InvoiceController::class, 'projectInvoice'])->name('invoices.project');
        Route::post('/invoices/project/{project}/send-wa', [Admin\InvoiceController::class, 'sendProjectInvoiceWa'])->name('invoices.project.send-wa');
        Route::post('/invoices/project/{project}/send-email', [Admin\InvoiceController::class, 'sendProjectInvoiceEmail'])->name('invoices.project.send-email');
        Route::get('/invoices/settlement/{project}', [Admin\InvoiceController::class, 'settlementInvoice'])->name('invoices.settlement');
        Route::post('/invoices/settlement/{project}/send-wa', [Admin\InvoiceController::class, 'sendSettlementInvoiceWa'])->name('invoices.settlement.send-wa');
        Route::post('/invoices/settlement/{project}/send-email', [Admin\InvoiceController::class, 'sendSettlementInvoiceEmail'])->name('invoices.settlement.send-email');
    });

    // Pembayaran
    Route::middleware('permission:payments.manage')->group(function () {
        Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
        Route::post('/payments', [Admin\PaymentController::class, 'store'])->name('payments.store');
        Route::patch('/payments/{payment}/status', [Admin\PaymentController::class, 'updateStatus'])->name('payments.update-status');
        Route::delete('/payments/{payment}', [Admin\PaymentController::class, 'destroy'])->name('payments.destroy');

        Route::get('/invoices/payment/{payment}', [Admin\InvoiceController::class, 'paymentReceipt'])->name('invoices.receipt');
        Route::post('/invoices/payment/{payment}/send-wa', [Admin\InvoiceController::class, 'sendPaymentReceiptWa'])->name('invoices.receipt.send-wa');
        Route::post('/invoices/payment/{payment}/send-email', [Admin\InvoiceController::class, 'sendPaymentReceiptEmail'])->name('invoices.receipt.send-email');
    });

    // Maintenance & Masa Berlaku
    Route::middleware('permission:maintenance.manage')->group(function () {
        Route::get('/maintenance', [Admin\MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::post('/maintenance', [Admin\MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::patch('/maintenance/{subscription}/toggle', [Admin\MaintenanceController::class, 'toggleStatus'])->name('maintenance.toggle');
        Route::delete('/maintenance/{subscription}', [Admin\MaintenanceController::class, 'destroy'])->name('maintenance.destroy');
        Route::post('/maintenance/{subscription}/reminder', [Admin\MaintenanceController::class, 'sendReminder'])->name('maintenance.reminder');
        Route::get('/invoices/maintenance/{subscription}', [Admin\InvoiceController::class, 'maintenanceInvoice'])->name('invoices.maintenance');
        Route::post('/invoices/maintenance/{subscription}/send-wa', [Admin\InvoiceController::class, 'sendMaintenanceInvoiceWa'])->name('invoices.maintenance.send-wa');
        Route::post('/invoices/maintenance/{subscription}/send-email', [Admin\InvoiceController::class, 'sendMaintenanceInvoiceEmail'])->name('invoices.maintenance.send-email');

        Route::get('/subscriptions', [Admin\ProjectSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('/subscriptions', [Admin\ProjectSubscriptionController::class, 'store'])->name('subscriptions.store');
        Route::put('/subscriptions/{subscription}', [Admin\ProjectSubscriptionController::class, 'update'])->name('subscriptions.update');
        Route::post('/subscriptions/{subscription}/renew', [Admin\ProjectSubscriptionController::class, 'renew'])->name('subscriptions.renew');
        Route::patch('/subscriptions/{subscription}/toggle', [Admin\ProjectSubscriptionController::class, 'toggleStatus'])->name('subscriptions.toggle');
        Route::post('/subscriptions/{subscription}/reminder', [Admin\ProjectSubscriptionController::class, 'sendReminder'])->name('subscriptions.reminder');
        Route::delete('/subscriptions/{subscription}', [Admin\ProjectSubscriptionController::class, 'destroy'])->name('subscriptions.destroy');
    });

    // Riwayat Pesan WhatsApp
    Route::middleware('permission:messages.manage')->group(function () {
        Route::get('/messages', [Admin\WhatsAppController::class, 'index'])->name('messages.index');
        Route::post('/messages/send-manual', [Admin\WhatsAppController::class, 'sendManual'])->name('messages.send-manual');
        Route::delete('/messages/{messageLog}', [Admin\WhatsAppController::class, 'destroy'])->name('messages.destroy');
        Route::delete('/messages-clear', [Admin\WhatsAppController::class, 'destroyAll'])->name('messages.destroy-all');
    });

    // Export Data
    Route::middleware('permission:export.data')->group(function () {
        Route::get('/export/leads', [Admin\ExportController::class, 'exportLeads'])->name('export.leads');
        Route::get('/export/projects', [Admin\ExportController::class, 'exportProjects'])->name('export.projects');
        Route::get('/export/payments', [Admin\ExportController::class, 'exportPayments'])->name('export.payments');
    });

    // Activity Log
    Route::middleware('permission:activity.view')->group(function () {
        Route::get('/activity-logs', [Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::delete('/activity-logs/{activityLog}', [Admin\ActivityLogController::class, 'destroy'])->name('activity-logs.destroy');
        Route::delete('/activity-logs-clear', [Admin\ActivityLogController::class, 'destroyAll'])->name('activity-logs.destroy-all');
    });

    // Pengaturan Perusahaan
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/settings/company', [Admin\CompanySettingController::class, 'edit'])->name('settings.company.edit');
        Route::put('/settings/company', [Admin\CompanySettingController::class, 'update'])->name('settings.company.update');
        Route::post('/settings/company/test-wa', [Admin\CompanySettingController::class, 'testWhatsApp'])->name('settings.company.test-wa');
    });

    // Tiket Masuk (admin inbox)
    Route::get('/tickets', [AdminTicketController::class, 'index'])
        ->middleware('permission:tickets.manage')->name('tickets');

    // Manajemen Akses (Pengguna & Role)
    Route::middleware('permission:users.manage')->group(function () {
        Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [Admin\UserController::class, 'create'])->name('users.create');
        Route::post('users', [Admin\UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('roles', [Admin\RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/create', [Admin\RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [Admin\RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [Admin\RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [Admin\RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [Admin\RoleController::class, 'destroy'])->name('roles.destroy');
    });
});

/*
|--------------------------------------------------------------------------
| AREA BERSAMA (klien & staf, dibatasi permission) — tampil di panel masing-masing
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/kpi-data', [DashboardController::class, 'getKpiData'])->name('api.kpi-data');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifikasi
    Route::get('/notifications', function () {
        return response()->json(auth()->user()->unreadNotifications);
    });
    Route::post('/notifications/{id}/read', function ($id) {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();

        return response()->json(['success' => true]);
    });
    Route::delete('/notifications-clear', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::delete('/notifications-clear-read', [NotificationController::class, 'destroyRead'])->name('notifications.destroy-read');

    // Tiket bantuan (klien)
    Route::middleware('permission:tickets.create')->group(function () {
        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    });

    // Tiket teknisi
    Route::get('/technician/tickets', [TechnicianTicketController::class, 'index'])
        ->middleware('permission:tickets.handle')->name('technician.tickets');

    // Kelola proyek operasional (Kanban)
    Route::middleware('permission:projects.manage')->group(function () {
        Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
        Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
        Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
        Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    });

    // Kelola tugas
    Route::middleware('permission:tasks.manage')->group(function () {
        Route::get('/tasks/create', [TaskController::class, 'create'])->name('tasks.create');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    });

    // Proyek & tugas (baca)
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::patch('/tasks/{task}/progress', [TaskController::class, 'updateProgress'])->name('tasks.progress');

    // Dokumen
    Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    // Tagihan & pembayaran klien
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf');
    Route::get('invoices/{invoice}/settlement-pdf', [InvoiceController::class, 'settlementPdf'])->name('invoices.settlement-pdf');
    Route::get('invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])->name('invoices.receipt');
    Route::get('invoices/{invoice}/download-receipt', [InvoiceController::class, 'downloadReceipt'])->name('invoices.download-receipt');
    Route::post('invoices/{invoice}/upload-proof', [InvoiceController::class, 'uploadPaymentProof'])->name('invoices.upload-proof');
    Route::post('invoices/{invoice}/pay-xendit', [InvoiceController::class, 'payWithXendit'])->name('invoices.pay-xendit');
    Route::post('invoices/{invoice}/verify', [InvoiceController::class, 'verifyPayment'])->name('invoices.verify');
});

require __DIR__.'/auth.php';

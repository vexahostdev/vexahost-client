<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Project;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\MaintenanceSubscription;
use App\Services\ActivityLogger;
use App\Services\WhatsApp\WhatsAppService;
use App\Services\WhatsApp\WhatsAppTemplates;
use App\Services\ProjectLifecycleService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $query = Project::with(['lead', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('paket')) {
            $query->where('paket', $request->paket);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhereHas('lead', function ($leadSub) use ($q) {
                        $leadSub->where('nama_usaha', 'like', "%{$q}%")
                            ->orWhere('nama_kontak', 'like', "%{$q}%")
                            ->orWhere('kontak_wa', 'like', "%{$q}%");
                    });
            });
        }

        $projects = $query->latest()->paginate(12)->withQueryString();

        $statusCounts = [
            'all' => Project::count(),
            'draft' => Project::where('status', 'draft')->count(),
            'dp_diterima' => Project::where('status', 'dp_diterima')->count(),
            'dikerjakan' => Project::where('status', 'dikerjakan')->count(),
            'review' => Project::where('status', 'review')->count(),
            'selesai' => Project::where('status', 'selesai')->count(),
        ];

        return view('admin.projects.index', compact('projects', 'statusCounts'));
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'nama_project' => 'required|string|max:255',
            'paket' => 'required|string',
            'harga' => 'required|numeric|min:0',
            'status' => 'required|string',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'link_website' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        $project = Project::create($validated);

        app(ProjectLifecycleService::class)->syncInvoiceForProject($project->fresh());

        ActivityLogger::log('project_created', "Membuat project baru: {$project->nama_project}", 'Project', $project->id);

        return redirect()->route('admin.projects.show', $project)->with('success', "Project {$project->nama_project} berhasil dibuat.");
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        app(ProjectLifecycleService::class)->syncInvoiceForProject($project);
        $project->load(['lead.messageLogs', 'payments', 'maintenanceSubscription', 'subscriptions', 'latestInvoice']);

        return view('admin.projects.show', compact('project'));
    }

    /**
     * Update the specified project details.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'nama_project' => 'required|string|max:255',
            'paket' => 'required|string',
            'harga' => 'required|numeric|min:0',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'link_website' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        if ($validated['harga'] < $project->total_paid) {
            return back()->with('error', "Nilai kontrak tidak boleh lebih kecil dari total pembayaran yang sudah diterima (Rp " . number_format($project->total_paid, 0, ',', '.') . ").")->withInput();
        }

        $oldPrice = $project->harga;
        $project->update($validated);

        ActivityLogger::log('project_updated', "Memperbarui data project {$project->nama_project} (Harga: Rp " . number_format($oldPrice, 0, ',', '.') . " -> Rp " . number_format($project->harga, 0, ',', '.') . ", Sisa: Rp " . number_format($project->remaining_balance, 0, ',', '.') . ")", 'Project', $project->id);

        // Harga berubah → invoice klien ikut diperbarui
        app(ProjectLifecycleService::class)->syncInvoiceForProject($project->fresh());

        return back()->with('success', "Data dan nilai kontrak proyek {$project->nama_project} berhasil diperbarui (Sisa Tagihan: Rp " . number_format($project->remaining_balance, 0, ',', '.') . ").");
    }

    /**
     * Update Project Status with Automated WhatsApp Trigger (Moment 1 & Moment 2).
     */
    public function updateStatus(Request $request, Project $project)
    {
        $request->validate([
            'status' => 'required|in:draft,dp_diterima,dikerjakan,review,selesai,dibatalkan',
            'link_website' => 'nullable|string|max:255',
            'send_wa' => 'nullable|boolean',
            'dp_amount' => 'nullable|numeric',
            'create_maintenance' => 'nullable|boolean',
        ]);

        $newStatus = $request->status;
        $oldStatus = $project->status;
        $sendWa = $request->boolean('send_wa', true);
        $lead = $project->lead;

        $project->status = $newStatus;

        if ($request->filled('link_website')) {
            $project->link_website = $request->link_website;
        }

        if ($newStatus === 'selesai' && !$project->tanggal_selesai) {
            $project->tanggal_selesai = now()->toDateString();
        }

        $project->save();

        ActivityLogger::log('project_status_changed', "Status project {$project->nama_project} diubah dari {$oldStatus} menjadi {$newStatus}", 'Project', $project->id);

        $waNotificationSent = false;
        $waMessageSummary = '';

        // =========================================================================
        // 1. OPSI: PENCATATAN PEMBAYARAN DP & SUBSCRIPTION MAINTENANCE
        // =========================================================================
        if ($newStatus === 'dp_diterima' && $request->filled('dp_amount') && $request->dp_amount > 0) {
            Payment::create([
                'project_id' => $project->id,
                'jenis' => 'dp',
                'jumlah' => $request->dp_amount,
                'status' => 'lunas',
                'tanggal' => now()->toDateString(),
                'catatan' => 'Uang Muka (DP) pengerjaan project.',
            ]);
        }

        if ($newStatus === 'selesai' && $oldStatus !== 'selesai' && $request->boolean('create_maintenance', false)) {
            MaintenanceSubscription::firstOrCreate(
                ['lead_id' => $lead->id, 'project_id' => $project->id],
                [
                    'harga_bulanan' => config('whatsapp.default_maintenance_price', 150000),
                    'status' => 'aktif',
                    'tanggal_mulai' => now()->toDateString(),
                    'tanggal_jatuh_tempo_berikutnya' => now()->addMonth()->toDateString(),
                    'catatan' => "Langganan pemeliharaan untuk project {$project->nama_project}.",
                ]
            );
        }

        // =========================================================================
        // 2. WHATSAPP NOTIFICATION TRIGGER (SEMUA STATUS PROYEK)
        // =========================================================================
        if ($sendWa && !empty($lead->kontak_wa)) {
            $msg = match ($newStatus) {
                'dp_diterima' => WhatsAppTemplates::dpReceived($lead, $project),
                'dikerjakan'  => WhatsAppTemplates::projectInProgress($lead, $project),
                'review'      => WhatsAppTemplates::projectReview($lead, $project),
                'selesai'     => WhatsAppTemplates::projectCompleted($lead, $project, $project->link_website),
                'dibatalkan'  => WhatsAppTemplates::projectCancelled($lead, $project),
                default       => WhatsAppTemplates::projectStatusUpdated($lead, $project),
            };

            $tipePesan = match ($newStatus) {
                'dp_diterima' => 'invoice_dp',
                'selesai'     => 'project_selesai',
                default       => 'project_status_' . $newStatus,
            };

            $res = $this->waService->sendWhatsApp(
                to: $lead->kontak_wa,
                message: $msg,
                lead: $lead,
                tipePesan: $tipePesan,
                isAutomated: false // Dikirim secara eksplisit oleh admin/owner
            );

            if ($res['success'] ?? false) {
                $waNotificationSent = true;
                $waMessageSummary = "WhatsApp pemberitahuan progres ({$project->status_label}) berhasil dikirim ke {$lead->kontak_wa}.";
            }
        }

        // =========================================================================
        // 3. SAMAKAN PAPAN KANBAN DI CLIENT PANEL
        // =========================================================================
        app(ProjectLifecycleService::class)->syncTasksFromStatus($project);

        $flashMessage = "Status project {$project->nama_project} diubah menjadi: {$project->status_label}.";
        if ($waNotificationSent) {
            $flashMessage .= " " . $waMessageSummary;
        }

        return back()->with('success', $flashMessage);
    }

    /**
     * Buat / tautkan akun klien sehingga proyek tampil di Client Panel.
     */
    public function provisionClient(Request $request, Project $project, ProjectLifecycleService $lifecycle)
    {
        $sendWa = $request->boolean('send_wa', true);
        $result = $lifecycle->provisionClient($project, $sendWa);

        if ($result['success'] ?? false) {
            return back()->with('success', "🚀 " . $result['message']);
        }

        return back()->with('error', $result['message'] ?? 'Gagal membuat akun klien.');
    }

    /**
     * Send Project Website Link directly to Client via WhatsApp.
     */
    public function sendWebsiteWa(Request $request, Project $project)
    {
        $request->validate([
            'link_website' => 'nullable|url',
        ]);

        if ($request->filled('link_website')) {
            $project->link_website = $request->link_website;
            $project->save();
        }

        $lead = $project->lead;
        if (!$lead || empty($lead->kontak_wa)) {
            return back()->with('error', 'Nomor WhatsApp klien tidak ditemukan.');
        }

        $link = $project->link_website ?: $request->input('link_website');
        if (empty($link)) {
            return back()->with('error', 'Silakan masukkan link URL website terlebih dahulu.');
        }

        $msg = WhatsAppTemplates::projectCompleted($lead, $project, $link);

        $res = $this->waService->sendWhatsApp(
            to: $lead->kontak_wa,
            message: $msg,
            lead: $lead,
            tipePesan: 'project_website_share',
            isAutomated: false
        );

        if ($res['success'] ?? false) {
            ActivityLogger::log('send_website_wa', "Mengirim link website {$link} via WA ke {$lead->kontak_wa}", 'Project', $project->id);
            return back()->with('success', "Link website berhasil dikirim ke WhatsApp klien ({$lead->kontak_wa})!");
        }

        return back()->with('error', 'Gagal mengirim WhatsApp: ' . ($res['message'] ?? 'Terjadi kesalahan gateway'));
    }

    /**
     * Send Project Settlement (Pelunasan) Request directly to Client via WhatsApp.
     */
    public function sendSettlementWa(Request $request, Project $project)
    {
        $lead = $project->lead;
        if (!$lead || empty($lead->kontak_wa)) {
            return back()->with('error', 'Nomor WhatsApp klien tidak ditemukan.');
        }

        if ($project->remaining_balance <= 0) {
            return back()->with('info', "Proyek {$project->nama_project} sudah lunas, tidak ada sisa tagihan pelunasan.");
        }

        $msg = WhatsAppTemplates::projectSettlementRequest($lead, $project);

        $res = $this->waService->sendWhatsApp(
            to: $lead->kontak_wa,
            message: $msg,
            lead: $lead,
            tipePesan: 'project_settlement_request',
            isAutomated: false
        );

        if ($res['success'] ?? false) {
            ActivityLogger::log('send_settlement_wa', "Mengirim tagihan pelunasan sisa Rp " . number_format($project->remaining_balance, 0, ',', '.') . " via WA ke {$lead->kontak_wa}", 'Project', $project->id);
            return back()->with('success', "Instruksi tagihan pelunasan (Sisa Rp " . number_format($project->remaining_balance, 0, ',', '.') . ") berhasil dikirim ke WhatsApp klien ({$lead->kontak_wa})!");
        }

        return back()->with('error', 'Gagal mengirim WhatsApp tagihan pelunasan: ' . ($res['message'] ?? 'Terjadi kesalahan gateway'));
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        $nama = $project->nama_project;
        $id = $project->id;
        $project->delete();

        ActivityLogger::log('project_deleted', "Menghapus project {$nama}", 'Project', $id);

        return redirect()->route('admin.projects.index')->with('success', "Project {$nama} berhasil dihapus.");
    }
}

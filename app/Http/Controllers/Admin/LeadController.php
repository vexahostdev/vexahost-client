<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Lead;
use App\Models\Project;
use App\Models\MessageLog;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeadController extends Controller
{
    /**
     * Display a listing of leads with filters (Table List & Kanban View).
     */
    public function index(Request $request)
    {
        $viewMode = $request->get('view', 'kanban'); // Default to 'kanban'

        $query = Lead::with(['projects', 'activeMaintenanceSubscription']);

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Sumber
        if ($request->filled('sumber')) {
            $query->where('sumber', $request->sumber);
        }

        // Filter by Paket
        if ($request->filled('paket')) {
            $query->where('paket_diminati', $request->paket);
        }

        // Filter Overdue Follow-ups
        if ($request->filter === 'overdue') {
            $query->whereNotNull('follow_up_date')
                ->where('follow_up_date', '<', now()->toDateString())
                ->whereNotIn('status', ['deal', 'tidak_lanjut']);
        } elseif ($request->filter === 'today') {
            $query->where('follow_up_date', now()->toDateString())
                ->whereNotIn('status', ['deal', 'tidak_lanjut']);
        }

        // Search Keyword (Nama Usaha, Kontak, WA)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_usaha', 'like', "%{$q}%")
                    ->orWhere('nama_kontak', 'like', "%{$q}%")
                    ->orWhere('kontak_wa', 'like', "%{$q}%")
                    ->orWhere('catatan', 'like', "%{$q}%");
            });
        }

        // Sorting
        $sort = $request->get('sort', 'latest');
        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort === 'follow_up') {
            $query->orderByRaw('CASE WHEN follow_up_date IS NULL THEN 1 ELSE 0 END, follow_up_date ASC');
        } else {
            $query->latest();
        }

        // For Kanban view, get all filtered leads grouped by status
        if ($viewMode === 'kanban') {
            $allKanbanLeads = $query->get();
            $kanbanColumns = [
                'belum_dihubungi' => $allKanbanLeads->where('status', 'belum_dihubungi'),
                'sudah_chat' => $allKanbanLeads->where('status', 'sudah_chat'),
                'nego' => $allKanbanLeads->where('status', 'nego'),
                'deal' => $allKanbanLeads->where('status', 'deal'),
                'tidak_lanjut' => $allKanbanLeads->where('status', 'tidak_lanjut'),
            ];
            // Paginate from the already-fetched collection (avoid double query)
            $page = $request->get('page', 1);
            $leads = new \Illuminate\Pagination\LengthAwarePaginator(
                $allKanbanLeads->forPage($page, 15)->values(),
                $allKanbanLeads->count(),
                15,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $kanbanColumns = [];
            $leads = $query->paginate(15)->withQueryString();
        }

        // Status counts — 1 query instead of 7
        $rawCounts = Lead::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $overdueCount = Lead::whereNotNull('follow_up_date')
            ->where('follow_up_date', '<', now()->toDateString())
            ->whereNotIn('status', ['deal', 'tidak_lanjut'])
            ->count();

        $statusCounts = [
            'all' => array_sum($rawCounts),
            'belum_dihubungi' => (int) ($rawCounts['belum_dihubungi'] ?? 0),
            'sudah_chat' => (int) ($rawCounts['sudah_chat'] ?? 0),
            'nego' => (int) ($rawCounts['nego'] ?? 0),
            'deal' => (int) ($rawCounts['deal'] ?? 0),
            'tidak_lanjut' => (int) ($rawCounts['tidak_lanjut'] ?? 0),
            'overdue' => $overdueCount,
        ];

        return view('admin.leads.index', compact('leads', 'statusCounts', 'viewMode', 'kanbanColumns'));
    }

    /**
     * Store a newly created lead in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_usaha' => 'required|string|max:255',
            'nama_kontak' => 'nullable|string|max:255',
            'kontak_wa' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'sumber' => 'required|string',
            'status' => 'required|string',
            'paket_diminati' => 'required|string',
            'nilai_nego' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $lead = Lead::create($validated);

        ActivityLogger::log('lead_created', "Menambahkan prospek baru: {$lead->nama_usaha} ({$lead->paket_label})", 'Lead', $lead->id);

        // If directly created with Deal status, automatically create the project
        if ($lead->status === 'deal') {
            $project = $this->createInitialProjectForDeal($lead);
            try {
                app(\App\Services\ProjectLifecycleService::class)->provisionClient($project, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Pembuatan akun klien otomatis (store deal) gagal: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.leads.show', $lead)->with('success', "Lead {$lead->nama_usaha} berhasil ditambahkan.");
    }

    /**
     * Display the specified lead details with chat timeline & projects.
     */
    public function show(Lead $lead)
    {
        $lead->load(['projects.payments', 'projects.latestInvoice', 'maintenanceSubscriptions', 'messageLogs']);

        return view('admin.leads.show', compact('lead'));
    }

    /**
     * Update the specified lead in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'nama_usaha' => 'required|string|max:255',
            'nama_kontak' => 'nullable|string|max:255',
            'kontak_wa' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'sumber' => 'required|string',
            'status' => 'required|string',
            'paket_diminati' => 'required|string',
            'nilai_nego' => 'nullable|numeric|min:0',
            'catatan' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $oldStatus = $lead->status;
        $lead->update($validated);

        ActivityLogger::log('lead_updated', "Memperbarui data prospek: {$lead->nama_usaha}", 'Lead', $lead->id);

        // If status changed to Deal and no project exists yet, auto-create project
        if ($oldStatus !== 'deal' && $lead->status === 'deal' && $lead->projects()->count() === 0) {
            $project = $this->createInitialProjectForDeal($lead);
            try {
                app(\App\Services\ProjectLifecycleService::class)->provisionClient($project, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Pembuatan akun klien otomatis (update deal) gagal: " . $e->getMessage());
            }
        } else {
            // If lead already has an unpaid draft project and nilai_nego / paket changed, sync the draft project price & invoice
            $draftProject = $lead->projects()->where('status', 'draft')->latest()->first();
            if ($draftProject && $draftProject->total_paid <= 0) {
                $newEstimatedPrice = $lead->getEstimatedDealPrice();
                $newPackage = $lead->paket_diminati !== 'belum_tahu' ? $lead->paket_diminati : $draftProject->paket;
                if ((int) $draftProject->harga !== $newEstimatedPrice || $draftProject->paket !== $newPackage) {
                    $draftProject->update([
                        'harga' => $newEstimatedPrice,
                        'paket' => $newPackage,
                    ]);
                    app(\App\Services\ProjectLifecycleService::class)->syncInvoiceForProject($draftProject->fresh());
                }
            }
        }

        return back()->with('success', "Data lead {$lead->nama_usaha} berhasil diperbarui.");
    }

    /**
     * Update Lead Status via Kanban AJAX Drag & Drop.
     */
    public function updateStatusAjax(Request $request, Lead $lead)
    {
        $request->validate([
            'status' => 'required|in:belum_dihubungi,sudah_chat,nego,deal,tidak_lanjut',
        ]);

        $oldStatus = $lead->status;
        $newStatus = $request->status;

        $lead->update(['status' => $newStatus]);

        ActivityLogger::log('lead_status_changed', "Mengubah status prospek {$lead->nama_usaha} dari {$oldStatus} menjadi {$newStatus}", 'Lead', $lead->id);

        // Auto-create project if moved to Deal
        $createdProject = null;
        if ($oldStatus !== 'deal' && $newStatus === 'deal' && $lead->projects()->count() === 0) {
            $createdProject = $this->createInitialProjectForDeal($lead);
            try {
                app(\App\Services\ProjectLifecycleService::class)->provisionClient($createdProject, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Pembuatan akun klien otomatis (kanban deal) gagal: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Status {$lead->nama_usaha} berhasil diubah ke {$lead->status_label}.",
            'lead' => $lead,
            'project_id' => $createdProject?->id,
        ]);
    }

    /**
     * Quick Snooze / Follow-Up Date Updater.
     */
    public function quickFollowUp(Request $request, Lead $lead)
    {
        $action = $request->get('days', '1'); // 1, 3, 7, today, clear

        if ($action === 'clear') {
            $lead->update(['follow_up_date' => null]);
            $msg = "Jadwal follow-up {$lead->nama_usaha} dibersihkan.";
        } elseif ($action === 'today') {
            $lead->update(['follow_up_date' => now()->toDateString()]);
            $msg = "Jadwal follow-up {$lead->nama_usaha} disetel ke HARI INI.";
        } else {
            $days = (int) $action;
            $newDate = now()->addDays($days)->toDateString();
            $lead->update(['follow_up_date' => $newDate]);
            $msg = "Jadwal follow-up {$lead->nama_usaha} diundur +{$days} hari (" . Carbon::parse($newDate)->format('d M Y') . ").";
        }

        ActivityLogger::log('lead_followup_snooze', $msg, 'Lead', $lead->id);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'date' => $lead->follow_up_date?->format('d M Y')]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Convert Lead to Deal (Action button).
     */
    public function convertToDeal(Request $request, Lead $lead)
    {
        $lead->update([
            'status' => 'deal',
            'follow_up_date' => null,
        ]);

        $price = $request->filled('harga') ? (int) $request->get('harga') : $lead->getEstimatedDealPrice();
        $project = $this->createInitialProjectForDeal($lead, $request->get('nama_project'), $price);

        ActivityLogger::log('lead_converted', "Mengonversi prospek {$lead->nama_usaha} menjadi Deal (Project ID #{$project->id}, Nilai: Rp " . number_format($project->harga, 0, ',', '.') . ")", 'Lead', $lead->id);

        $syncResult = null;
        if ($request->boolean('sync_portal', true)) {
            try {
                $syncResult = app(\App\Services\ProjectLifecycleService::class)->provisionClient($project, true);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Pembuatan akun klien otomatis (deal) gagal: " . $e->getMessage());
            }
        }

        $successMsg = "🎉 Selamat! Lead berhasil ditandai Deal dan Project baru telah dibuat (Nilai: Rp " . number_format($project->harga, 0, ',', '.') . ").";
        if ($syncResult && ($syncResult['success'] ?? false)) {
            $successMsg .= " Akun klien juga otomatis dibuat & proyek tampil di Client Panel!";
        }

        return redirect()->route('admin.projects.show', $project)->with('success', $successMsg);
    }

    /**
     * Remove the specified lead from storage.
     */
    public function destroy(Lead $lead)
    {
        $nama = $lead->nama_usaha;
        $id = $lead->id;
        $lead->delete();

        ActivityLogger::log('lead_deleted', "Menghapus prospek {$nama}", 'Lead', $id);

        return redirect()->route('admin.leads.index')->with('success', "Lead {$nama} berhasil dihapus.");
    }

    /**
     * Helper to create the initial project when a lead becomes a Deal.
     */
    protected function createInitialProjectForDeal(Lead $lead, ?string $customProjectName = null, ?int $customPrice = null): Project
    {
        $packageKey = $lead->paket_diminati !== 'belum_tahu' ? $lead->paket_diminati : 'landing_page';
        $finalPrice = $customPrice ?: $lead->getEstimatedDealPrice();

        $project = Project::create([
            'lead_id' => $lead->id,
            'nama_project' => $customProjectName ?: "Website {$lead->nama_usaha}",
            'paket' => $packageKey,
            'harga' => $finalPrice,
            'status' => 'draft',
            'tanggal_mulai' => now()->toDateString(),
            'catatan' => "Dikonversi otomatis dari Lead ID #{$lead->id}." . ($lead->nilai_nego ? " (Harga kesepakatan nego: Rp " . number_format($lead->nilai_nego, 0, ',', '.') . ")" : ''),
        ]);

        app(\App\Services\ProjectLifecycleService::class)->syncInvoiceForProject($project->fresh());

        ActivityLogger::log('project_created', "Project otomatis dibuat untuk klien {$lead->nama_usaha} (Paket: {$project->paket_label}, Nilai: Rp " . number_format($project->harga, 0, ',', '.') . ")", 'Project', $project->id);

        return $project;
    }
}

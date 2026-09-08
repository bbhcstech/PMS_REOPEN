<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\CompanyComplaint;
use App\Models\Central\Company;
use App\Models\Central\SuperAdmin;
use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class ComplaintController extends Controller
{
    protected ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    private function authorizeSuperAdmin(): void
    {
        if (\Illuminate\Support\Facades\Auth::guard('super_admin')->check()) {
            return;
        }

        if (auth()->check()) {
            $user = auth()->user();
            $role = strtolower((string) ($user->role ?? ''));
            if ($role === 'superadmin' || $role === 'admin') {
                return;
            }
        }

        throw new \Illuminate\Auth\AuthenticationException('Unauthenticated.', ['web', 'super_admin']);
    }

    /**
     * Display Super Admin Complaints Dashboard with stats, filters, and ticket table.
     */
    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $companies = Company::on('central')->orderBy('name', 'asc')->get();
        $superAdmins = SuperAdmin::on('central')->orderBy('name', 'asc')->get();

        $query = CompanyComplaint::on('central')->with(['company', 'assignedSuperAdmin', 'conversations', 'attachments']);

        // Search Filter (Ticket ID, Company name, Subject, Admin name)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('ticket_id', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('raised_by_name', 'like', "%{$search}%")
                  ->orWhere('assigned_to_name', 'like', "%{$search}%")
                  ->orWhereHas('company', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('priority')) {
            $query->where('priority', strtoupper($request->priority));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_super_admin_id');
            } else {
                $query->where('assigned_super_admin_id', $request->assigned_to);
            }
        }

        if ($request->filled('date_range')) {
            $range = $request->date_range;
            if (str_contains($range, ' to ')) {
                [$start, $end] = explode(' to ', $range);
                try {
                    $query->whereBetween('created_at', [
                        Carbon::parse($start)->startOfDay(),
                        Carbon::parse($end)->endOfDay()
                    ]);
                } catch (\Exception $e) {}
            }
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'priority':
                $query->orderByRaw("FIELD(priority, 'CRITICAL', 'HIGH', 'MEDIUM', 'LOW')");
                break;
            case 'recently_updated':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100, 250], true)) {
            $perPage = 10;
        }

        $tickets = $query->paginate($perPage)->withQueryString();

        // Calculate KPI Statistics directly from central database
        $kpis = [
            'total'       => CompanyComplaint::on('central')->count(),
            'open'        => CompanyComplaint::on('central')->where('status', 'OPEN')->count(),
            'in_progress' => CompanyComplaint::on('central')->where('status', 'IN PROGRESS')->count(),
            'resolved'    => CompanyComplaint::on('central')->where('status', 'RESOLVED')->count(),
            'critical'    => CompanyComplaint::on('central')->where('priority', 'CRITICAL')->whereNotIn('status', ['RESOLVED', 'CLOSED'])->count(),
            'unassigned'  => CompanyComplaint::on('central')->whereNull('assigned_super_admin_id')->whereNotIn('status', ['RESOLVED', 'CLOSED'])->count(),
        ];

        return view('superadmin.complaints.index', compact('tickets', 'companies', 'superAdmins', 'kpis'));
    }

    /**
     * Export Complaints to CSV or PDF based on active filters.
     */
    public function export(Request $request)
    {
        $this->authorizeSuperAdmin();

        $query = CompanyComplaint::on('central')->with(['company', 'assignedSuperAdmin']);

        // Search Filter (Ticket ID, Company name, Subject, Admin name)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('ticket_id', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('raised_by_name', 'like', "%{$search}%")
                  ->orWhere('assigned_to_name', 'like', "%{$search}%")
                  ->orWhereHas('company', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('priority')) {
            $query->where('priority', strtoupper($request->priority));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('assigned_to')) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_super_admin_id');
            } else {
                $query->where('assigned_super_admin_id', $request->assigned_to);
            }
        }

        if ($request->filled('date_range')) {
            $range = $request->date_range;
            if (str_contains($range, ' to ')) {
                [$start, $end] = explode(' to ', $range);
                try {
                    $query->whereBetween('created_at', [
                        Carbon::parse($start)->startOfDay(),
                        Carbon::parse($end)->endOfDay()
                    ]);
                } catch (\Exception $e) {}
            }
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'priority':
                $query->orderByRaw("FIELD(priority, 'CRITICAL', 'HIGH', 'MEDIUM', 'LOW')");
                break;
            case 'recently_updated':
                $query->orderBy('updated_at', 'desc');
                break;
            case 'newest':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $tickets = $query->get();
        $format = strtolower($request->get('export_format', 'csv'));

        // Collect active filters summary for report header
        $activeFilters = [];
        if ($request->filled('search')) $activeFilters[] = "Search: " . $request->search;
        if ($request->filled('status')) $activeFilters[] = "Status: " . $request->status;
        if ($request->filled('priority')) $activeFilters[] = "Priority: " . $request->priority;
        if ($request->filled('category')) $activeFilters[] = "Category: " . $request->category;
        if ($request->filled('company_id')) {
            $comp = Company::on('central')->find($request->company_id);
            if ($comp) $activeFilters[] = "Company: " . $comp->name;
        }

        $dateStr = now()->format('Y-m-d_His');

        if ($format === 'pdf') {
            if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('superadmin.complaints.exports.pdf', compact('tickets', 'activeFilters'));
                return $pdf->download("complaints_report_{$dateStr}.pdf");
            }
            return view('superadmin.complaints.exports.pdf', compact('tickets', 'activeFilters'));
        }

        // Default: CSV Export
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=complaints_export_{$dateStr}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($tickets) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, [
                'Ticket ID',
                'Company Name',
                'Company Code',
                'Raised By Name',
                'Raised By Email',
                'Subject',
                'Category',
                'Priority',
                'Status',
                'Created At',
                'Last Updated',
                'Assigned To'
            ]);

            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    '#' . $ticket->ticket_id,
                    $ticket->company?->name ?? 'Unknown Company',
                    $ticket->company?->company_code ?? 'N/A',
                    $ticket->raised_by_name ?? 'N/A',
                    $ticket->raised_by_email ?? 'N/A',
                    $ticket->subject ?? '',
                    $ticket->category ?? '',
                    $ticket->priority ?? '',
                    $ticket->status ?? '',
                    $ticket->created_at?->format('Y-m-d H:i:s') ?? '',
                    $ticket->updated_at?->format('Y-m-d H:i:s') ?? '',
                    $ticket->assigned_to_name ?? 'Unassigned'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display or return single ticket details.
     */
    public function show($id): View|JsonResponse
    {
        $this->authorizeSuperAdmin();

        $ticket = CompanyComplaint::on('central')
            ->with(['company', 'conversations', 'attachments', 'activities', 'assignedSuperAdmin'])
            ->findOrFail($id);

        $superAdmins = SuperAdmin::on('central')->get();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'ticket'  => $ticket,
                'html'    => view('superadmin.complaints.partials.drawer_content', compact('ticket', 'superAdmins'))->render()
            ]);
        }

        return view('superadmin.complaints.show', compact('ticket', 'superAdmins'));
    }

    /**
     * Respond to a ticket.
     */
    public function respond(Request $request, $id): RedirectResponse|JsonResponse
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'message'       => 'required|string|min:2',
            'attachments.*' => 'nullable|file|max:5120',
        ]);

        $ticket = CompanyComplaint::on('central')->findOrFail($id);
        $sender = auth('super_admin')->user() ?? auth()->user();

        $files = $request->file('attachments', []);
        if (!is_array($files) && $files) {
            $files = [$files];
        }

        $this->complaintService->addResponse($ticket, $request->message, $sender, 'super_admin', $files);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Response submitted successfully.']);
        }

        return redirect()->back()->with('success', 'Response posted and Company Admin notified successfully.');
    }

    /**
     * Update ticket status.
     */
    public function updateStatus(Request $request, $id): RedirectResponse|JsonResponse
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'status' => 'required|in:OPEN,IN PROGRESS,WAITING FOR COMPANY,RESOLVED,CLOSED,REOPENED',
            'note'   => 'nullable|string|max:500',
        ]);

        $ticket = CompanyComplaint::on('central')->findOrFail($id);
        $actor = auth('super_admin')->user() ?? auth()->user();

        $updated = $this->complaintService->updateStatus($ticket, $request->status, $actor, 'super_admin', $request->note);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Ticket status updated to ' . $request->status]);
        }

        return redirect()->back()->with('success', 'Ticket status updated to ' . $request->status . '.');
    }

    /**
     * Assign ticket to Super Admin or support staff.
     */
    public function assign(Request $request, $id): RedirectResponse|JsonResponse
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'super_admin_id' => 'nullable',
        ]);

        $ticket = CompanyComplaint::on('central')->findOrFail($id);
        $actor = auth('super_admin')->user() ?? auth()->user();

        if ($request->filled('super_admin_id') && $request->super_admin_id !== 'unassigned') {
            $admin = SuperAdmin::on('central')->find($request->super_admin_id);
            $assigneeName = $admin?->name ?? 'Super Admin';
            $assigneeId = $admin?->id;
        } else {
            $assigneeName = 'Unassigned';
            $assigneeId = null;
        }

        $this->complaintService->assignTicket($ticket, $assigneeId, $assigneeName, $actor);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Ticket assigned to ' . $assigneeName]);
        }

        return redirect()->back()->with('success', 'Ticket assigned to ' . $assigneeName . '.');
    }

    /**
     * Unread/Open pending count for Super Admin sidebar badge.
     */
    public function unreadCount(): JsonResponse
    {
        $count = CompanyComplaint::on('central')
            ->whereIn('status', ['OPEN', 'IN PROGRESS', 'REOPENED'])
            ->count();

        return response()->json(['count' => $count]);
    }
}

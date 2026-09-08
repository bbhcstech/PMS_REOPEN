<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Central\CompanyComplaint;
use App\Models\Central\Company;
use App\Services\ComplaintService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CompanyComplaintController extends Controller
{
    protected ComplaintService $complaintService;

    public function __construct(ComplaintService $complaintService)
    {
        $this->complaintService = $complaintService;
    }

    private function getActiveCompany(): Company
    {
        $user = auth()->user();
        $companyId = $user?->company_id ?? session('current_company_id');

        if ($companyId) {
            $company = Company::on('central')->find($companyId);
            if ($company) return $company;
        }

        $dbName = session('current_company_db');
        if ($dbName) {
            $company = Company::on('central')->where('db_name', $dbName)->first();
            if ($company) return $company;
        }

        $first = Company::on('central')->first();
        if ($first) return $first;

        // Fallback
        return new Company([
            'id'    => 1,
            'name'  => config('app.name', 'Default Company'),
            'email' => 'admin@company.com'
        ]);
    }

    /**
     * Display Company's Own Complaints List.
     */
    public function index(Request $request): View
    {
        $company = $this->getActiveCompany();

        $query = CompanyComplaint::on('central')
            ->where('company_id', $company->id)
            ->with(['conversations', 'attachments']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('ticket_id', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        if ($request->filled('priority')) {
            $query->where('priority', strtoupper($request->priority));
        }

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100, 250], true)) {
            $perPage = 10;
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $kpis = [
            'total'       => CompanyComplaint::on('central')->where('company_id', $company->id)->count(),
            'open'        => CompanyComplaint::on('central')->where('company_id', $company->id)->where('status', 'OPEN')->count(),
            'in_progress' => CompanyComplaint::on('central')->where('company_id', $company->id)->where('status', 'IN PROGRESS')->count(),
            'waiting'     => CompanyComplaint::on('central')->where('company_id', $company->id)->where('status', 'WAITING FOR COMPANY')->count(),
            'resolved'    => CompanyComplaint::on('central')->where('company_id', $company->id)->where('status', 'RESOLVED')->count(),
            'closed'      => CompanyComplaint::on('central')->where('company_id', $company->id)->where('status', 'CLOSED')->count(),
        ];

        return view('admin.complaints.index', compact('tickets', 'kpis', 'company'));
    }

    /**
     * Show Form to Create Support Complaint.
     */
    public function create(): View
    {
        $company = $this->getActiveCompany();
        $categories = [
            'Technical Issue', 'Subscription', 'Billing', 'Account',
            'Payroll', 'HR', 'Security', 'Performance', 'Feature Request', 'Bug Report', 'Other'
        ];

        return view('admin.complaints.create', compact('company', 'categories'));
    }

    /**
     * Submit Complaint Ticket.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'subject'           => 'required|string|max:255',
            'category'          => 'required|string',
            'priority'          => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'description'       => 'required|string|min:10',
            'attachment'        => 'nullable|file|max:5120',
            'related_module'    => 'nullable|string|max:100',
            'related_record_id' => 'nullable|string|max:100',
        ]);

        $company = $this->getActiveCompany();
        $user = auth()->user();

        $complaint = $this->complaintService->createComplaint($request->all(), $company, $user);

        return redirect()->route('admin.company-complaints.show', $complaint->id)
            ->with('success', "Ticket created successfully! Your Ticket ID is: {$complaint->ticket_id}");
    }

    /**
     * View Complaint Details & Timeline.
     */
    public function show($id): View
    {
        $company = $this->getActiveCompany();

        $ticket = CompanyComplaint::on('central')
            ->where('company_id', $company->id)
            ->with(['conversations', 'attachments', 'activities'])
            ->findOrFail($id);

        return view('admin.complaints.show', compact('ticket', 'company'));
    }

    /**
     * Send Reply to Super Admin.
     */
    public function reply(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'message'       => 'required|string|min:2',
            'attachments.*' => 'nullable|file|max:5120',
        ]);

        $company = $this->getActiveCompany();
        $ticket = CompanyComplaint::on('central')
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $user = auth()->user();
        $files = $request->file('attachments', []);
        if (!is_array($files) && $files) {
            $files = [$files];
        }

        $this->complaintService->addResponse($ticket, $request->message, $user, 'company_admin', $files);

        return redirect()->back()->with('success', 'Response sent successfully to Super Admin.');
    }

    /**
     * Reopen resolved ticket.
     */
    public function reopen(Request $request, $id): RedirectResponse
    {
        $company = $this->getActiveCompany();
        $ticket = CompanyComplaint::on('central')
            ->where('company_id', $company->id)
            ->findOrFail($id);

        $user = auth()->user();
        $this->complaintService->updateStatus($ticket, 'REOPENED', $user, 'company_admin', 'Ticket reopened by Company Admin.');

        return redirect()->back()->with('success', 'Ticket reopened successfully.');
    }
}

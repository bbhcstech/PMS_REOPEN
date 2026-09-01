<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeadContact;
use App\Models\Deal;
use App\Models\Client;
use App\Models\CrmActivity;
use App\Models\CrmFollowUp;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeadsExport;
use App\Imports\LeadsImport;

class LeadContactController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $type = $request->get('type', 'all');
        $perPage = (int) $request->get('per_page', 10);
        $search = trim($request->get('search', ''));

        $validPerPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;

        // KPI Counts (scoped to current tenant)
        $kpiQuery = LeadContact::query();

        $kpiStats = [
            'total' => (clone $kpiQuery)->count(),
            'new' => (clone $kpiQuery)->where(function($q) {
                $q->where('status', 'new')->orWhereNull('status');
            })->count(),
            'qualified' => (clone $kpiQuery)->where('status', 'qualified')->count(),
            'hot' => (clone $kpiQuery)->where(function($q) {
                $q->where('lead_score', '>=', 61)
                  ->orWhereIn('priority', ['high', 'urgent'])
                  ->orWhere('status', 'hot');
            })->count(),
            'converted' => (clone $kpiQuery)->where(function($q) {
                $q->where('type', 'client')->orWhere('status', 'converted');
            })->count(),
            'lost' => (clone $kpiQuery)->where('status', 'lost')->count(),
        ];

        // Main Query with Filters
        $query = LeadContact::with(['owner', 'creator', 'dealAgent', 'deals']);

        if ($type === 'lead') {
            $query->where(function($q) {
                $q->where('type', 'lead')->orWhereNull('type');
            });
        } elseif ($type === 'client') {
            $query->where('type', 'client');
        }

        // Filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('lead_owner_id') && $request->lead_owner_id !== 'all') {
            $query->where('lead_owner_id', $request->lead_owner_id);
        }

        if ($request->filled('lead_source') && $request->lead_source !== 'all') {
            $query->where('lead_source', $request->lead_source);
        }

        if ($request->filled('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('industry') && $request->industry !== 'all') {
            $query->where('industry', $request->industry);
        }

        if ($request->filled('score_rating')) {
            switch ($request->score_rating) {
                case 'very_hot':
                    $query->where('lead_score', '>=', 81);
                    break;
                case 'hot':
                    $query->whereBetween('lead_score', [61, 80]);
                    break;
                case 'warm':
                    $query->whereBetween('lead_score', [31, 60]);
                    break;
                case 'cold':
                    $query->where('lead_score', '<=', 30);
                    break;
            }
        }

        if ($request->filled('created_from') && $request->filled('created_to')) {
            $query->whereBetween('created_at', [$request->created_from . ' 00:00:00', $request->created_to . ' 23:59:59']);
        }

        if ($request->filled('tags')) {
            $query->where('tags', 'like', "%{$request->tags}%");
        }

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('contact_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $leads = $query->latest()->paginate($validPerPage)->appends($request->query());
        $users = User::select('id', 'name')->get();

        // Get distinct industries and sources for filter dropdowns
        $sources = LeadContact::whereNotNull('lead_source')->distinct()->pluck('lead_source');
        $industries = LeadContact::whereNotNull('industry')->distinct()->pluck('industry');

        return view('admin.leads.contacts.index', compact('leads', 'users', 'kpiStats', 'sources', 'industries'));
    }

    public function checkDuplicate(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['exists' => false]);
        }

        $email = trim($request->get('email', ''));
        $phone = trim($request->get('phone', ''));
        $mobile = trim($request->get('mobile', ''));

        if (empty($email) && empty($phone) && empty($mobile)) {
            return response()->json(['exists' => false]);
        }

        $query = LeadContact::query();
        $query->where(function($q) use ($email, $phone, $mobile) {
            if (!empty($email)) {
                $q->orWhere('email', $email);
            }
            if (!empty($phone)) {
                $q->orWhere('phone', $phone);
            }
            if (!empty($mobile)) {
                $q->orWhere('mobile', $mobile);
            }
        });

        if ($request->filled('exclude_id')) {
            $query->where('id', '!=', $request->exclude_id);
        }

        $duplicates = $query->limit(5)->get(['id', 'contact_name', 'email', 'phone', 'company_name', 'status']);

        return response()->json([
            'exists' => $duplicates->isNotEmpty(),
            'duplicates' => $duplicates
        ]);
    }

    public function create()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $users = User::select('id', 'name')->get();
        return view('admin.leads.contacts.create', compact('users'));
    }

    public function store(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $data = $request->validate([
            // Section: Basic Information
            'salutation' => 'nullable|string|max:20',
            'contact_name' => 'required|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',

            // Section: Location
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:20',

            // Section: Lead Information
            'lead_source' => 'required|string|max:100',
            'status' => 'nullable|string|max:50',
            'priority' => 'nullable|string|max:20',
            'lead_owner_id' => 'required|exists:users,id',
            'lead_score' => 'nullable|integer|min:0|max:100',
            'expected_value' => 'nullable|numeric|min:0',
            'expected_closing_date' => 'nullable|date',
            'industry' => 'nullable|string|max:100',
            'tags' => 'nullable|string',

            // Deal Information
            'create_deal' => 'nullable|boolean',
            'deal_name' => 'nullable|string|max:255',
            'deal_value' => 'nullable|numeric|min:0',
            'deal_currency' => 'nullable|string|max:10',
            'deal_agent_id' => 'nullable|exists:users,id',
            'pipeline' => 'nullable|string|max:100',
            'deal_stage' => 'nullable|string|max:100',
            'deal_category' => 'nullable|string|max:100',
            'close_date' => 'nullable|date',

            // Section: Notes
            'description' => 'nullable|string',
        ]);

        $data['create_deal'] = $request->has('create_deal');
        $data['added_by'] = auth()->id();
        $data['status'] = $data['status'] ?? 'new';
        $data['priority'] = $data['priority'] ?? 'medium';

        if ($request->has('products') && is_array($request->products)) {
            $data['products'] = json_encode($request->products);
        }

        if ($request->has('lead_owner_id')) {
            $owner = User::find($request->lead_owner_id);
            if ($owner) {
                $data['lead_owner_designation'] = $owner->designation ?? null;
            }
        }
        $data['added_by_designation'] = auth()->user()->designation ?? null;

        // Instantiate model to calculate score if not explicitly set
        $lead = new LeadContact($data);
        if (!isset($data['lead_score']) || $data['lead_score'] === null) {
            $lead->lead_score = $lead->calculateLeadScore();
        }
        $lead->save();

        // Log CrmActivity for Creation
        CrmActivity::create([
            'lead_id' => $lead->id,
            'type' => 'created',
            'title' => 'Lead Contact Created',
            'description' => 'Lead contact "' . $lead->contact_name . '" created by ' . auth()->user()->name,
            'created_by' => auth()->id(),
            'activity_date' => now(),
        ]);

        // Deal Creation if checked
        if ($lead->create_deal && !empty($request->deal_name)) {
            $dealStageId = 1;
            if (!empty($request->deal_stage)) {
                $stage = \App\Models\DealStage::where('name', $request->deal_stage)->first();
                if ($stage) $dealStageId = $stage->id;
            }

            $deal = Deal::create([
                'lead_id' => $lead->id,
                'deal_name' => $request->deal_name,
                'lead_name' => $lead->contact_name,
                'company_name' => $lead->company_name,
                'contact_details' => $lead->email . ($lead->phone ? ' | ' . $lead->phone : ''),
                'value' => $request->deal_value ?? 0,
                'currency' => $request->deal_currency ?? 'INR',
                'close_date' => $request->close_date ?? now()->addDays(30),
                'deal_stage_id' => $dealStageId,
                'deal_agent_id' => $request->deal_agent_id ?? $lead->lead_owner_id,
                'pipeline' => $request->pipeline ?? 'Sales Pipeline',
                'priority' => $lead->priority ?? 'medium',
                'is_active' => true,
            ]);

            CrmActivity::create([
                'lead_id' => $lead->id,
                'deal_id' => $deal->id,
                'type' => 'deal_created',
                'title' => 'Associated Deal Created',
                'description' => 'Deal "' . $deal->deal_name . '" (Value: ' . $deal->value . ') created for lead.',
                'created_by' => auth()->id(),
                'activity_date' => now(),
            ]);
        }

        if ($request->has('save_and_add_more')) {
            return redirect('leads/contacts/create')
                ->with('success', 'Lead added successfully. Add another lead.');
        }

        return redirect('leads/contacts')
            ->with('success', 'Lead added successfully');
    }

    public function show($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $lead = LeadContact::with([
            'owner',
            'creator',
            'dealAgent',
            'deals.stage',
            'deals.agent',
            'activities.creator',
            'followUps.assignee'
        ])->findOrFail($id);

        $users = User::select('id', 'name')->get();
        $stages = \App\Models\DealStage::orderBy('order')->get();
        $categories = \App\Models\DealCategory::all();

        return view('admin.leads.contacts.show', compact('lead', 'users', 'stages', 'categories'));
    }

    public function edit($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $lead = LeadContact::findOrFail($id);
        $users = User::select('id', 'name')->get();

        return view('admin.leads.contacts.edit', compact('lead', 'users'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $lead = LeadContact::findOrFail($id);
        $oldStatus = $lead->status;
        $oldPriority = $lead->priority;

        $data = $request->validate([
            // Section: Basic Information
            'salutation' => 'nullable|string|max:20',
            'contact_name' => 'required|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'mobile' => 'nullable|string|max:30',
            'alternate_phone' => 'nullable|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',

            // Section: Location
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'postal_code' => 'nullable|string|max:20',

            // Section: Lead Information
            'lead_source' => 'required|string|max:100',
            'status' => 'nullable|string|max:50',
            'priority' => 'nullable|string|max:20',
            'lead_owner_id' => 'required|exists:users,id',
            'lead_score' => 'nullable|integer|min:0|max:100',
            'expected_value' => 'nullable|numeric|min:0',
            'expected_closing_date' => 'nullable|date',
            'industry' => 'nullable|string|max:100',
            'tags' => 'nullable|string',

            // Section: Notes
            'description' => 'nullable|string',
        ]);

        if ($request->has('products') && is_array($request->products)) {
            $data['products'] = json_encode($request->products);
        }

        if ($request->has('lead_owner_id')) {
            $owner = User::find($request->lead_owner_id);
            if ($owner) {
                $data['lead_owner_designation'] = $owner->designation ?? null;
            }
        }

        $lead->fill($data);
        if (!isset($data['lead_score']) || $data['lead_score'] === null) {
            $lead->lead_score = $lead->calculateLeadScore();
        }
        $lead->save();

        // Audit Activity Logging
        if ($oldStatus !== $lead->status) {
            CrmActivity::create([
                'lead_id' => $lead->id,
                'type' => 'status_change',
                'title' => 'Status Updated',
                'description' => "Status changed from '{$oldStatus}' to '{$lead->status}'",
                'created_by' => auth()->id(),
                'activity_date' => now(),
            ]);
        }

        if ($oldPriority !== $lead->priority) {
            CrmActivity::create([
                'lead_id' => $lead->id,
                'type' => 'priority_change',
                'title' => 'Priority Updated',
                'description' => "Priority changed from '{$oldPriority}' to '{$lead->priority}'",
                'created_by' => auth()->id(),
                'activity_date' => now(),
            ]);
        }

        return redirect('leads/contacts')->with('success', 'Lead updated successfully');
    }

    public function destroy($id)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $lead = LeadContact::findOrFail($id);
        $lead->delete();

        return redirect()->route('leads.contacts.index')->with('success', 'Lead deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lead_contacts,id'
        ]);

        try {
            LeadContact::whereIn('id', $request->ids)->delete();
            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' contact(s) deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting contacts: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeActivity(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $lead = LeadContact::findOrFail($id);

        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $activity = CrmActivity::create([
            'lead_id' => $lead->id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => auth()->id(),
            'activity_date' => now(),
        ]);

        $lead->update(['last_contacted_at' => now()]);

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            return response()->json(['success' => true, 'message' => 'Activity recorded successfully', 'activity' => $activity]);
        }

        return back()->with('success', 'Activity logged successfully.');
    }

    public function storeFollowUp(Request $request, $id)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $lead = LeadContact::findOrFail($id);

        $request->validate([
            'follow_up_type' => 'required|string',
            'date' => 'required|date',
            'time' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $followUp = CrmFollowUp::create([
            'lead_id' => $lead->id,
            'follow_up_type' => $request->follow_up_type,
            'date' => $request->date,
            'time' => $request->time,
            'assigned_to' => $request->assigned_to ?? $lead->lead_owner_id,
            'reminder' => $request->has('reminder'),
            'description' => $request->description,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        $lead->update(['next_follow_up' => $request->date]);

        CrmActivity::create([
            'lead_id' => $lead->id,
            'type' => 'follow_up',
            'title' => 'Follow-up Scheduled',
            'description' => "Scheduled {$request->follow_up_type} for {$request->date}" . ($request->time ? " at {$request->time}" : ""),
            'created_by' => auth()->id(),
            'activity_date' => now(),
        ]);

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson() || $request->isJson()) {
            return response()->json(['success' => true, 'message' => 'Follow-up scheduled successfully']);
        }

        return back()->with('success', 'Follow-up scheduled successfully.');
    }

    public function convertToClient(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'lead_id' => 'required|exists:lead_contacts,id'
        ]);

        try {
            $lead = LeadContact::findOrFail($request->lead_id);

            // Prevent duplicate client creation by checking email or company name in clients table
            $existingClient = null;
            if (!empty($lead->email)) {
                $existingClient = Client::where('email', $lead->email)->first();
            }
            if (!$existingClient && !empty($lead->company_name)) {
                $existingClient = Client::where('company_name', $lead->company_name)->first();
            }

            if (!$existingClient) {
                Client::create([
                    'salutation' => $lead->salutation ?? null,
                    'name' => $lead->contact_name,
                    'email' => $lead->email ?? (strtolower(str_replace(' ', '.', $lead->contact_name)) . '@example.com'),
                    'mobile' => $lead->mobile ?? $lead->phone,
                    'company_name' => $lead->company_name ?? null,
                    'company_address' => $lead->address ?? null,
                    'city' => $lead->city ?? null,
                    'state' => $lead->state ?? null,
                    'country' => $lead->country ?? null,
                    'postal_code' => $lead->postal_code ?? null,
                    'website' => $lead->website ?? null,
                    'status' => 'active',
                    'password' => bcrypt('123456'),
                    'added_by' => auth()->id(),
                ]);
            }

            $lead->update([
                'type' => 'client',
                'status' => 'converted',
                'converted_at' => now(),
                'converted_by' => auth()->id(),
            ]);

            CrmActivity::create([
                'lead_id' => $lead->id,
                'type' => 'converted',
                'title' => 'Converted to Client',
                'description' => 'Lead successfully converted to Client by ' . (auth()->user()->name ?? 'Admin'),
                'created_by' => auth()->id(),
                'activity_date' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead successfully converted to Client!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Lead convertToClient error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to convert lead: ' . $e->getMessage()
            ], 500);
        }
    }

    public function convert(Request $request)
    {
        return $this->convertToClient($request);
    }

    public function export(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $type = $request->get('type', 'all');
        $ids = $request->get('ids', []);

        return Excel::download(new LeadsExport($type, $ids), 'leads_contacts_' . date('Y-m-d_H-i') . '.xlsx');
    }

    public function import(Request $request)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'file' => 'required|mimes:csv,xlsx,xls|max:2048'
        ]);

        try {
            Excel::import(new LeadsImport, $request->file('file'));
            return redirect('leads/contacts')->with('success', 'Leads imported successfully');
        } catch (\Exception $e) {
            return redirect('leads/contacts')->with('error', 'Error importing leads: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }

        $template = [
            ['contact_name', 'email', 'company_name', 'phone', 'lead_source', 'status', 'lead_owner_id']
        ];

        return Excel::download(new LeadsExport($template), 'leads-template.xlsx');
    }
}

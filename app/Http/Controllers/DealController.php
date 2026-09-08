<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealCategory;
use App\Models\DealStage;
use App\Models\LeadContact;
use App\Models\Client;
use App\Models\CrmActivity;
use App\Models\CrmFollowUp;
use App\Models\User;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $query = Deal::with(['stage', 'category', 'agent', 'watchers', 'lead']);

        // Main Filters
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('deal_name', 'like', "%{$search}%")
                  ->orWhere('lead_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('contact_details', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('close_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('pipeline') && $request->pipeline !== 'All') {
            $query->where('pipeline', $request->pipeline);
        }

        if ($request->filled('category') && $request->category !== 'All') {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category)->orWhere('id', $request->category);
            });
        }

        if ($request->filled('product') && $request->product !== 'All') {
            $query->where('product', $request->product);
        }

        if ($request->filled('agent_id')) {
            $query->where('deal_agent_id', $request->agent_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('min_value')) {
            $query->where('value', '>=', $request->min_value);
        }

        if ($request->filled('max_value')) {
            $query->where('value', '<=', $request->max_value);
        }

        if ($request->filled('stages')) {
            $stagesFilter = is_array($request->stages) ? $request->stages : [$request->stages];
            $query->whereHas('stage', function($q) use ($stagesFilter) {
                $q->whereIn('id', $stagesFilter)->orWhereIn('name', $stagesFilter);
            });
        }

        // Deal KPI Cards Statistics
        $allDeals = (clone $query)->get();
        $stages = DealStage::orderBy('order')->get();

        $wonStageIds = $stages->filter(fn($s) => str_contains(strtolower($s->name), 'won') || str_contains(strtolower($s->name), 'concreted'))->pluck('id')->toArray();
        $lostStageIds = $stages->filter(fn($s) => str_contains(strtolower($s->name), 'lost'))->pluck('id')->toArray();

        $kpiStats = [
            'total' => $allDeals->count(),
            'open' => $allDeals->filter(fn($d) => !in_array($d->deal_stage_id, array_merge($wonStageIds, $lostStageIds)))->count(),
            'won' => $allDeals->filter(fn($d) => in_array($d->deal_stage_id, $wonStageIds))->count(),
            'lost' => $allDeals->filter(fn($d) => in_array($d->deal_stage_id, $lostStageIds))->count(),
            'pipeline_value' => $allDeals->filter(fn($d) => !in_array($d->deal_stage_id, array_merge($wonStageIds, $lostStageIds)))->sum('value'),
            'weighted_value' => $allDeals->filter(fn($d) => !in_array($d->deal_stage_id, array_merge($wonStageIds, $lostStageIds)))->sum(function($d) {
                return $d->weighted_value ?? $d->calculateWeightedValue();
            }),
        ];

        $categories = DealCategory::all();
        $agents = User::select('id', 'name')->get();
        $pipelines = ['Sales Pipeline', 'Marketing Pipeline', 'Other Pipeline'];

        // Kanban View Check
        if ($request->has('view') && $request->view === 'kanban') {
            $dealsByStage = [];

            foreach ($stages as $stage) {
                $dealsByStage[$stage->id] = (clone $query)
                    ->where('deal_stage_id', $stage->id)
                    ->get();
            }

            return view('admin.deals.index', compact('stages', 'dealsByStage', 'categories', 'agents', 'pipelines', 'kpiStats'));
        }

        // Table View
        $perPage = (int) ($request->per_page ?? 10);
        $deals = $query->latest()->paginate($perPage)->appends($request->query());

        return view('admin.deals.index', compact('deals', 'categories', 'stages', 'agents', 'pipelines', 'kpiStats'));
    }

    public function create(Request $request)
    {
        $categories = DealCategory::all();
        $stages = DealStage::orderBy('order')->get();
        $agents = User::select('id', 'name')->get();
        $leads = LeadContact::select('id', 'contact_name', 'company_name', 'email', 'phone')->get();

        $selectedLead = null;
        if ($request->filled('lead_id')) {
            $selectedLead = LeadContact::find($request->lead_id);
        }

        return view('admin.deals.create', compact('categories', 'stages', 'agents', 'leads', 'selectedLead'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'deal_name' => 'required|string|max:255',
            'lead_id' => 'nullable|exists:lead_contacts,id',
            'lead_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_details' => 'required|string',
            'value' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'probability' => 'nullable|integer|min:0|max:100',
            'close_date' => 'required|date',
            'deal_stage_id' => 'required|exists:deal_stages,id',
            'pipeline' => 'nullable|string|max:255',
            'product' => 'nullable|string|max:255',
            'priority' => 'nullable|string|max:20',
            'deal_agent_id' => 'nullable|exists:users,id',
            'deal_category_id' => 'nullable|exists:deal_categories,id',
            'next_follow_up' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $stage = DealStage::find($request->deal_stage_id);
        $probability = $request->probability ?? ($stage ? $stage->default_probability : 10);
        $val = (float) $request->value;
        $weightedValue = round(($val * $probability) / 100, 2);

        $deal = Deal::create([
            'lead_id' => $request->lead_id,
            'deal_name' => $request->deal_name,
            'lead_name' => $request->lead_name,
            'company_name' => $request->company_name,
            'contact_details' => $request->contact_details,
            'value' => $val,
            'currency' => $request->currency ?? 'INR',
            'probability' => $probability,
            'weighted_value' => $weightedValue,
            'close_date' => $request->close_date,
            'next_follow_up' => $request->next_follow_up,
            'deal_stage_id' => $request->deal_stage_id,
            'pipeline' => $request->pipeline ?? 'Sales Pipeline',
            'product' => $request->product,
            'priority' => $request->priority ?? 'medium',
            'deal_agent_id' => $request->deal_agent_id,
            'deal_category_id' => $request->deal_category_id,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        CrmActivity::create([
            'lead_id' => $request->lead_id,
            'deal_id' => $deal->id,
            'type' => 'deal_created',
            'title' => 'Deal Created',
            'description' => "Deal '{$deal->deal_name}' created with value {$deal->currency} {$deal->value}.",
            'created_by' => auth()->id(),
            'activity_date' => now(),
        ]);

        return redirect()->route('admin.deals.index')->with('success', 'Deal created successfully.');
    }

    public function show(Deal $deal)
    {
        $deal->load(['stage', 'category', 'agent', 'lead', 'activities.creator', 'followUps.assignee']);
        $stages = DealStage::orderBy('order')->get();
        $users = User::select('id', 'name')->get();

        return view('admin.deals.show', compact('deal', 'stages', 'users'));
    }

    public function edit(Deal $deal)
    {
        $categories = DealCategory::all();
        $stages = DealStage::orderBy('order')->get();
        $agents = User::select('id', 'name')->get();
        $leads = LeadContact::select('id', 'contact_name', 'company_name', 'email', 'phone')->get();

        return view('admin.deals.edit', compact('deal', 'categories', 'stages', 'agents', 'leads'));
    }

    public function update(Request $request, Deal $deal)
    {
        $request->validate([
            'deal_name' => 'required|string|max:255',
            'lead_id' => 'nullable|exists:lead_contacts,id',
            'lead_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'contact_details' => 'required|string',
            'value' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'probability' => 'nullable|integer|min:0|max:100',
            'close_date' => 'required|date',
            'deal_stage_id' => 'required|exists:deal_stages,id',
            'pipeline' => 'nullable|string|max:255',
            'product' => 'nullable|string|max:255',
            'priority' => 'nullable|string|max:20',
            'deal_agent_id' => 'nullable|exists:users,id',
            'deal_category_id' => 'nullable|exists:deal_categories,id',
            'next_follow_up' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $stage = DealStage::find($request->deal_stage_id);
        $probability = $request->probability ?? ($stage ? $stage->default_probability : $deal->probability);
        $val = (float) $request->value;
        $weightedValue = round(($val * $probability) / 100, 2);

        $oldStageId = $deal->deal_stage_id;

        $deal->update([
            'lead_id' => $request->lead_id,
            'deal_name' => $request->deal_name,
            'lead_name' => $request->lead_name,
            'company_name' => $request->company_name,
            'contact_details' => $request->contact_details,
            'value' => $val,
            'currency' => $request->currency ?? 'INR',
            'probability' => $probability,
            'weighted_value' => $weightedValue,
            'close_date' => $request->close_date,
            'next_follow_up' => $request->next_follow_up,
            'deal_stage_id' => $request->deal_stage_id,
            'pipeline' => $request->pipeline ?? 'Sales Pipeline',
            'product' => $request->product,
            'priority' => $request->priority ?? 'medium',
            'deal_agent_id' => $request->deal_agent_id,
            'deal_category_id' => $request->deal_category_id,
            'notes' => $request->notes,
        ]);

        if ($oldStageId !== $deal->deal_stage_id) {
            CrmActivity::create([
                'lead_id' => $deal->lead_id,
                'deal_id' => $deal->id,
                'type' => 'status_change',
                'title' => 'Deal Stage Changed',
                'description' => "Deal stage updated to '{$deal->stage->name}'.",
                'created_by' => auth()->id(),
                'activity_date' => now(),
            ]);
        }

        return redirect()->route('admin.deals.index')->with('success', 'Deal updated successfully.');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();
        return redirect()->route('admin.deals.index')->with('success', 'Deal deleted successfully.');
    }

    public function updateStage(Request $request, Deal $deal)
    {
        $request->validate([
            'stage_id' => 'required|exists:deal_stages,id',
            'lost_reason' => 'nullable|string',
            'lost_notes' => 'nullable|string',
        ]);

        $stage = DealStage::findOrFail($request->stage_id);
        $probability = $stage->default_probability;
        $weightedValue = round(((float)$deal->value * $probability) / 100, 2);

        $updateData = [
            'deal_stage_id' => $stage->id,
            'probability' => $probability,
            'weighted_value' => $weightedValue,
        ];

        if (str_contains(strtolower($stage->name), 'lost')) {
            if ($request->filled('lost_reason')) {
                $updateData['lost_reason'] = $request->lost_reason;
            }
            if ($request->filled('lost_notes')) {
                $updateData['lost_notes'] = $request->lost_notes;
            }
        }

        $deal->update($updateData);

        CrmActivity::create([
            'lead_id' => $deal->lead_id,
            'deal_id' => $deal->id,
            'type' => 'status_change',
            'title' => 'Stage Moved to ' . $stage->name,
            'description' => "Deal moved to '{$stage->name}' (Probability: {$probability}%).",
            'created_by' => auth()->id(),
            'activity_date' => now(),
        ]);

        if ($request->ajax() || $request->expectsJson() || $request->wantsJson() || $request->isJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Deal stage updated successfully',
                'deal' => $deal->fresh(['stage'])
            ]);
        }

        return back()->with('success', 'Stage updated successfully.');
    }

    public function updateLostReason(Request $request, Deal $deal)
    {
        $request->validate([
            'lost_reason' => 'required|string',
            'lost_notes' => 'nullable|string',
        ]);

        $deal->update([
            'lost_reason' => $request->lost_reason,
            'lost_notes' => $request->lost_notes,
        ]);

        CrmActivity::create([
            'lead_id' => $deal->lead_id,
            'deal_id' => $deal->id,
            'type' => 'status_change',
            'title' => 'Deal Lost Reason Recorded',
            'description' => "Reason: {$request->lost_reason}. Notes: {$request->lost_notes}",
            'created_by' => auth()->id(),
            'activity_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Lost reason updated successfully']);
    }

    public function addFollowUp(Request $request, Deal $deal)
    {
        $request->validate([
            'follow_up_date' => 'required|date',
            'follow_up_notes' => 'nullable|string'
        ]);

        $deal->update(['next_follow_up' => $request->follow_up_date]);

        CrmFollowUp::create([
            'lead_id' => $deal->lead_id,
            'deal_id' => $deal->id,
            'follow_up_type' => 'call',
            'date' => $request->follow_up_date,
            'description' => $request->follow_up_notes,
            'assigned_to' => $deal->deal_agent_id ?? auth()->id(),
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        CrmActivity::create([
            'lead_id' => $lead_id ?? $deal->lead_id,
            'deal_id' => $deal->id,
            'type' => 'follow_up',
            'title' => 'Follow-up Scheduled for Deal',
            'description' => 'Follow up scheduled for ' . $request->follow_up_date,
            'created_by' => auth()->id(),
            'activity_date' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Follow up added successfully']);
        }

        return back()->with('success', 'Follow up added successfully.');
    }

    public function storeActivity(Request $request, Deal $deal)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $activity = CrmActivity::create([
            'lead_id' => $deal->lead_id,
            'deal_id' => $deal->id,
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => auth()->id(),
            'activity_date' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Activity recorded successfully', 'activity' => $activity]);
    }

    public function storeFollowUp(Request $request, Deal $deal)
    {
        $request->validate([
            'follow_up_type' => 'required|string',
            'date' => 'required|date',
            'time' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        $followUp = CrmFollowUp::create([
            'lead_id' => $deal->lead_id,
            'deal_id' => $deal->id,
            'follow_up_type' => $request->follow_up_type,
            'date' => $request->date,
            'time' => $request->time,
            'assigned_to' => $request->assigned_to ?? $deal->deal_agent_id,
            'description' => $request->description,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        $deal->update(['next_follow_up' => $request->date]);

        return response()->json(['success' => true, 'message' => 'Follow-up scheduled successfully']);
    }

    public function convertToClient(Request $request, Deal $deal)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $lead = $deal->lead;
            $name = $deal->lead_name ?? ($lead ? $lead->contact_name : 'Client');
            $company = $deal->company_name ?? ($lead ? $lead->company_name : null);
            $email = $lead->email ?? null;
            $phone = $lead->phone ?? ($lead->mobile ?? null);

            $existingClient = null;
            if (!empty($email)) {
                $existingClient = Client::where('email', $email)->first();
            }
            if (!$existingClient && !empty($company)) {
                $existingClient = Client::where('company_name', $company)->first();
            }

            if (!$existingClient) {
                Client::create([
                    'name' => $name,
                    'email' => $email ?? (strtolower(str_replace(' ', '.', $name)) . '@example.com'),
                    'mobile' => $phone,
                    'company_name' => $company,
                    'company_address' => $lead->address ?? null,
                    'website' => $lead->website ?? null,
                    'status' => 'active',
                    'password' => bcrypt('123456'),
                    'added_by' => auth()->id(),
                ]);
            }

            if ($lead) {
                $lead->update(['type' => 'client', 'status' => 'converted', 'converted_at' => now(), 'converted_by' => auth()->id()]);
            }

            CrmActivity::create([
                'lead_id' => $deal->lead_id,
                'deal_id' => $deal->id,
                'type' => 'converted',
                'title' => 'Deal Converted to Client',
                'description' => "Deal '{$deal->deal_name}' converted to client by " . (auth()->user()->name ?? 'Admin'),
                'created_by' => auth()->id(),
                'activity_date' => now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Deal successfully converted to Client!']);
        } catch (\Exception $e) {
            \Log::error('Deal convertToClient error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to convert deal: ' . $e->getMessage()], 500);
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'ids' => 'required|array',
            'ids.*' => 'exists:deals,id'
        ]);

        switch ($request->action) {
            case 'delete':
                Deal::whereIn('id', $request->ids)->delete();
                $message = 'Selected deals deleted successfully.';
                break;

            case 'change_stage':
                $request->validate(['stage_id' => 'required|exists:deal_stages,id']);
                Deal::whereIn('id', $request->ids)->update(['deal_stage_id' => $request->stage_id]);
                $message = 'Stage updated for selected deals.';
                break;

            case 'assign_agent':
                $request->validate(['agent_id' => 'required|exists:users,id']);
                Deal::whereIn('id', $request->ids)->update(['deal_agent_id' => $request->agent_id]);
                $message = 'Agent assigned to selected deals.';
                break;

            default:
                return back()->with('error', 'Invalid action.');
        }

        return back()->with('success', $message);
    }

    public function export(Request $request)
    {
        $deals = Deal::with(['stage', 'category', 'agent', 'lead'])->get();

        $fileName = 'deals-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function() use ($deals) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Deal Name', 'Lead Name', 'Company Name', 'Contact Details', 'Value', 'Currency',
                'Probability', 'Weighted Value', 'Close Date', 'Next Follow Up', 'Stage', 'Category',
                'Agent', 'Pipeline', 'Product', 'Priority', 'Lost Reason', 'Notes'
            ]);

            foreach ($deals as $deal) {
                fputcsv($file, [
                    $deal->deal_name,
                    $deal->lead_name,
                    $deal->company_name ?? '',
                    $deal->contact_details,
                    $deal->value,
                    $deal->currency ?? 'INR',
                    ($deal->probability ?? 0) . '%',
                    $deal->weighted_value ?? $deal->calculateWeightedValue(),
                    $deal->close_date ? $deal->close_date->format('Y-m-d') : '',
                    $deal->next_follow_up ? $deal->next_follow_up->format('Y-m-d') : '',
                    $deal->stage->name ?? '',
                    $deal->category->name ?? '',
                    $deal->agent->name ?? '',
                    $deal->pipeline,
                    $deal->product ?? '',
                    $deal->priority ?? 'medium',
                    $deal->lost_reason ?? '',
                    $deal->notes ?? ''
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt'
        ]);

        try {
            $file = $request->file('file');
            $csvData = file_get_contents($file->getRealPath());
            $rows = array_map('str_getcsv', explode("\n", $csvData));

            $header = array_shift($rows);

            foreach ($rows as $row) {
                if (count($row) > 0 && count($row) == count($header)) {
                    $row = array_combine($header, $row);

                    $stage = DealStage::where('name', $row['Stage'] ?? '')->first();
                    $category = !empty($row['Category']) ? DealCategory::where('name', $row['Category'])->first() : null;
                    $agent = !empty($row['Agent']) ? User::where('name', $row['Agent'])->first() : null;

                    $val = (float)($row['Value'] ?? 0);
                    $prob = isset($row['Probability']) ? (int) str_replace('%', '', $row['Probability']) : ($stage ? $stage->default_probability : 10);
                    $weighted = round(($val * $prob) / 100, 2);

                    Deal::create([
                        'deal_name' => $row['Deal Name'] ?? 'Imported Deal',
                        'lead_name' => $row['Lead Name'] ?? 'Imported Lead',
                        'company_name' => $row['Company Name'] ?? null,
                        'contact_details' => $row['Contact Details'] ?? '',
                        'value' => $val,
                        'currency' => $row['Currency'] ?? 'INR',
                        'probability' => $prob,
                        'weighted_value' => $weighted,
                        'close_date' => !empty($row['Close Date']) ? $row['Close Date'] : now()->addDays(30),
                        'next_follow_up' => !empty($row['Next Follow Up']) ? $row['Next Follow Up'] : null,
                        'deal_stage_id' => $stage->id ?? 1,
                        'deal_category_id' => $category->id ?? null,
                        'deal_agent_id' => $agent->id ?? null,
                        'pipeline' => $row['Pipeline'] ?? 'Sales Pipeline',
                        'product' => $row['Product'] ?? null,
                        'priority' => $row['Priority'] ?? 'medium',
                        'notes' => $row['Notes'] ?? null,
                    ]);
                }
            }

            return back()->with('success', 'Deals imported successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to import deals: ' . $e->getMessage());
        }
    }
}

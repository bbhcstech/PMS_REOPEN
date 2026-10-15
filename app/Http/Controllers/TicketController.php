<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Project;
use App\Models\TicketGroup;
use App\Models\Reply;
use App\Models\TicketActivity;
use App\Services\SystemNotificationService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Support\TicketAccess;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        if (\App\Support\PlatformSupportAccess::companyAdmin(auth()->user())) {
            $company = \App\Models\Central\Company::find(auth()->user()->company_id);
            if ($company) app(\App\Services\InternalTicketRouting::class)->reconcile($company);
        }
        $agents = User::where('company_id', auth()->user()->company_id)->whereRaw('LOWER(role) IN (?, ?, ?, ?, ?)', ['employee', 'developer', 'dev', 'manager', 'hr'])->whereNull('archived_at')->get();
        $projects = Project::where('company_id', auth()->user()->company_id)->get();

        $tickets = Ticket::query();
        $user = auth()->user();

        if (! TicketAccess::canManage()) {
            $tickets->where(function ($employeeTickets) use ($user) {
                $employeeTickets->where('agent_id', $user->id)
                    ->orWhere('requester_id', $user->id);
            });
        }

        if ($request->filled('duration')) {
            $duration = $request->duration;

            if (str_contains($duration, ' to ')) {
                [$start, $end] = explode(' to ', $duration);
                try {
                    $startDate = Carbon::parse($start)->startOfDay();
                    $endDate = Carbon::parse($end)->endOfDay();
                    $tickets->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // invalid format - ignore
                }
            } else {
                switch ($duration) {
                    case 'Today':
                        $startDate = Carbon::today();
                        $endDate = Carbon::today()->endOfDay();
                        break;
                    case 'Last 30 Days':
                        $startDate = Carbon::now()->subDays(29)->startOfDay();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    case 'This Month':
                        $startDate = Carbon::now()->startOfMonth();
                        $endDate = Carbon::now()->endOfMonth();
                        break;
                    case 'Last Month':
                        $startDate = Carbon::now()->subMonth()->startOfMonth();
                        $endDate = Carbon::now()->subMonth()->endOfMonth();
                        break;
                    case 'Last 90 Days':
                        $startDate = Carbon::now()->subDays(89)->startOfDay();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    case 'Last 6 Months':
                        $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    case 'Last 1 Year':
                        $startDate = Carbon::now()->subYear()->startOfMonth();
                        $endDate = Carbon::now()->endOfDay();
                        break;
                    default:
                        $startDate = null;
                        $endDate = null;
                }

                if ($startDate && $endDate) {
                    $tickets->whereBetween('created_at', [$startDate, $endDate]);
                }
            }
        }

        if ($request->filled('project_id')) {
            $tickets->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $tickets->where('status', $request->status);
        }

        $tickets = $tickets->latest()->get();
        $currentProject = $request->filled('project_id') ? $projects->firstWhere('id', $request->project_id) : null;
        $project = $currentProject;

        return view('admin.tickets.index', compact('tickets', 'agents', 'projects', 'currentProject', 'project'));
    }

    public function create()
    {
        abort_unless($this->canCreateWorkItems(), 403);

        $agents = User::where('company_id', auth()->user()->company_id)->whereRaw('LOWER(role) IN (?, ?, ?, ?, ?)', ['employee', 'developer', 'dev', 'manager', 'hr'])->whereNull('archived_at')->get();
        $projects = Project::where('company_id', auth()->user()->company_id)->get();
        $clients = User::where('company_id', auth()->user()->company_id)->where('role', 'client')->get();
        $employees = User::where('company_id', auth()->user()->company_id)->whereRaw('LOWER(role) <> ?', ['client'])->whereNull('archived_at')->get();
        $ticketgroup  = TicketGroup::all();

        return view('admin.tickets.create', compact('agents', 'projects','ticketgroup','clients', 'employees'));
    }

   public function store(Request $request)
{
    abort_unless($this->canCreateWorkItems(), 403);
    if (!TicketAccess::canManage()) {
        abort_unless((int) $request->requester_name === (int) auth()->id() && !$request->filled('agent_id'), 403, 'You can raise your own ticket; assignments are managed by your company.');
    }

    $request->validate([
        'requester_type' => 'required|in:client,employee',
        'requester_name' => ['required', Rule::exists('tenant.users', 'id')->where('company_id', auth()->user()->company_id)],
        'group_id'       => 'nullable|integer|exists:tenant.ticket_groups,id',
        'subject'        => 'required|string|max:255',
        'description'    => 'required|string',
        'attachment'     => 'nullable|file|mimes:jpeg,png,jpg,pdf,docx,xlsx,xls,csv,txt,zip|max:10240',
        'priority'       => 'nullable|in:low,medium,high,critical',
        'channel'        => 'nullable|string|max:100',
        'tags'           => 'nullable|string|max:255',
        'type_id'        => 'nullable|integer|min:1',
        'agent_id'       => ['nullable', Rule::exists('tenant.users', 'id')->where('company_id', auth()->user()->company_id)->whereIn('role', ['employee', 'developer', 'dev', 'manager', 'hr'])],
        'project_id'     => ['nullable', Rule::exists('tenant.projects', 'id')->where('company_id', auth()->user()->company_id)],
        'affected_module' => 'nullable|string|max:255',
        'deadline'       => 'nullable|date',
        'status'         => 'nullable|in:open,in_progress,pending,closed,resolved,reopened',
    ]);

    // create ticket
    $this->authorizeAssignment($request);
    $ticket = new Ticket();

    $ticket->requester_type = $request->requester_type;
    $ticket->requester_id = $request->requester_name; // actual user ID selected
    $ticket->requester_name = \App\Models\User::find($request->requester_name)->name ?? null;
    $ticket->group_id = $request->group_id;
    $ticket->agent_id = $request->agent_id;
    $ticket->project_id = $request->project_id;
    $ticket->affected_module = $request->affected_module;
    $ticket->deadline = $request->deadline;
    $ticket->type_id = $request->type_id;
    $ticket->subject = $request->subject;
    $ticket->description = $request->description;
    $ticket->priority = $request->priority ?? 'medium';
    $ticket->channel = $request->channel;
    $ticket->tags = $request->tags;
    $ticket->status = $request->status ?? 'open';

    if ($request->hasFile('attachment')) {
        $file = $request->file('attachment');
        $filename = (string) Str::uuid() . '.' . $file->extension();
        $file->move(public_path('admin/uploads/tickets'), $filename);
        $ticket->attachment = 'admin/uploads/tickets/' . $filename;
    }

    DB::connection('tenant')->transaction(function () use ($ticket, $request) {
    $ticket->save();

    // store ticket activity
    TicketActivity::create([
        'ticket_id' => $ticket->id,
        'project_id' => $request->project_id,
        'user_id' => auth()->id(),
        'assigned_to' => $ticket->agent_id,
        'channel_id' => $ticket->channel_id ?? null,
        'group_id' => $ticket->group_id,
        'type_id' => $ticket->type_id,
        'status' => $ticket->status,
        'priority' => $ticket->priority,
        'type' => 'Status Update',
        'content' => 'Ticket status changed to ' . Ticket::statusLabel($ticket->status),
    ]);
    });

    $this->notifyParticipants($ticket, 'Ticket Raised', auth()->user()->name . ' raised ticket #' . $ticket->id . ': ' . $ticket->subject);

    return redirect()->route('tickets.index')->with('success', 'Ticket created successfully');
}

    public function edit($id)
    {
        abort_unless(TicketAccess::canManage(), 403);
        $ticket = Ticket::findOrFail($id);
        TicketAccess::authorize($ticket);
        $agents = User::where('company_id', auth()->user()->company_id)->whereRaw('LOWER(role) IN (?, ?, ?, ?, ?)', ['employee', 'developer', 'dev', 'manager', 'hr'])->whereNull('archived_at')->get();
        $projects = Project::where('company_id', auth()->user()->company_id)->get();

        $users = User::where('company_id', auth()->user()->company_id)->whereRaw('LOWER(role) IN (?, ?, ?, ?, ?)', ['employee', 'developer', 'dev', 'manager', 'hr'])->whereNull('archived_at')->get();
        $clients = User::where('company_id', auth()->user()->company_id)->where('role', 'client')->get();
        $employees = User::where('company_id', auth()->user()->company_id)->whereRaw('LOWER(role) <> ?', ['client'])->whereNull('archived_at')->get();
        $ticketgroup  = TicketGroup::all();

        return view('admin.tickets.edit', compact('ticket', 'agents', 'projects', 'clients', 'users','ticketgroup','clients', 'employees'));
    }

    public function update(Request $request, $id)
    {
        abort_unless(TicketAccess::canManage(), 403);
        $request->validate([
            'requester_type' => 'required|in:client,employee',
            'requester_name' => ['required', Rule::exists('tenant.users', 'id')->where('company_id', auth()->user()->company_id)],
            'group_id' => 'nullable|integer|exists:tenant.ticket_groups,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,docx,xlsx,xls,csv,txt,zip|max:10240',
            'priority' => 'nullable|in:low,medium,high,critical',
            'channel' => 'nullable|string|max:100',
            'tags' => 'nullable|string|max:255',
            'type_id' => 'nullable|integer|min:1',
            'agent_id' => ['nullable', Rule::exists('tenant.users', 'id')->where('company_id', auth()->user()->company_id)->whereIn('role', ['employee', 'developer', 'dev', 'manager', 'hr'])],
            'project_id' => ['nullable', Rule::exists('tenant.projects', 'id')->where('company_id', auth()->user()->company_id)],
            'affected_module' => 'nullable|string|max:255',
            'deadline' => 'nullable|date',
        ]);

        $ticket = Ticket::findOrFail($id);
        TicketAccess::authorize($ticket);

        $ticket->requester_type = $request->requester_type;
        $this->authorizeAssignment($request, $ticket);
        $ticket->requester_id = $request->requester_name;
        $ticket->requester_name = User::find($request->requester_name)->name ?? null;
        $ticket->group_id = $request->group_id;
        $ticket->agent_id = $request->agent_id;
        $ticket->project_id = $request->project_id;
        $ticket->affected_module = $request->affected_module;
        $ticket->deadline = $request->deadline;
        $ticket->type_id = $request->type_id;
        $ticket->subject = $request->subject;
        $ticket->description = $request->description;
        $ticket->priority = $request->priority ?? 'medium';
        $ticket->channel = $request->channel;
        $ticket->tags = $request->tags;

        if ($request->hasFile('attachment')) {
            if ($ticket->attachment && file_exists(public_path($ticket->attachment))) {
                unlink(public_path($ticket->attachment));
            }

            $file = $request->file('attachment');
            $filename = (string) Str::uuid() . '.' . $file->extension();
            $file->move(public_path('admin/uploads/tickets'), $filename);
            $ticket->attachment = 'admin/uploads/tickets/' . $filename;
        }

        DB::connection('tenant')->transaction(function () use ($ticket, $request) {
    $ticket->save();

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'project_id' => $request->project_id,
            'user_id' => auth()->id(),
            'assigned_to' => $ticket->agent_id,
            'channel_id' => $ticket->channel_id ?? null,
            'group_id' => $ticket->group_id,
            'type_id' => $ticket->type_id,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'type' => 'Status Update',
            'content' => 'Ticket status changed to ' . Ticket::statusLabel($ticket->status),
        ]);
    });

        $this->notifyParticipants($ticket, 'Ticket Updated', auth()->user()->name . ' updated ticket #' . $ticket->id);
        return redirect()->route('tickets.index')->with('success', 'Ticket updated successfully');
    }

    public function changeStatus(Request $request)
    {
        $request->validate([
            'ticketId' => 'required|exists:tickets,id',
            'status' => 'required|in:open,in_progress,pending,resolved,closed,reopened'
        ]);

        $ticket = Ticket::findOrFail($request->ticketId);
        $user = auth()->user();

        TicketAccess::authorize($ticket, false, true);

        DB::connection('tenant')->transaction(function () use ($ticket, $request) {
            $ticket->status = $request->status;
            $ticket->save();
            TicketActivity::create([
                'ticket_id' => $ticket->id,
                'project_id' => $ticket->project_id,
                'user_id' => auth()->id(),
                'assigned_to' => $ticket->agent_id,
                'channel_id' => null,
                'group_id' => $ticket->group_id,
                'type_id' => $ticket->type_id,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'type' => 'Status Update',
                'content' => 'Ticket status changed to ' . Ticket::statusLabel($ticket->status),
            ]);
        });

        $this->notifyParticipants($ticket,
            'Ticket Status Updated',
            ($user?->name ?? 'Someone') . ' changed ticket #' . $ticket->id . ' to ' . Ticket::statusLabel($ticket->status),
            route('tickets.show', $ticket->id),
            ['ticket_id' => $ticket->id, 'project_id' => $ticket->project_id, 'type' => 'ticket_status_updated', 'icon' => 'fa-ticket', 'color' => 'info']
        );

        if (! $request->expectsJson() && ! $request->ajax()) {
            return back()->with('success', 'Ticket status updated.');
        }

        return response()->json(['status' => 'success', 'message' => 'Status updated', 'ticket_id' => $ticket->id, 'new_status' => $ticket->status]);
    }

    public function destroy($ticketId)
    {
        $ticket = Ticket::findOrFail($ticketId);
        TicketAccess::authorize($ticket, true);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket removed successfully.');
    }

    public function storeGroup(Request $request)
    {
        abort_unless(TicketAccess::canManage(), 403);
        $request->validate([
            'group_name' => 'required|string|max:255|unique:ticket_groups,group_name'
        ]);

        $group = TicketGroup::create([
            'group_name' => $request->group_name
        ]);

        return response()->json(['status' => 'success', 'group' => $group]);
    }

    public function fetchGroups()
    {
        $groups = TicketGroup::all();
        return response()->json($groups);
    }

    public function destroygroup($id)
    {
        abort_unless(TicketAccess::canManage(), 403);
        $group = TicketGroup::findOrFail($id);
        $group->delete();

        return response()->json(['status' => 'success']);
    }

    public function show($id)
    {
        $ticket = Ticket::with(['requester', 'agent', 'project.client', 'group', 'replies.user', 'activities.user'])->findOrFail($id);
        TicketAccess::authorize($ticket);
        $agents = User::where('company_id', auth()->user()->company_id)->whereRaw('LOWER(role) IN (?, ?, ?, ?, ?)', ['employee', 'developer', 'dev', 'manager', 'hr'])->whereNull('archived_at')->get();
        $groups = TicketGroup::all();
        $replies = Reply::where('ticket_id', $ticket->id)->with('user')->latest()->get();
        $activities = TicketActivity::where('ticket_id', $ticket->id)->with('user')->latest()->get();
        $timeLogs = \App\Models\TimeLog::where('project_id', $ticket->project_id)
            ->where('memo', 'like', "%Ticket #{$ticket->id}%")
            ->with('employee')
            ->latest()
            ->get();

        return view('admin.tickets.show', compact('ticket', 'agents', 'groups', 'replies', 'activities', 'timeLogs'));
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,docx,xlsx,xls,csv,txt,zip|max:2048',
        ]);

        $ticket = Ticket::findOrFail($id);
        TicketAccess::authorize($ticket);

        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = (string) Str::uuid() . '.' . $file->extension();
            $file->move(public_path('admin/uploads/ticket_replies'), $filename);
            $attachmentPath = 'admin/uploads/ticket_replies/' . $filename;
        }

        DB::connection('tenant')->transaction(function () use ($ticket, $request, $attachmentPath) {
        Reply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'attachment' => $attachmentPath,
        ]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'project_id' => $ticket->project_id,
            'user_id' => auth()->id(),
            'assigned_to' => $ticket->agent_id,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'type' => 'Comment Added',
            'content' => auth()->user()->name . ' added a comment/update: ' . Str::limit($request->message, 80),
        ]);
        });

        $this->notifyParticipants($ticket, 'Ticket Reply', auth()->user()->name . ' replied to ticket #' . $ticket->id);
        return redirect()->back()->with('success', 'Reply added successfully.');
    }

    public function updateDetails(Request $request, $id)
    {
        abort_unless(TicketAccess::canManage(), 403);
        $request->validate([
            'agent_id' => ['nullable', Rule::exists('tenant.users', 'id')->where('company_id', auth()->user()->company_id)->whereIn('role', ['employee', 'developer', 'dev', 'manager', 'hr'])],
            'group_id' => 'nullable|exists:ticket_groups,id',
            'priority' => 'nullable|in:low,medium,high,critical',
            'status' => 'sometimes|required|in:open,in_progress,pending,resolved,closed,reopened',
            'type_id' => 'nullable|integer|min:1',
        ]);

        $ticket = Ticket::findOrFail($id);
        TicketAccess::authorize($ticket);


        $this->authorizeAssignment($request, $ticket);
        $ticket->fill($request->only(['agent_id', 'group_id', 'priority', 'status', 'type_id']));

        DB::connection('tenant')->transaction(function () use ($ticket, $request) {
        $ticket->save();

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'project_id' => $ticket->project_id,
            'user_id' => auth()->id(),
            'assigned_to' => $ticket->agent_id,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'type' => 'Details Updated',
            'content' => 'Ticket details updated by ' . auth()->user()->name,
        ]);
        });

        $this->notifyParticipants($ticket, 'Ticket Details Updated', auth()->user()->name . ' updated ticket #' . $ticket->id);
        return redirect()->back()->with('success', 'Ticket details updated.');
    }

    public function reopen(Request $request, $id)
    {
        abort_unless(TicketAccess::canManage(), 403);
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $ticket = Ticket::findOrFail($id);
        TicketAccess::authorize($ticket);
        DB::connection('tenant')->transaction(function () use ($ticket, $request) {
        $ticket->status = 'reopened';
        $ticket->save();

        $reasonText = $request->input('reason', 'Client reported the issue is not fully resolved after delivery.');

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'project_id' => $ticket->project_id,
            'user_id' => auth()->id(),
            'assigned_to' => $ticket->agent_id,
            'status' => 'reopened',
            'priority' => $ticket->priority,
            'type' => 'Reopened',
            'content' => 'Ticket reopened by Admin. Statement: ' . $reasonText,
        ]);
        });

        $this->notifyParticipants($ticket,
            'Ticket Reopened',
            auth()->user()->name . ' reopened Ticket #' . $ticket->id . ': ' . $ticket->subject,
            route('tickets.show', $ticket->id),
            [
                'ticket_id' => $ticket->id,
                'project_id' => $ticket->project_id,
                'type' => 'ticket_reopened',
                'icon' => 'fa-redo',
                'color' => 'warning',
            ]
        );

        return redirect()->back()->with('success', 'Ticket reopened successfully and notification sent to developer.');
    }

    public function logTime(Request $request, $id)
    {
        $request->validate([
            'hours' => 'required|numeric|min:0.1|max:24',
            'memo' => 'required|string|max:500',
            'start_date' => 'nullable|date',
        ]);

        $ticket = Ticket::findOrFail($id);
        TicketAccess::authorize($ticket);
        $user = auth()->user();
        $logDate = $request->input('start_date', now()->toDateString());
        TicketAccess::authorize($ticket, false, true);
        abort_unless($ticket->project_id, 422, 'Link a project before logging project time.');
        $hours = (float) $request->hours;
        $memo = $request->memo;

        $startTime = '09:00:00';
        $endTime = Carbon::parse($startTime)->addMinutes((int) ($hours * 60))->format('H:i:s');

        \App\Models\TimeLog::create([
            'user_id' => $user->id,
            'project_id' => $ticket->project_id,
            'start_date' => $logDate,
            'start_time' => $startTime,
            'end_date' => $logDate,
            'end_time' => $endTime,
            'total_hours' => $hours,
            'memo' => "Logged on Ticket #{$ticket->id}: {$ticket->subject} - {$memo}",
        ]);

        TicketActivity::create([
            'ticket_id' => $ticket->id,
            'project_id' => $ticket->project_id,
            'user_id' => $user->id,
            'assigned_to' => $ticket->agent_id,
            'status' => $ticket->status,
            'priority' => $ticket->priority,
            'type' => 'Time Logged',
            'content' => "{$user->name} logged {$hours} hrs on Ticket: {$memo}",
        ]);

        return redirect()->back()->with('success', "Logged {$hours} hours to timesheet successfully.");
    }

    public function bulkAction(Request $request)
    {
        abort_unless(TicketAccess::canManage(), 403);
        $request->validate(['tickets' => 'required|array|min:1', 'tickets.*' => 'required|integer|distinct', 'action' => 'required|in:delete,change_status', 'status' => ['required_if:action,change_status', 'nullable', Rule::in(array_keys(Ticket::STATUSES))]]);
        $tickets = Ticket::whereIn('id', $request->tickets)->get();
        abort_unless($tickets->count() === count($request->tickets), 403);
        foreach ($tickets as $ticket) TicketAccess::authorize($ticket, true);
        DB::connection('tenant')->transaction(function () use ($tickets, $request) {
            foreach ($tickets as $ticket) {
                if ($request->action === 'delete') $ticket->delete();
                else $this->changeStatus(Request::create('/tickets/change-status', 'POST', ['ticketId' => $ticket->id, 'status' => $request->status]));
            }
        });

        return response()->json(['status' => 'success']);
    }

    public function bulkDelete(Request $request)
    {
        abort_unless(TicketAccess::canManage(), 403);
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No tickets selected',
            ], 422);
        }

        $request->validate(['ids.*' => 'required|integer|distinct']);
        $tickets = Ticket::whereIn('id', $ids)->get();
        abort_unless($tickets->count() === count($ids), 403);
        foreach ($tickets as $ticket) TicketAccess::authorize($ticket, true);
        DB::connection('tenant')->transaction(fn () => $tickets->each->delete());

        return response()->json([
            'status'  => 'success',
            'message' => 'Tickets deleted successfully',
        ]);
    }

    private function canCreateWorkItems(): bool
    {
        return (bool) auth()->user()?->company_id;
    }

    private function authorizeAssignment(Request $request, ?Ticket $ticket = null): void
    {
        if (!$request->has('agent_id') || (int) $request->agent_id === (int) $ticket?->agent_id) return;
        abort_unless(\App\Services\WorkforceAccess::isAdmin(auth()->user())
            || (TicketAccess::canManage() && auth()->user()->moduleToggleAllows('tickets', 'assign')), 403, 'You do not have permission to assign tickets.');
    }

    private function notifyParticipants(Ticket $ticket, string $title, string $message, ?string $url = null, array $data = []): void
    {
        $companyId = (int) auth()->user()->company_id;
        $recipients = SystemNotificationService::admins($companyId)->merge(User::where('company_id', $companyId)
            ->whereIn('id', array_filter([$ticket->agent_id, $ticket->requester_id]))->get())->unique('id');
        DB::connection('tenant')->afterCommit(fn () => SystemNotificationService::notifyUser($recipients, $title, $message,
            $url ?? route('tickets.show', $ticket->id), $data + ['ticket_id' => $ticket->id, 'type' => 'internal_ticket', 'icon' => 'fa-ticket']));
    }
}

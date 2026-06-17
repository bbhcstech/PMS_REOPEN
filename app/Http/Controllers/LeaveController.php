<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeaveRequest;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveService;
use App\Services\SystemNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function __construct(private LeaveService $leaveService)
    {
    }

    public function index(Request $request)
    {
        $this->leaveService->ensureDefaultTypes();
        $policy = $this->leaveService->policy();
        $leaveTypes = $this->leaveService->leaveTypes();
        $isAdmin = $this->isAdmin();
        $employees = $isAdmin ? $this->employeeQuery()->get() : User::where('id', Auth::id())->get();
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 30, 50, 100], true) ? $perPage : 20;

        foreach ($employees as $employee) {
            $this->leaveService->ensureBalance($employee);
        }

        if (! $isAdmin) {
            $this->leaveService->ensureBalance(Auth::user());
        }

        $query = Leave::with(['user.employeeDetail.department', 'leaveType', 'approver', 'rejector'])
            ->whereNull('archived_at');

        if (! $isAdmin) {
            $query->where('user_id', Auth::id());
        }

        if ($isAdmin && $request->filled('employee')) {
            $query->where('user_id', $request->employee);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->to);
        }

        $leaves = $query->latest()->paginate($perPage)->withQueryString();
        $allLeaves = (clone $query)->get();

        $balances = LeaveBalance::with('user')
            ->whereIn('user_id', $isAdmin ? $employees->pluck('id') : [Auth::id()])
            ->latest('year_start')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $stats = [
            'total' => $allLeaves->count(),
            'pending' => $allLeaves->where('status', 'pending')->count(),
            'approved' => $allLeaves->where('status', 'approved')->count(),
            'rejected' => $allLeaves->where('status', 'rejected')->count(),
            'unpaid' => $allLeaves->where('is_unpaid', true)->count(),
        ];

        $archivedCount = $isAdmin ? Leave::whereNotNull('archived_at')->count() : 0;
        $policyNotice = $this->leaveService->policyNotice($policy);

        return view('admin.leaves.index', compact(
            'policy',
            'leaveTypes',
            'isAdmin',
            'employees',
            'leaves',
            'balances',
            'stats',
            'archivedCount',
            'perPage',
            'policyNotice'
        ));
    }

    public function create()
    {
        $this->leaveService->ensureDefaultTypes();
        $policy = $this->leaveService->policy();
        $leaveTypes = $this->leaveService->leaveTypes();
        $users = $this->employeeQuery()->get();
        $selectedUser = $this->isAdmin() ? null : Auth::user();
        $balance = $selectedUser ? $this->leaveService->ensureBalance($selectedUser) : null;
        $policyNotice = $this->leaveService->policyNotice($policy);

        return view('admin.leaves.create', compact('policy', 'leaveTypes', 'users', 'selectedUser', 'balance', 'policyNotice'));
    }

    public function store(StoreLeaveRequest $request)
    {
        $this->leaveService->ensureDefaultTypes();
        $actor = Auth::user();
        $employee = $this->isAdmin() && $request->filled('user_id')
            ? User::findOrFail($request->user_id)
            : $actor;

        $type = LeaveType::findOrFail($request->leave_type_id);
        $data = $request->validated();
        $data['emergency_flag'] = $request->boolean('emergency_flag');
        $data['half_day_flag'] = $request->boolean('half_day_flag');
        $data['status'] = $this->isAdmin() ? ($request->status ?: 'pending') : 'pending';

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $name = time() . '-' . preg_replace('/[^A-Za-z0-9_.-]/', '-', $file->getClientOriginalName());
            $file->move(public_path('admin/uploads/leave-file'), $name);
            $data['attachment'] = 'admin/uploads/leave-file/' . $name;
        }

        $errors = $this->leaveService->validateRequest(
            $employee,
            $type,
            Carbon::parse($data['start_date']),
            Carbon::parse($data['end_date']),
            $data
        );

        if ($errors) {
            return back()->withErrors($errors)->withInput();
        }

        $leave = $this->leaveService->createLeave($employee, $type, $data, $actor);

        if (! $this->isAdmin()) {
            SystemNotificationService::notifyAdmins(
                'New Leave Request',
                $employee->name . ' requested ' . $type->name . ' from ' . $data['start_date'] . ' to ' . $data['end_date'],
                route('leaves.show', $leave->id),
                ['employee_id' => $employee->id, 'entity_type' => Leave::class, 'entity_id' => $leave->id, 'type' => 'leave_requested', 'icon' => 'fa-calendar-days']
            );
        } else {
            SystemNotificationService::notifyUser(
                $employee,
                'Leave Added',
                'A leave request was added for you by ' . $actor->name,
                route('leaves.show', $leave->id),
                ['employee_id' => $employee->id, 'entity_type' => Leave::class, 'entity_id' => $leave->id, 'type' => 'leave_added', 'icon' => 'fa-calendar-days']
            );
        }

        return redirect()->route('leaves.index')->with('success', 'Leave request submitted successfully.');
    }

    public function show(Leave $leave)
    {
        $this->authorizeLeaveAccess($leave);
        $leave->load(['user.employeeDetail.department', 'leaveType', 'approvals.user', 'approver', 'rejector']);

        return view('admin.leaves.show', compact('leave'));
    }

    public function edit(Leave $leave)
    {
        $this->authorizeLeaveAccess($leave);
        $policy = $this->leaveService->policy();
        $leaveTypes = $this->leaveService->leaveTypes();
        $users = $this->employeeQuery()->get();

        return view('admin.leaves.create', [
            'policy' => $policy,
            'leaveTypes' => $leaveTypes,
            'users' => $users,
            'selectedUser' => $leave->user,
            'balance' => $leave->user ? $this->leaveService->ensureBalance($leave->user) : null,
            'policyNotice' => $this->leaveService->policyNotice($policy),
            'leave' => $leave,
        ]);
    }

    public function update(StoreLeaveRequest $request, Leave $leave)
    {
        $this->authorizeLeaveAccess($leave);
        abort_if($leave->status === 'approved' && ! $this->isAdmin(), 403);

        $employee = $this->isAdmin() && $request->filled('user_id') ? User::findOrFail($request->user_id) : $leave->user;
        $type = LeaveType::findOrFail($request->leave_type_id);
        $data = $request->validated();
        $data['emergency_flag'] = $request->boolean('emergency_flag');
        $data['half_day_flag'] = $request->boolean('half_day_flag');

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $name = time() . '-' . preg_replace('/[^A-Za-z0-9_.-]/', '-', $file->getClientOriginalName());
            $file->move(public_path('admin/uploads/leave-file'), $name);
            $data['attachment'] = 'admin/uploads/leave-file/' . $name;
        }

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);

        $leave->update([
            'user_id' => $employee->id,
            'leave_type_id' => $type->id,
            'type' => match ($type->code) {
                'SL' => 'sick',
                'CL' => 'casual',
                'ML' => 'maternity',
                'UL' => 'leave-without-pay',
                default => strtolower($type->code),
            },
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'date' => $start->toDateString(),
            'total_days' => $this->leaveService->calculateDays($start, $end, $data['half_day_flag']),
            'reason' => $data['reason'],
            'attachment' => $data['attachment'] ?? $leave->attachment,
            'files' => $data['attachment'] ?? $leave->files,
            'apology_note' => $data['apology_note'] ?? null,
            'emergency_flag' => $data['emergency_flag'],
            'half_day_flag' => $data['half_day_flag'],
            'contact_during_leave' => $data['contact_during_leave'] ?? null,
        ]);

        $this->leaveService->syncBalanceCounters($employee);

        return redirect()->route('leaves.index')->with('success', 'Leave request updated successfully.');
    }

    public function updateStatus(Request $request, Leave $leave)
    {
        $this->ensureAdmin();
        $request->validate([
            'status' => ['required', 'in:approved,rejected,pending,unpaid'],
            'note' => ['nullable', 'string', 'max:2000'],
            'rejection_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->status === 'approved') {
            $this->leaveService->approve($leave, Auth::user(), $request->note);
        } elseif ($request->status === 'unpaid') {
            $this->leaveService->approve($leave, Auth::user(), $request->note, true);
        } elseif ($request->status === 'rejected') {
            $this->leaveService->reject($leave, Auth::user(), $request->rejection_reason ?: $request->note ?: 'Rejected by HR/Admin.');
        } else {
            $leave->update(['status' => 'pending', 'approval_status' => 'pending']);
        }

        if ($leave->user) {
            SystemNotificationService::notifyUser(
                $leave->user,
                'Leave ' . ucfirst($leave->status),
                'Your leave request has been marked ' . ucfirst($leave->status) . '.',
                route('leaves.show', $leave->id),
                ['employee_id' => $leave->user_id, 'entity_type' => Leave::class, 'entity_id' => $leave->id, 'type' => 'leave_status_updated', 'icon' => 'fa-calendar-check']
            );
        }

        return back()->with('success', 'Leave request status updated.');
    }

    public function updatePolicy(Request $request)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'annual_leaves' => ['required', 'numeric', 'min:0'],
            'sick_leave_limit' => ['required', 'numeric', 'min:0'],
            'casual_leave_limit' => ['required', 'numeric', 'min:0'],
            'maternity_leave_limit' => ['required', 'numeric', 'min:0'],
            'casual_advance_days' => ['required', 'integer', 'min:0', 'max:60'],
            'casual_manual_review_days' => ['required', 'integer', 'min:0', 'max:30'],
            'auto_approve_casual_leave' => ['nullable'],
            'hr_approval_required' => ['nullable'],
            'allow_sick_apology' => ['nullable'],
            'allow_carry_forward' => ['nullable'],
            'max_carry_forward' => ['nullable', 'integer', 'min:0'],
            'unpaid_leave_handling' => ['required', 'in:unpaid_leave,absent'],
            'maternity_is_paid' => ['nullable'],
            'maternity_requires_document' => ['nullable'],
            'leave_monetary_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach (['auto_approve_casual_leave', 'hr_approval_required', 'allow_sick_apology', 'allow_carry_forward', 'maternity_is_paid', 'maternity_requires_document'] as $field) {
            $data[$field] = $request->boolean($field);
        }

        $data['leave_year_start_month'] = 4;
        $data['leave_year_end_month'] = 3;
        $data['fiscal_year_start'] = now()->month >= 4 ? now()->year . '-04-01' : (now()->year - 1) . '-04-01';
        $data['fiscal_year_end'] = now()->month >= 4 ? (now()->year + 1) . '-03-31' : now()->year . '-03-31';

        $this->leaveService->updatePolicy($data, Auth::user());

        SystemNotificationService::notifyEmployees(
            'Leave Policy Updated',
            'Leave policy has been updated by ' . Auth::user()->name . '.',
            route('leaves.index'),
            ['type' => 'leave_policy_updated', 'icon' => 'fa-file-contract']
        );

        return back()->with('success', 'Leave policy updated and employee balances recalculated successfully.');
    }

    public function resetEmployeeLeaves($id)
    {
        $this->ensureAdmin();
        $employee = User::findOrFail($id);
        $balance = $this->leaveService->ensureBalance($employee);
        $policy = $this->leaveService->policy();
        $balance->forceFill([
            'allocated_leaves' => $policy->annual_leaves,
            'remaining_leaves' => $policy->annual_leaves,
            'used_leaves' => 0,
            'sick_used' => 0,
            'casual_used' => 0,
            'maternity_used' => 0,
            'unpaid_used' => 0,
            'absent_count' => 0,
        ])->save();
        $this->leaveService->syncBalanceCounters($employee, $balance);

        return back()->with('success', 'Leave balance reset for ' . $employee->name . '.');
    }

    public function updatePaidStatus(Request $request)
    {
        $this->ensureAdmin();
        $request->validate(['leave_id' => 'required|exists:leaves,id', 'paid' => 'required|boolean']);
        $leave = Leave::findOrFail($request->leave_id);
        $totalDays = (float) ($leave->total_days ?: 1);
        $leave->update([
            'is_paid' => $request->boolean('paid'),
            'is_unpaid' => ! $request->boolean('paid'),
            'paid' => $request->boolean('paid'),
            'paid_days' => $request->boolean('paid') ? $totalDays : 0,
            'unpaid_days' => $request->boolean('paid') ? 0 : $totalDays,
            'payroll_deduction_flag' => ! $request->boolean('paid'),
        ]);

        $this->leaveService->syncBalanceCounters($leave->user);

        return response()->json(['success' => true, 'message' => 'Paid status updated.']);
    }

    public function destroy($id)
    {
        $leave = Leave::findOrFail($id);
        $this->authorizeLeaveAccess($leave);
        abort_if(! $this->isAdmin() && $leave->status !== 'pending', 403);
        $user = $leave->user;
        $leave->delete();
        if (! $this->isAdmin()) {
            SystemNotificationService::notifyAdmins(
                'Leave Request Deleted',
                Auth::user()->name . ' deleted a leave request.',
                route('leaves.index'),
                ['employee_id' => Auth::id(), 'type' => 'leave_deleted', 'icon' => 'fa-trash']
            );
        }
        if ($user) {
            $this->leaveService->syncBalanceCounters($user);
        }

        return back()->with('success', 'Leave request deleted successfully.');
    }

    public function archive(Request $request)
    {
        $this->ensureAdmin();

        $query = Leave::with(['user.employeeDetail.department', 'leaveType'])
            ->whereNotNull('archived_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', '%' . $search . '%')
                    ->orWhere('status', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('leaveType', fn ($typeQuery) => $typeQuery->where('name', 'like', '%' . $search . '%'));
            });
        }

        $leaves = $query->orderByDesc('archived_at')->paginate(20)->withQueryString();

        return view('admin.leaves.archive', compact('leaves'));
    }

    public function archiveLeave(Leave $leave)
    {
        $this->ensureAdmin();

        if ($leave->archived_at) {
            return back()->with('success', 'Leave request is already archived.');
        }

        $leave->forceFill(['archived_at' => now()])->save();

        return back()->with('success', 'Leave request archived successfully.');
    }

    public function bulkArchive(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:leaves,id'],
        ]);

        $count = Leave::whereIn('id', $request->ids)
            ->whereNull('archived_at')
            ->update(['archived_at' => now()]);

        return response()->json(['message' => $count . ' leave request(s) archived successfully.']);
    }

    public function archiveAll()
    {
        $this->ensureAdmin();

        $count = Leave::whereNull('archived_at')->update(['archived_at' => now()]);

        return back()->with('success', $count . ' leave request(s) archived successfully.');
    }

    public function restore($id)
    {
        $this->ensureAdmin();

        $leave = Leave::whereNotNull('archived_at')->findOrFail($id);
        $leave->forceFill(['archived_at' => null])->save();

        return back()->with('success', 'Leave request restored successfully.');
    }

    public function bulkAction(Request $request)
    {
        $this->ensureAdmin();
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:leaves,id'],
            'action' => ['required', 'in:delete,change_status,archive'],
            'status' => ['nullable', 'in:approved,rejected,pending,unpaid'],
        ]);

        $leaves = Leave::whereIn('id', $request->ids)->get();

        foreach ($leaves as $leave) {
            if ($request->action === 'archive') {
                $leave->forceFill(['archived_at' => now()])->save();
                continue;
            }

            if ($request->action === 'delete') {
                $leave->delete();
                continue;
            }

            if ($request->status === 'approved') {
                $this->leaveService->approve($leave, Auth::user(), 'Bulk approved');
            } elseif ($request->status === 'unpaid') {
                $this->leaveService->approve($leave, Auth::user(), 'Bulk converted to unpaid', true);
            } elseif ($request->status === 'rejected') {
                $this->leaveService->reject($leave, Auth::user(), 'Bulk rejected');
            } else {
                $leave->update(['status' => 'pending', 'approval_status' => 'pending']);
            }
        }

        return response()->json(['message' => 'Bulk action completed successfully.']);
    }

    public function bulkDelete(Request $request)
    {
        $request->merge(['action' => 'delete']);
        return $this->bulkAction($request);
    }

    public function export(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'type' => ['nullable', 'in:copy,excel,csv,pdf,print'],
            'employee' => ['nullable', 'integer', 'exists:users,id'],
            'leave_type_id' => ['nullable', 'integer', 'exists:leave_types,id'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $query = Leave::with(['user', 'leaveType'])->whereNull('archived_at');
        if ($request->filled('employee')) {
            $query->where('user_id', $request->employee);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->to);
        }
        $leaves = (clone $query)->latest()->get();
        $this->syncLeavePayrollForUsers($leaves);
        $leaves = $query->latest()->get();
        $type = $request->input('type', 'csv');
        $filenameDate = now()->format('Y-m-d');

        if ($type === 'pdf') {
            return Pdf::loadView('admin.leaves.exports.pdf', compact('leaves'))
                ->download('leave-report-' . $filenameDate . '.pdf');
        }

        if ($type === 'print') {
            return view('admin.leaves.exports.print', compact('leaves'));
        }

        $headers = ['Employee', 'Type', 'Start Date', 'End Date', 'Days', 'Paid Days', 'Unpaid Days', 'Status', 'Payment', 'Reason'];

        if ($type === 'copy') {
            $lines = [$this->exportLine($headers, "\t")];
            foreach ($leaves as $leave) {
                $lines[] = $this->exportLine($this->leaveExportRow($leave), "\t");
            }

            return response(implode("\n", $lines), 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        if ($type === 'excel') {
            return response()->stream(function () use ($leaves, $headers) {
                $out = fopen('php://output', 'w');
                fputcsv($out, $headers, "\t");
                foreach ($leaves as $leave) {
                    fputcsv($out, $this->leaveExportRow($leave), "\t");
                }
                fclose($out);
            }, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=leave-report-' . $filenameDate . '.xls',
            ]);
        }

        $responseHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=leave-report-' . $filenameDate . '.csv',
        ];

        return response()->stream(function () use ($leaves, $headers) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($leaves as $leave) {
                fputcsv($out, $this->leaveExportRow($leave));
            }
            fclose($out);
        }, 200, $responseHeaders);
    }

    public function leaveReport(Request $request)
    {
        return $this->index($request);
    }

    public function calendar()
    {
        $this->leaveService->ensureDefaultTypes();
        $isAdmin = $this->isAdmin();
        $employee_data = $isAdmin ? $this->employeeQuery()->get() : User::where('id', Auth::id())->get();
        $leaveTypes = $this->leaveService->leaveTypes();

        return view('admin.leaves.calendar', compact('employee_data', 'leaveTypes', 'isAdmin'));
    }

    public function calendarData(Request $request)
    {
        $query = Leave::with(['user', 'leaveType'])->whereNull('archived_at');
        if (! $this->isAdmin()) {
            $query->where('user_id', Auth::id());
        }
        if ($this->isAdmin() && $request->filled('employee')) {
            $query->where('user_id', $request->employee);
        }
        if ($request->filled('leave_type')) {
            $query->where('leave_type_id', $request->leave_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start')) {
            $query->whereDate('end_date', '>=', Carbon::parse($request->start)->toDateString());
        }
        if ($request->filled('end')) {
            $query->whereDate('start_date', '<=', Carbon::parse($request->end)->toDateString());
        }

        $leaves = $query->get();
        $this->syncLeavePayrollForUsers($leaves);
        $leaves = $query->get();

        return response()->json($leaves->map(function (Leave $leave) {
            return [
                'title' => ($leave->user?->name ?? 'Employee') . ' - ' . $leave->type_label,
                'start' => optional($leave->start_date)->toDateString(),
                'end' => $leave->end_date ? $leave->end_date->copy()->addDay()->toDateString() : null,
                'color' => match ($leave->status) {
                    'approved' => $leave->is_unpaid ? '#f97316' : '#10b981',
                    'rejected' => '#ef4444',
                    default => '#f59e0b',
                },
                'extendedProps' => [
                    'employee' => $leave->user?->name ?? 'Employee',
                    'status' => $leave->status,
                    'type' => $leave->type_label,
                    'duration' => $leave->half_day_flag ? 'Half Day' : ($leave->duration ?: 'Full Day'),
                    'reason' => $leave->reason,
                    'payment' => $leave->is_unpaid ? 'Unpaid' : 'Paid',
                    'editUrl' => route('leaves.edit', $leave->id),
                    'showUrl' => route('leaves.show', $leave->id),
                ],
            ];
        }));
    }

    private function isAdmin(): bool
    {
        return in_array(strtolower((string) Auth::user()?->role), ['admin', 'hr'], true);
    }

    private function ensureAdmin(): void
    {
        abort_if(! $this->isAdmin(), 403);
    }

    private function employeeQuery()
    {
        return User::with('employeeDetail.department')
            ->where('role', 'employee')
            ->orderBy('name');
    }

    private function authorizeLeaveAccess(Leave $leave): void
    {
        abort_if(! $this->isAdmin() && $leave->user_id !== Auth::id(), 403);
    }

    private function leaveExportRow(Leave $leave): array
    {
        return [
            $leave->user?->name ?? 'N/A',
            $leave->type_label,
            optional($leave->start_date)->format('Y-m-d'),
            optional($leave->end_date)->format('Y-m-d'),
            (string) $leave->total_days,
            (string) ($leave->paid_days ?? 0),
            (string) ($leave->unpaid_days ?? 0),
            ucfirst($leave->status),
            $leave->is_unpaid ? 'Unpaid' : 'Paid',
            (string) $leave->reason,
        ];
    }

    private function exportLine(array $columns, string $delimiter): string
    {
        return implode($delimiter, array_map(fn ($value) => str_replace(["\r", "\n", "\t"], ' ', (string) $value), $columns));
    }

    private function syncLeavePayrollForUsers($leaves): void
    {
        $leaves->filter(fn (Leave $leave) => $leave->user && $leave->start_date)
            ->groupBy(fn (Leave $leave) => $leave->user_id . ':' . $leave->start_date->format('Y-m-d'))
            ->each(function ($group) {
                $leave = $group->first();
                $this->leaveService->ensureBalance($leave->user, $leave->start_date);
            });
    }
}

<?php

namespace App\Services;

use App\Models\Central\Company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Route, Schema};
use Illuminate\Support\Str;

/**
 * Live, computed facts for the company assistant ("how many holidays this week?", "office timings",
 * "my leave balance", "tasks due this week", "how do I apply for leave?").
 *
 * Every section reads the same services/tables the workspace pages use, is limited to the signed-in
 * user's company, and respects the user's module permissions. Personal sections (attendance, leave,
 * tasks, payslips) only ever describe the signed-in user. Only explicit, approved fields are emitted.
 */
class CompanyAiLiveContext
{
    /** Topic => trigger words (prefix match for words of 4+ letters). Includes common Hindi/Bengali words. */
    private const TOPICS = [
        'holidays' => ['holiday', 'holidays', 'festival', 'vacation', 'weekend', 'off', 'offs', 'weekoff', 'closed', 'closure', 'chutti', 'chhutti', 'chuti', 'chhuti', 'puja', 'diwali', 'eid', 'christmas', 'holi', 'durga', 'independence', 'republic'],
        'schedule' => ['timing', 'timings', 'time', 'office', 'hours', 'start', 'starts', 'end', 'ends', 'shift', 'schedule', 'working', 'workday', 'workdays', 'late', 'clock', 'punch', 'wfh', 'remote', 'home', 'break', 'lunch', 'policy', 'rule', 'rules', 'half', 'grace', 'open', 'opens', 'close'],
        'attendance' => ['attendance', 'present', 'absent', 'late', 'clock', 'clocked', 'punch', 'worked', 'hours', 'checkin', 'checkout', 'wfh', 'halfday', 'days'],
        'leaves' => ['leave', 'leaves', 'balance', 'sick', 'casual', 'maternity', 'paternity', 'quota', 'remaining', 'unpaid', 'apology', 'chutti', 'chhutti', 'chuti', 'chhuti', 'pending', 'approved', 'rejected'],
        'work' => ['task', 'tasks', 'todo', 'deadline', 'deadlines', 'due', 'overdue', 'project', 'projects', 'assigned', 'work', 'pending', 'priority', 'milestone'],
        'events' => ['event', 'events', 'meeting', 'meetings', 'celebration', 'party', 'program', 'programme', 'upcoming', 'rsvp', 'function', 'townhall', 'webinar'],
        'team' => ['manager', 'boss', 'report', 'reports', 'reporting', 'team', 'colleague', 'colleagues', 'department', 'departments', 'designation', 'designations', 'employee', 'employees', 'staff', 'headcount', 'who', 'lead', 'members', 'people', 'organization', 'organisation', 'company', 'joined', 'joining'],
        'payslips' => ['salary', 'payslip', 'payslips', 'pay', 'paid', 'payroll', 'net', 'ctc', 'deduction', 'deductions', 'earning', 'earnings'],
        'guide' => ['how', 'where', 'apply', 'page', 'menu', 'find', 'navigate', 'create', 'add', 'submit', 'raise', 'change', 'update', 'password', 'profile', 'document', 'documents', 'ticket', 'tickets', 'access', 'module', 'modules', 'feature', 'features', 'use', 'steps', 'option', 'settings', 'link'],
        'today' => ['today', 'tomorrow', 'yesterday', 'week', 'weekly', 'month', 'monthly', 'year', 'date', 'day', 'days', 'now', 'current', 'next', 'last', 'this', 'upcoming', 'aaj', 'kal'],
    ];

    /** Topics the question refers to (empty when the question is not about the workspace). */
    public function topics(string $question): array
    {
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower($question), $matches);
        $words = array_unique($matches[0]);
        $topics = [];
        foreach (self::TOPICS as $topic => $keywords) {
            foreach ($words as $word) {
                foreach ($keywords as $keyword) {
                    if ($word === $keyword || (mb_strlen($keyword) >= 4 && str_starts_with($word, $keyword))) {
                        $topics[] = $topic;
                        continue 3;
                    }
                }
            }
        }
        // "this/next/today" alone is not a workspace topic; it only sharpens another one.
        return array_values(array_unique($topics)) === ['today'] ? [] : array_values(array_unique($topics));
    }

    /** Live records for the requested topics. Always starts with "live:today" when anything is returned. */
    public function build(Company $company, User $user, array $topics): array
    {
        if (! $topics) {
            return [];
        }
        $now = now();
        $records = [$this->today($company, $user, $now)];
        $sections = [
            'holidays' => fn () => $this->holidays($company, $user, $now),
            'schedule' => fn () => $this->schedule($user, $now),
            'attendance' => fn () => $this->attendance($company, $user, $now),
            'leaves' => fn () => $this->leaves($user, $now),
            'work' => fn () => $this->work($company, $user, $now),
            'events' => fn () => $this->events($company, $user, $now),
            'team' => fn () => $this->team($company, $user),
            'payslips' => fn () => $this->payslips($user),
            'guide' => fn () => $this->guide($user),
        ];
        // Closely related questions need neighbouring facts (a holiday question needs the week's offs,
        // an attendance question needs office timings).
        $related = ['attendance' => ['schedule'], 'holidays' => ['schedule'], 'leaves' => ['holidays'], 'schedule' => ['holidays']];
        foreach ($topics as $topic) foreach ($related[$topic] ?? [] as $extra) $topics[] = $extra;
        foreach (array_unique($topics) as $topic) {
            if (! isset($sections[$topic])) continue;
            try {
                $record = $sections[$topic]();
            } catch (\Throwable $e) {
                report($e);
                $record = null;
            }
            if ($record) $records[] = $record;
        }
        return $records;
    }

    private function record(string $id, string $label, array $facts): array
    {
        return ['id' => 'live:' . $id, 'type' => 'live', 'label' => $label, 'facts' => $facts];
    }

    private function day(Carbon $date): string
    {
        return $date->format('D, d M Y');
    }

    private function today(Company $company, User $user, Carbon $now): array
    {
        $detail = $user->employeeDetail;
        return $this->record('today', 'Today & you', [
            'today' => $now->format('l, d M Y'),
            'current_time' => $now->format('h:i A') . ' (' . $now->getTimezone()->getName() . ')',
            'this_week' => $this->day($now->copy()->startOfWeek()) . ' to ' . $this->day($now->copy()->endOfWeek()),
            'next_week' => $this->day($now->copy()->addWeek()->startOfWeek()) . ' to ' . $this->day($now->copy()->addWeek()->endOfWeek()),
            'this_month' => $now->format('F Y'),
            'company' => $company->name,
            'asked_by' => $user->name,
            'role' => ucfirst((string) $user->role),
            'designation' => $detail?->designation?->name,
            'department' => $detail?->department?->name,
            'employee_id' => $detail?->employee_id,
            'reports_to' => $detail?->reportingTo?->name,
            'joining_date' => $detail?->joining_date ? Carbon::parse($detail->joining_date)->format('d M Y') : null,
        ]);
    }

    /** Holidays + weekly offs, exactly as the attendance calendar and holiday list compute them. */
    private function holidays(Company $company, User $user, Carbon $now): ?array
    {
        $yearStart = $now->copy()->startOfYear();
        $horizon = $now->copy()->addDays(120)->max($now->copy()->endOfYear());
        $map = WorkScheduleService::getHolidaysAndOffDays($yearStart, $horizon, (int) $company->id);
        ksort($map);
        $settings = WorkScheduleService::getSettings();
        $working = array_values($settings['working_days'] ?? []);
        $allDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $between = function (Carbon $from, Carbon $to, bool $weeklyOff) use ($map) {
            $list = [];
            foreach ($map as $date => $entry) {
                if ($date < $from->toDateString() || $date > $to->toDateString() || (bool) ($entry['is_weekly_off'] ?? false) !== $weeklyOff) continue;
                $list[] = $this->day(Carbon::parse($date)) . ($weeklyOff ? '' : ' — ' . ($entry['occassion'] ?? 'Holiday'));
            }
            return $list;
        };
        $weekStart = $now->copy()->startOfWeek(); $weekEnd = $now->copy()->endOfWeek();
        $nextStart = $weekStart->copy()->addWeek(); $nextEnd = $weekEnd->copy()->addWeek();
        $thisWeek = $between($weekStart, $weekEnd, false);
        $nextWeek = $between($nextStart, $nextEnd, false);
        $thisMonth = $between($now->copy()->startOfMonth(), $now->copy()->endOfMonth(), false);
        $nextMonthDate = $now->copy()->addMonthNoOverflow();
        $nextMonth = $between($nextMonthDate->copy()->startOfMonth(), $nextMonthDate->copy()->endOfMonth(), false);
        $upcoming = array_slice($between($now->copy()->startOfDay(), $horizon, false), 0, 15);
        $year = $between($yearStart, $now->copy()->endOfYear(), false);
        $todayEntry = $map[$now->toDateString()] ?? null;

        return $this->record('holidays', 'Holidays & weekly offs', [
            'working_days' => $working ? implode(', ', $working) : 'Monday to Friday',
            'weekly_off_days' => implode(', ', array_values(array_diff($allDays, $working))) ?: 'None',
            'today_is' => $todayEntry ? (($todayEntry['is_weekly_off'] ?? false) ? 'a weekly off' : 'a holiday: ' . ($todayEntry['occassion'] ?? 'Holiday')) : 'a working day',
            'holidays_this_week_count' => count($thisWeek),
            'holidays_this_week' => $thisWeek ?: 'None',
            'weekly_offs_this_week' => $between($weekStart, $weekEnd, true) ?: 'None',
            'holidays_next_week_count' => count($nextWeek),
            'holidays_next_week' => $nextWeek ?: 'None',
            'holidays_this_month_count' => count($thisMonth),
            'holidays_this_month' => $thisMonth ?: 'None',
            'holidays_next_month_count' => count($nextMonth),
            'holidays_next_month' => $nextMonth ?: 'None',
            'upcoming_holidays' => $upcoming ?: 'None',
            'holidays_this_year_count' => count($year),
            'holidays_this_year' => array_slice($year, 0, 40) ?: 'None',
            'note' => 'Holidays exclude regular weekly offs, which are listed separately.',
            'holiday_list_page' => $this->url('holidays.calendar'),
        ]);
    }

    private function schedule(User $user, Carbon $now): array
    {
        $settings = WorkScheduleService::getSettings();
        $policy = WorkScheduleService::attendancePolicy();
        $time = fn ($value) => $value ? Carbon::parse((string) $value)->format('h:i A') : null;
        $wfh = WorkScheduleService::isUserWfhOnDate($user->id, $now->toDateString(), $settings);
        return $this->record('schedule', 'Office timings & attendance policy', [
            'office_hours' => $time($settings['work_start_time'] ?? null) . ' to ' . $time($settings['work_end_time'] ?? null),
            'break_minutes' => (int) ($settings['break_duration'] ?? 0),
            'working_days' => implode(', ', $settings['working_days'] ?? []),
            'weekly_work_from_home_days' => ($settings['weekly_wfh_days'] ?? []) ? implode(', ', $settings['weekly_wfh_days']) : 'None',
            'shifts_enabled' => (string) ($settings['shift_enabled'] ?? '0') === '1' ? 'Yes' : 'No',
            'attendance_office_start' => $time($policy->office_start_time),
            'marked_late_after' => $time($policy->late_time),
            'half_day_after' => $time($policy->half_day_after_time),
            'office_end' => $time($policy->office_end_time),
            'clock_in_window' => $policy->clock_window_enabled ? ($time($policy->clock_in_opens_at) . ' to ' . $time($policy->clock_in_closes_at)) : 'Not restricted',
            'full_day_minimum_hours' => $policy->half_day_threshold_minutes ? round($policy->half_day_threshold_minutes / 60, 1) : null,
            'half_day_minimum_hours' => $policy->day_off_threshold_minutes ? round($policy->day_off_threshold_minutes / 60, 1) : null,
            'your_work_from_home_today' => $wfh ? 'Yes' . (! empty($wfh['reason']) ? ' (' . $wfh['reason'] . ')' : '') : 'No',
        ]);
    }

    private function attendance(Company $company, User $user, Carbon $now): ?array
    {
        if (! $user->canViewModule('attendance')) return null;
        $calendar = app(AttendanceCalendar::class);
        $summarize = function (Carbon $from, Carbon $to) use ($calendar, $user) {
            $counts = [];
            $seconds = 0;
            foreach ($calendar->daysForUser($user, $from, $to) as $day) {
                if ($day['date']->isFuture() && ! $day['date']->isToday()) continue;
                $counts[$day['status']] = ($counts[$day['status']] ?? 0) + 1;
                $seconds += (int) $day['seconds'];
            }
            unset($counts['not_marked'], $counts['not_joined']);
            $counts = array_combine(array_map(fn ($key) => str_replace('_', ' ', $key), array_keys($counts)), array_values($counts));
            return ['days_by_status' => $counts ?: 'No days recorded yet', 'hours_worked' => round($seconds / 3600, 1)];
        };
        $todayRows = \App\Models\Attendance::where('user_id', $user->id)->whereNull('archived_at')->whereDate('date', $now->toDateString())->orderBy('id')->get();
        $first = $todayRows->first();
        $last = $todayRows->last();
        $facts = [
            'today' => $first ? [
                'status' => (string) $first->status . (($first->late ?? 'no') === 'yes' ? ' (late)' : ''),
                'clocked_in_at' => $first->clock_in ? Carbon::parse($first->clock_in)->format('h:i A') : null,
                'clocked_out_at' => $last?->clock_out ? Carbon::parse($last->clock_out)->format('h:i A') : 'Still clocked in',
            ] : 'Not clocked in yet today',
            'this_week' => $summarize($now->copy()->startOfWeek(), $now->copy()),
            'this_month' => $summarize($now->copy()->startOfMonth(), $now->copy()),
            'my_attendance_page' => $this->url('attendance.index'),
        ];
        // Company-wide figures only for people who manage attendance.
        if (WorkforceAccess::isAdmin($user) || WorkforceAccess::isHr($user)) {
            $staff = User::where('company_id', $company->id)->whereIn('role', ['employee', 'hr', 'manager'])->whereNull('archived_at');
            $staffIds = (clone $staff)->pluck('id');
            $present = \App\Models\Attendance::whereIn('user_id', $staffIds)->whereNull('archived_at')->whereDate('date', $now->toDateString());
            $onLeave = \App\Models\Leave::whereIn('user_id', $staffIds)->where('status', 'approved')
                ->whereDate('start_date', '<=', $now->toDateString())->whereDate('end_date', '>=', $now->toDateString());
            $facts['company_today'] = [
                'active_staff' => $staffIds->count(),
                'clocked_in_today' => (clone $present)->distinct('user_id')->count('user_id'),
                'late_today' => (clone $present)->where('late', 'yes')->distinct('user_id')->count('user_id'),
                'on_approved_leave_today' => $onLeave->distinct('user_id')->count('user_id'),
                'pending_leave_requests' => \App\Models\Leave::whereIn('user_id', $staffIds)->where('status', 'pending')->count(),
            ];
        }
        return $this->record('attendance', 'Attendance', $facts);
    }

    private function leaves(User $user, Carbon $now): ?array
    {
        if (! $user->canViewModule('leaves')) return null;
        $service = app(LeaveService::class);
        $balance = $service->ensureBalance($user, $now);
        $year = $service->leaveYearDates($now);
        $types = $service->leaveTypes()->map(fn ($type) => $type->name . ($type->annual_limit ? ' (up to ' . $type->annual_limit . ' days/year' : ' (') . ($type->is_paid ? ', paid)' : ', unpaid)'))->values()->all();
        $requests = \App\Models\Leave::with('leaveType')->where('user_id', $user->id)
            ->when(Schema::connection('tenant')->hasColumn('leaves', 'archived_at'), fn ($q) => $q->whereNull('archived_at'))
            ->orderByDesc('id')->limit(40)->get();
        $describe = fn ($leave) => trim(($leave->leaveType?->name ?? 'Leave') . ': '
            . ($leave->start_date ? $this->day(Carbon::parse($leave->start_date)) : '') . ($leave->end_date && $leave->end_date != $leave->start_date ? ' to ' . $this->day(Carbon::parse($leave->end_date)) : '')
            . ' (' . ucfirst((string) $leave->status) . ')');
        $pending = $requests->where('status', 'pending')->map($describe)->values()->all();
        $upcoming = $requests->where('status', 'approved')->filter(fn ($leave) => $leave->end_date && Carbon::parse($leave->end_date)->gte($now->copy()->startOfDay()))->map($describe)->values()->all();
        $recent = $requests->take(8)->map($describe)->values()->all();
        return $this->record('leaves', 'Your leave', [
            'leave_year' => $year['label'] . ' (' . $year['start']->format('d M Y') . ' to ' . $year['end']->format('d M Y') . ')',
            'balance' => $balance ? [
                'allocated_days' => (float) $balance->allocated_leaves,
                'used_days' => (float) $balance->used_leaves,
                'remaining_days' => (float) $balance->remaining_leaves,
                'sick_used' => (float) ($balance->sick_used ?? 0),
                'casual_used' => (float) ($balance->casual_used ?? 0),
            ] : 'Not available',
            'leave_types' => $types ?: 'Not configured',
            'pending_requests' => $pending ?: 'None',
            'upcoming_approved_leave' => $upcoming ?: 'None',
            'recent_requests' => $recent ?: 'None',
            'policy' => $service->policyNotice(),
            'apply_leave_page' => $this->url('leaves.create'),
            'my_leaves_page' => $this->url('leaves.index'),
        ]);
    }

    private function work(Company $company, User $user, Carbon $now): ?array
    {
        $facts = [];
        $tenant = Schema::connection('tenant');
        if ($user->canViewModule('tasks') && $tenant->hasTable('tasks') && $tenant->hasColumn('tasks', 'assigned_to')) {
            $tasks = DB::connection('tenant')->table('tasks')->where('assigned_to', $user->id)
                ->when($tenant->hasColumn('tasks', 'company_id'), fn ($q) => $q->where('company_id', $company->id))
                ->when($tenant->hasColumn('tasks', 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
                ->when($tenant->hasColumn('tasks', 'archived_at'), fn ($q) => $q->whereNull('archived_at'))
                ->orderBy('due_date')->limit(300)->get(['id', 'title', 'status', 'priority', 'due_date']);
            $open = $tasks->reject(fn ($task) => in_array(strtolower((string) $task->status), ['completed', 'complete', 'done', 'closed', 'cancelled'], true));
            $line = fn ($task) => $task->title . ($task->due_date ? ' — due ' . $this->day(Carbon::parse($task->due_date)) : '') . ' [' . ($task->status ?: 'open') . ($task->priority ? ', ' . $task->priority : '') . ']';
            $facts['my_tasks'] = [
                'total_assigned' => $tasks->count(),
                'open' => $open->count(),
                'by_status' => $tasks->groupBy(fn ($task) => $task->status ?: 'open')->map->count()->all(),
                'overdue' => $open->filter(fn ($task) => $task->due_date && Carbon::parse($task->due_date)->lt($now->copy()->startOfDay()))->take(10)->map($line)->values()->all() ?: 'None',
                'due_this_week' => $open->filter(fn ($task) => $task->due_date && Carbon::parse($task->due_date)->betweenIncluded($now->copy()->startOfWeek(), $now->copy()->endOfWeek()))->take(10)->map($line)->values()->all() ?: 'None',
                'next_open_tasks' => $open->take(10)->map($line)->values()->all() ?: 'None',
                'tasks_page' => $this->url('tasks.index'),
            ];
        }
        if ($user->canViewModule('projects') && $tenant->hasTable('projects')) {
            $projects = DB::connection('tenant')->table('projects')
                ->when($tenant->hasColumn('projects', 'company_id'), fn ($q) => $q->where('company_id', $company->id))
                ->when($tenant->hasColumn('projects', 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
                ->when($tenant->hasColumn('projects', 'archived_at'), fn ($q) => $q->whereNull('archived_at'));
            if (! WorkforceAccess::isAdmin($user)) {
                if (! $tenant->hasTable('project_user')) $projects = null;
                else $projects->whereIn('id', DB::connection('tenant')->table('project_user')->select('project_id')->where('user_id', $user->id));
            }
            if ($projects) {
                $columns = array_values(array_intersect(['id', 'name', 'status', 'deadline'], $tenant->getColumnListing('projects')));
                $rows = $projects->orderByDesc('id')->limit(15)->get($columns);
                $facts['projects'] = $rows->map(fn ($project) => ($project->name ?? 'Project') . ' [' . ($project->status ?? 'active') . ']'
                    . (! empty($project->deadline) ? ' — deadline ' . $this->day(Carbon::parse($project->deadline)) : ''))->all() ?: 'None';
            }
        }
        return $facts ? $this->record('work', 'Your tasks & projects', $facts) : null;
    }

    private function events(Company $company, User $user, Carbon $now): ?array
    {
        $tenant = Schema::connection('tenant');
        if (! $user->canViewModule('events') || ! $tenant->hasTable('events') || ! $tenant->hasColumn('events', 'company_id')) return null;
        $columns = $tenant->getColumnListing('events');
        $query = DB::connection('tenant')->table('events')->where('company_id', $company->id)
            ->whereDate('start_date', '>=', $now->copy()->subDays(1)->toDateString())
            ->whereDate('start_date', '<=', $now->copy()->addDays(60)->toDateString());
        if (in_array('deleted_at', $columns, true)) $query->whereNull('deleted_at');
        if (in_array('archived_at', $columns, true)) $query->whereNull('archived_at');
        if (! WorkforceAccess::isAdmin($user) && in_array('status', $columns, true)) $query->where('status', '!=', 'draft');
        $fields = array_values(array_intersect(['title', 'event_type', 'start_date', 'start_time', 'end_date', 'location', 'location_type', 'status'], $columns));
        $rows = $query->orderBy('start_date')->limit(15)->get($fields);
        return $this->record('events', 'Upcoming events', [
            'next_60_days' => $rows->map(fn ($event) => ($event->title ?? 'Event') . ' — ' . $this->day(Carbon::parse($event->start_date))
                . (! empty($event->start_time) ? ' at ' . Carbon::parse($event->start_time)->format('h:i A') : '')
                . (! empty($event->location) ? ' · ' . Str::limit(strip_tags((string) $event->location), 80, '') : ''))->all() ?: 'None',
            'events_page' => $this->url('events.index'),
        ]);
    }

    private function team(Company $company, User $user): array
    {
        $detail = $user->employeeDetail;
        $describe = fn (User $person) => $person->name . ($person->employeeDetail?->designation?->name ? ' (' . $person->employeeDetail->designation->name . ')' : '');
        $reports = User::with('employeeDetail.designation')->where('company_id', $company->id)->whereNull('archived_at')
            ->whereHas('employeeDetail', fn ($q) => $q->where('reporting_to', $user->id))->orderBy('name')->limit(30)->get();
        $facts = [
            'your_manager' => $detail?->reportingTo ? $describe($detail->reportingTo->loadMissing('employeeDetail.designation')) : 'Not assigned',
            'your_direct_reports' => $reports->map($describe)->all() ?: 'None',
        ];
        if ($detail?->department_id) {
            $colleagues = User::with('employeeDetail.designation')->where('company_id', $company->id)->whereNull('archived_at')->where('id', '!=', $user->id)
                ->whereHas('employeeDetail', fn ($q) => $q->where('department_id', $detail->department_id))->orderBy('name')->limit(40)->get();
            $facts['your_department_colleagues'] = $colleagues->map($describe)->all() ?: 'None';
        }
        if ($user->canViewModule('employees')) {
            $staff = User::where('company_id', $company->id)->whereIn('role', ['employee', 'hr', 'manager'])->whereNull('archived_at');
            $facts['company_headcount'] = (clone $staff)->count();
            $facts['headcount_by_department'] = (clone $staff)->with('employeeDetail.department')->get()
                ->groupBy(fn ($person) => $person->employeeDetail?->department?->name ?: 'No department')->map->count()->sortDesc()->all();
            $facts['joined_this_month'] = (clone $staff)->whereHas('employeeDetail', fn ($q) => $q->whereBetween('joining_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]))->count();
        }
        if ($user->canViewModule('organization')) $facts['organization_directory_page'] = $this->url('organization.index');
        return $this->record('team', 'Your team & organization', $facts);
    }

    private function payslips(User $user): ?array
    {
        if (! $user->canViewModule('my-payslips') || ! Schema::connection('tenant')->hasTable('payslips')) return null;
        $slips = \App\Models\Payslip::with('payroll')->where('user_id', $user->id)
            ->whereHas('payroll', fn ($q) => $q->whereIn('status', ['approved', 'finalized']))->get()
            ->sortByDesc(fn ($slip) => $slip->payroll?->period_start)->take(3)->values();
        return $this->record('payslips', 'Your payslips', [
            'latest_payslips' => $slips->map(fn ($slip) => ($slip->payroll?->period_start ? Carbon::parse($slip->payroll->period_start)->format('F Y') : 'Payslip')
                . ' — net ' . number_format((float) $slip->net_salary, 2) . ', gross ' . number_format((float) $slip->gross_salary, 2)
                . ', deductions ' . number_format((float) $slip->total_deductions, 2))->all() ?: 'No payslip published yet',
            'my_payslips_page' => $this->url('my-payslips.index'),
        ]);
    }

    /** Pages this user can open (from the sidebar registry) plus step-by-step help for common actions. */
    private function guide(User $user): array
    {
        $modules = config('workspace_modules', []);
        $pages = [];
        foreach ($modules as $slug => $module) {
            if (empty($module['route']) || ! $user->canViewModule($slug)) continue;
            $url = $this->url($module['route']);
            if (! $url) continue;
            $section = $module['parent'] ? ($modules[$module['parent']]['name'] ?? null) : null;
            $pages[] = ($section ? $section . ' › ' : '') . $module['name'] . ': ' . $url;
        }
        $howTo = [
            'leaves' => 'Apply for leave: open Apply Leave (' . $this->url('leaves.create') . '), pick the leave type and dates, add a reason, then submit. Track status in My Leaves (' . $this->url('leaves.index') . ').',
            'attendance' => 'Clock in/out: use the Clock In button on the Dashboard (' . $this->url('dashboard') . '); the camera confirms your photo. See your history in Attendance (' . $this->url('attendance.index') . ').',
            'holidays' => 'Holiday list: open Holiday List (' . $this->url('holidays.calendar') . ') to see every company holiday.',
            'my-payslips' => 'Payslips: open My Payslips (' . $this->url('my-payslips.index') . ') to view or download a payslip.',
            'my-documents' => 'Documents: upload or download your documents in My Documents (' . $this->url('my-documents.index') . ').',
            'tasks' => 'Tasks: open Tasks (' . $this->url('tasks.index') . ') to see assigned work and update its status.',
            'tickets' => 'Internal tickets: raise an issue in Internal Tickets (' . $this->url('tickets.index') . ').',
            'events' => 'Events: see and RSVP to company events in Events (' . $this->url('events.index') . ').',
            'organization' => 'Find a colleague: search the Organization directory (' . $this->url('organization.index') . ').',
        ];
        $steps = [];
        foreach ($howTo as $slug => $text) if ($user->canViewModule($slug) && ! str_contains($text, '()')) $steps[] = $text;
        if ($url = $this->url('profile.edit')) $steps[] = 'Profile & password: open your profile (' . $url . ') to update details or change your password.';
        return $this->record('guide', 'Workspace guide', [
            'pages_you_can_open' => $pages ?: 'Dashboard only',
            'how_to' => $steps ?: 'None',
        ]);
    }

    private function url(string $route): ?string
    {
        try {
            return Route::has($route) ? parse_url(route($route), PHP_URL_PATH) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}

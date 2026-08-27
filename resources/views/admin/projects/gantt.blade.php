@extends('admin.layout.app')

@section('title', 'Gantt Chart - ' . $project->name)

@section('content')
<div class="gantt-template-page">
    <div class="container-fluid px-4">

@php
    // Determine Project / Task Date Span
    $min = $project->tasks->whereNotNull('start_date')->min('start_date');
    $max = $project->tasks->whereNotNull('due_date')->max('due_date');

    $timelineStart = \Carbon\Carbon::parse($min ?? $project->start_date ?? now())->startOfWeek();
    $timelineEnd = \Carbon\Carbon::parse($max ?? $project->deadline ?? now()->addMonths(2))->endOfWeek();

    if ($timelineEnd->diffInWeeks($timelineStart) < 8) {
        $timelineEnd = $timelineStart->copy()->addWeeks(10)->endOfWeek();
    }

    $totalDays = max(1, $timelineStart->diffInDays($timelineEnd) + 1);

    // Build Weekly Columns & Month Groups
    $weeks = [];
    $curr = $timelineStart->copy();
    while ($curr->lte($timelineEnd)) {
        $weeks[] = [
            'date' => $curr->copy(),
            'day' => $curr->format('d'),
            'month' => $curr->format('M'),
            'month_year' => $curr->format('M Y'),
            'full' => $curr->format('d M Y'),
        ];
        $curr->addWeek();
    }
    $totalWeeks = count($weeks);

    // Group weeks by Month
    $months = [];
    foreach ($weeks as $w) {
        $mKey = $w['month_year'];
        if (!isset($months[$mKey])) {
            $months[$mKey] = [
                'name' => $w['month'],
                'year' => $w['date']->format('Y'),
                'count' => 0,
            ];
        }
        $months[$mKey]['count']++;
    }

    // Helper closure to calculate position percentages
    $calcPos = function($startDate, $endDate = null) use ($timelineStart, $totalDays) {
        if (!$startDate) return ['left' => 0, 'width' => 0];
        $start = \Carbon\Carbon::parse($startDate);
        $offset = max(0, $timelineStart->diffInDays($start, false));
        $left = min(99, ($offset / $totalDays) * 100);

        if (!$endDate) {
            return ['left' => $left, 'width' => 0];
        }

        $end = \Carbon\Carbon::parse($endDate);
        if ($end->lt($start)) $end = $start->copy()->addDay();
        $duration = max(1, $start->diffInDays($end) + 1);
        $width = min(100 - $left, max(1.5, ($duration / $totalDays) * 100));

        return ['left' => $left, 'width' => $width];
    };

    // Build Hierarchical Items
    $ganttItems = [];
    $hasRealData = $project->tasks->count() > 0 || $project->milestones->count() > 0;

    if ($hasRealData) {
        $groupNum = 1;
        $milestones = $project->milestones;

        if ($milestones->count() > 0) {
            foreach ($milestones as $mIndex => $milestone) {
                $mTasks = $project->tasks->where('milestone_id', $milestone->id);
                $mStart = $milestone->start_date ? \Carbon\Carbon::parse($milestone->start_date) : ($mTasks->min('start_date') ? \Carbon\Carbon::parse($mTasks->min('start_date')) : $timelineStart->copy()->addWeeks($mIndex * 2));
                $mEnd = $milestone->end_date ? \Carbon\Carbon::parse($milestone->end_date) : ($mTasks->max('due_date') ? \Carbon\Carbon::parse($mTasks->max('due_date')) : $mStart->copy()->addWeeks(3));

                $gPos = $calcPos($mStart, $mEnd);
                $ganttItems[] = [
                    'level' => 1,
                    'name' => 'Group ' . $groupNum . ': ' . $milestone->title,
                    'start_date' => $mStart,
                    'end_date' => $mEnd,
                    'visual' => 'group',
                    'type' => 'group',
                    'left' => $gPos['left'],
                    'width' => $gPos['width'],
                ];

                $taskIndex = 1;
                foreach ($mTasks->whereNull('parent_id') as $task) {
                    $tStart = $task->start_date ? \Carbon\Carbon::parse($task->start_date) : $mStart->copy()->addDays(($taskIndex - 1) * 3);
                    $tEnd = $task->due_date ? \Carbon\Carbon::parse($task->due_date) : $tStart->copy()->addDays(7);
                    $tPos = $calcPos($tStart, $tEnd);

                    $ganttItems[] = [
                        'level' => 2,
                        'name' => '- Task ' . $groupNum . '.' . $taskIndex . ': ' . $task->title,
                        'start_date' => $tStart,
                        'end_date' => $tEnd,
                        'visual' => 'task',
                        'type' => 'task',
                        'left' => $tPos['left'],
                        'width' => $tPos['width'],
                    ];

                    $subIndex = 1;
                    $subtasks = $project->tasks->where('parent_id', $task->id);
                    foreach ($subtasks as $subtask) {
                        $sStart = $subtask->start_date ? \Carbon\Carbon::parse($subtask->start_date) : $tStart->copy()->addDays($subIndex * 2);
                        $sEnd = $subtask->due_date ? \Carbon\Carbon::parse($subtask->due_date) : $sStart->copy()->addDays(4);
                        $sPos = $calcPos($sStart, $sEnd);

                        $ganttItems[] = [
                            'level' => 3,
                            'name' => '-- Task ' . $groupNum . '.' . $taskIndex . '.' . $subIndex . ': ' . $subtask->title,
                            'start_date' => $sStart,
                            'end_date' => $sEnd,
                            'visual' => 'task',
                            'type' => 'subtask',
                            'left' => $sPos['left'],
                            'width' => $sPos['width'],
                        ];
                        $subIndex++;
                    }
                    $taskIndex++;
                }

                // Milestone marker
                $mMarkerDate = $mEnd->copy();
                $mPos = $calcPos($mMarkerDate);
                $ganttItems[] = [
                    'level' => 2,
                    'name' => '- Milestone ' . $groupNum . '.' . $taskIndex . ': ' . $milestone->title . ' Completion',
                    'start_date' => $mMarkerDate,
                    'end_date' => null,
                    'visual' => 'milestone',
                    'type' => 'milestone',
                    'left' => $mPos['left'],
                    'width' => 0,
                ];

                $groupNum++;
            }
        }

        // Tasks not assigned to any milestone
        $unassigned = $project->tasks->whereNull('milestone_id')->whereNull('parent_id');
        if ($unassigned->count() > 0) {
            $uStart = $unassigned->min('start_date') ? \Carbon\Carbon::parse($unassigned->min('start_date')) : $timelineStart->copy()->addWeeks(2);
            $uEnd = $unassigned->max('due_date') ? \Carbon\Carbon::parse($unassigned->max('due_date')) : $uStart->copy()->addWeeks(3);
            $uPos = $calcPos($uStart, $uEnd);

            $ganttItems[] = [
                'level' => 1,
                'name' => 'Group ' . $groupNum . ': Deliverables & Tasks',
                'start_date' => $uStart,
                'end_date' => $uEnd,
                'visual' => 'group',
                'type' => 'group',
                'left' => $uPos['left'],
                'width' => $uPos['width'],
            ];

            $uTaskIdx = 1;
            foreach ($unassigned as $task) {
                $tStart = $task->start_date ? \Carbon\Carbon::parse($task->start_date) : $uStart->copy()->addDays(($uTaskIdx - 1) * 3);
                $tEnd = $task->due_date ? \Carbon\Carbon::parse($task->due_date) : $tStart->copy()->addDays(7);
                $tPos = $calcPos($tStart, $tEnd);

                $ganttItems[] = [
                    'level' => 2,
                    'name' => '- Task ' . $groupNum . '.' . $uTaskIdx . ': ' . $task->title,
                    'start_date' => $tStart,
                    'end_date' => $tEnd,
                    'visual' => 'task',
                    'type' => 'task',
                    'left' => $tPos['left'],
                    'width' => $tPos['width'],
                ];
                $uTaskIdx++;
            }
        }
    } else {
        // High fidelity sample data matching the exact template layout
        $s = $timelineStart->copy();
        $sampleStructure = [
            ['level' => 1, 'name' => 'Group 1', 's_days' => 0, 'e_days' => 23, 'visual' => 'group', 'type' => 'group'],
            ['level' => 2, 'name' => '- Task 1.1', 's_days' => 0, 'e_days' => 19, 'visual' => 'task', 'type' => 'task'],
            ['level' => 2, 'name' => '- Milestone 1.2', 's_days' => 23, 'e_days' => null, 'visual' => 'milestone', 'type' => 'milestone'],
            ['level' => 1, 'name' => 'Group 2', 's_days' => 8, 'e_days' => 37, 'visual' => 'group', 'type' => 'group'],
            ['level' => 2, 'name' => '- Task 2.1', 's_days' => 8, 'e_days' => 26, 'visual' => 'task', 'type' => 'task'],
            ['level' => 2, 'name' => '- Group 2.2', 's_days' => 16, 'e_days' => 33, 'visual' => 'group', 'type' => 'group'],
            ['level' => 3, 'name' => '-- Task 2.2.1', 's_days' => 16, 'e_days' => 27, 'visual' => 'task', 'type' => 'subtask'],
            ['level' => 3, 'name' => '-- Task 2.2.2', 's_days' => 23, 'e_days' => 33, 'visual' => 'task', 'type' => 'subtask'],
            ['level' => 2, 'name' => '- Task 2.3', 's_days' => 34, 'e_days' => 56, 'visual' => 'task', 'type' => 'task'],
            ['level' => 2, 'name' => '- Milestone 2.4', 's_days' => 56, 'e_days' => null, 'visual' => 'milestone', 'type' => 'milestone'],
            ['level' => 1, 'name' => 'Group 3', 's_days' => 40, 'e_days' => 73, 'visual' => 'group', 'type' => 'group'],
            ['level' => 2, 'name' => '- Task 3.1', 's_days' => 40, 'e_days' => 51, 'visual' => 'task', 'type' => 'task'],
            ['level' => 2, 'name' => '- Task 3.2', 's_days' => 52, 'e_days' => 72, 'visual' => 'task', 'type' => 'task'],
            ['level' => 2, 'name' => '- Task 3.3', 's_days' => 58, 'e_days' => 73, 'visual' => 'task', 'type' => 'task'],
        ];

        foreach ($sampleStructure as $item) {
            $startDate = $s->copy()->addDays($item['s_days']);
            $endDate = $item['e_days'] !== null ? $s->copy()->addDays($item['e_days']) : null;
            $pos = $calcPos($startDate, $endDate);

            $ganttItems[] = [
                'level' => $item['level'],
                'name' => $item['name'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'visual' => $item['visual'],
                'type' => $item['type'],
                'left' => $pos['left'],
                'width' => $pos['width'],
            ];
        }
    }
@endphp

        {{-- Standardized Project Header & Scrollable 13-Tab Navigation --}}
        @include('admin.projects.partials.header', [
            'project' => $project,
            'activeTab' => 'gantt'
        ])

        <!-- Gantt Template Card -->
        <div class="gantt-template-card mb-5">
            {{-- Template Header --}}
            <div class="gantt-template-header">
                <div class="header-branding">
                    <div class="brand-badge">
                        <span>{{ $project->project_code ? Str::limit($project->project_code, 5, '') : 'Shift' }}</span>
                    </div>
                    <h2 class="template-title">Gantt Template</h2>
                </div>

                <div class="header-actions">
                    @if(in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr', 'manager'], true))
                        <a href="{{ route('tasks.create', ['project_id' => $project->id]) }}" class="btn btn-sm btn-primary-custom">
                            <i class="fas fa-plus me-1"></i> Add Task
                        </a>
                    @endif
                    <button type="button" class="btn btn-sm btn-outline-custom" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-custom" id="ganttFullscreenBtn">
                        <i class="fas fa-expand me-1"></i> Fullscreen
                    </button>
                </div>
            </div>

            {{-- Table + Gantt Matrix Grid Container --}}
            <div class="gantt-matrix-wrapper">
                <table class="gantt-matrix-table">
                    <thead>
                        {{-- Row 1: Left Table Headers + Month Groupings --}}
                        <tr class="header-row-months">
                            <th class="th-level" rowspan="2">Level</th>
                            <th class="th-task" rowspan="2">Task</th>
                            <th class="th-start" rowspan="2">Start Date</th>
                            <th class="th-end" rowspan="2">End Date</th>
                            <th class="th-visual" rowspan="2">Visual</th>

                            @php
                                $monthColors = ['month-soft', 'month-medium', 'month-dark'];
                                $mIndex = 0;
                            @endphp
                            @foreach($months as $mKey => $m)
                                <th colspan="{{ $m['count'] }}" class="th-month {{ $monthColors[$mIndex % 3] }}">
                                    {{ $m['name'] }}
                                </th>
                                @php $mIndex++; @endphp
                            @endforeach
                        </tr>

                        {{-- Row 2: Day Numbers --}}
                        <tr class="header-row-days">
                            @foreach($weeks as $w)
                                <th class="th-day">{{ $w['day'] }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($ganttItems as $item)
                            <tr class="matrix-row level-{{ $item['level'] }} type-{{ $item['type'] }}">
                                {{-- Level --}}
                                <td class="td-level">{{ $item['level'] }}</td>

                                {{-- Task Name with Hierarchy --}}
                                <td class="td-task level-indent-{{ $item['level'] }}">
                                    <span class="task-title-text" title="{{ $item['name'] }}">{{ $item['name'] }}</span>
                                </td>

                                {{-- Start Date --}}
                                <td class="td-start">{{ $item['start_date']->format('d/m/Y') }}</td>

                                {{-- End Date --}}
                                <td class="td-end">{{ $item['end_date'] ? $item['end_date']->format('d/m/Y') : '' }}</td>

                                {{-- Visual Icon --}}
                                <td class="td-visual">
                                    @if($item['visual'] === 'group')
                                        <span class="visual-icon icon-group" title="Group Summary"></span>
                                    @elseif($item['visual'] === 'task')
                                        <span class="visual-icon icon-task" title="Task Item"></span>
                                    @elseif($item['visual'] === 'milestone')
                                        <span class="visual-icon icon-milestone" title="Milestone Target"></span>
                                    @endif
                                </td>

                                {{-- Timeline Bar Area (spanning all week columns) --}}
                                <td colspan="{{ $totalWeeks }}" class="td-timeline">
                                    <div class="timeline-lane" style="--total-cols: {{ $totalWeeks }};">
                                        {{-- Background Vertical Grid Lines --}}
                                        <div class="timeline-grid-lines">
                                            @for($i = 0; $i < $totalWeeks; $i++)
                                                <div class="grid-col-line"></div>
                                            @endfor
                                        </div>

                                        {{-- Render Bars / Markers --}}
                                        @if($item['type'] === 'group')
                                            <div class="gantt-bar-item bar-group" 
                                                 style="left: {{ $item['left'] }}%; width: {{ $item['width'] }}%;"
                                                 data-bs-toggle="tooltip" 
                                                 title="{{ $item['name'] }} ({{ $item['start_date']->format('d M') }} - {{ $item['end_date']->format('d M Y') }})">
                                            </div>
                                        @elseif($item['type'] === 'task' || $item['type'] === 'subtask')
                                            <div class="gantt-bar-item bar-task" 
                                                 style="left: {{ $item['left'] }}%; width: {{ $item['width'] }}%;"
                                                 data-bs-toggle="tooltip" 
                                                 title="{{ $item['name'] }} ({{ $item['start_date']->format('d M') }} - {{ $item['end_date']->format('d M Y') }})">
                                            </div>
                                        @elseif($item['type'] === 'milestone')
                                            <div class="gantt-diamond-marker" 
                                                 style="left: {{ $item['left'] }}%;"
                                                 data-bs-toggle="tooltip" 
                                                 title="{{ $item['name'] }} ({{ $item['start_date']->format('d M Y') }})">
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<style>
    /* ===== GANTT TEMPLATE EXACT REPLICA STYLING ===== */
    .gantt-template-page {
        background: #f8fafc;
        min-height: 100vh;
        padding-bottom: 40px;
    }

    /* Template Card */
    .gantt-template-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        border: 1px solid #e2e8f0;
        padding: 30px 36px 40px;
    }

    /* Template Header */
    .gantt-template-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .header-branding {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .brand-badge {
        background: #0f172a;
        color: #ffffff;
        font-weight: 800;
        font-size: 1.15rem;
        padding: 8px 18px 8px 14px;
        border-radius: 12px 4px 12px 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        letter-spacing: -0.02em;
    }

    .template-title {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.03em;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .btn-primary-custom {
        background: #0f744c;
        color: #ffffff;
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 14px;
        border: none;
        transition: all 0.2s;
    }
    .btn-primary-custom:hover {
        background: #0a5638;
        color: #ffffff;
    }

    .btn-outline-custom {
        background: #ffffff;
        color: #475569;
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 12px;
        border: 1px solid #cbd5e1;
        transition: all 0.2s;
    }
    .btn-outline-custom:hover {
        background: #f1f5f9;
        color: #0f172a;
    }

    /* Gantt Matrix Table */
    .gantt-matrix-wrapper {
        overflow-x: auto;
        border-radius: 6px;
        background: #ffffff;
    }

    .gantt-matrix-table {
        width: 100%;
        border-collapse: collapse;
        font-family: inherit;
    }

    /* Headers */
    .gantt-matrix-table thead th {
        vertical-align: middle;
        text-align: center;
        font-weight: 600;
        font-size: 0.82rem;
        padding: 8px 6px;
    }

    .th-level {
        width: 60px;
        background: #e2e8f0;
        color: #334155;
        border-right: 1px solid #cbd5e1;
    }

    .th-task {
        min-width: 220px;
        text-align: left !important;
        padding-left: 16px !important;
        background: #e2e8f0;
        color: #334155;
        border-right: 1px solid #cbd5e1;
    }

    .th-start, .th-end {
        width: 110px;
        background: #e2e8f0;
        color: #334155;
        border-right: 1px solid #cbd5e1;
    }

    .th-visual {
        width: 55px;
        background: #e2e8f0;
        color: #334155;
        border-right: 2px solid #94a3b8;
    }

    /* Month Headers */
    .th-month {
        font-size: 0.82rem;
        font-weight: 700;
        padding: 7px 4px !important;
        border-bottom: 1px solid #cbd5e1;
        border-right: 1px solid rgba(255, 255, 255, 0.3);
    }

    .month-soft {
        background: #c7d2fe;
        color: #312e81;
    }

    .month-medium {
        background: #818cf8;
        color: #ffffff;
    }

    .month-dark {
        background: #6366f1;
        color: #ffffff;
    }

    /* Day Headers */
    .header-row-days th {
        background: #e0e7ff;
        color: #1e293b;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 5px 2px !important;
        border-bottom: 2px solid #cbd5e1;
        border-right: 1px solid #c7d2fe;
        min-width: 44px;
    }

    /* Table Rows */
    .matrix-row {
        height: 38px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.15s ease;
    }

    .matrix-row:hover {
        background: #f8fafc;
    }

    .td-level {
        text-align: center;
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        border-right: 1px solid #f1f5f9;
    }

    .td-task {
        font-size: 0.85rem;
        color: #0f172a;
        border-right: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    .matrix-row.level-1 .td-task {
        font-weight: 700;
        color: #0f172a;
    }

    .matrix-row.level-2 .td-task {
        font-weight: 500;
        color: #334155;
    }

    .matrix-row.level-3 .td-task {
        font-weight: 400;
        color: #64748b;
    }

    .level-indent-1 { padding-left: 16px; }
    .level-indent-2 { padding-left: 28px; }
    .level-indent-3 { padding-left: 42px; }

    .td-start, .td-end {
        text-align: center;
        font-size: 0.82rem;
        color: #475569;
        border-right: 1px solid #f1f5f9;
        white-space: nowrap;
    }

    .td-visual {
        text-align: center;
        border-right: 2px solid #cbd5e1;
    }

    /* Visual Icons */
    .visual-icon {
        display: inline-block;
        vertical-align: middle;
    }

    /* Group: square outline */
    .icon-group {
        width: 10px;
        height: 10px;
        border: 2px solid #0f172a;
        background: transparent;
        border-radius: 1px;
    }

    /* Task: filled dark square */
    .icon-task {
        width: 9px;
        height: 9px;
        background: #0f172a;
        border-radius: 1px;
    }

    /* Milestone: diamond */
    .icon-milestone {
        width: 9px;
        height: 9px;
        background: #0f172a;
        transform: rotate(45deg);
    }

    /* Timeline Lane */
    .td-timeline {
        padding: 0 !important;
        position: relative;
        vertical-align: middle;
        background: #ffffff;
    }

    .timeline-lane {
        position: relative;
        height: 38px;
        width: 100%;
        display: flex;
        align-items: center;
    }

    .timeline-grid-lines {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        pointer-events: none;
    }

    .grid-col-line {
        flex: 1;
        border-right: 1px solid #f1f5f9;
        height: 100%;
    }

    /* Gantt Bars */
    .gantt-bar-item {
        position: absolute;
        border-radius: 2px;
        cursor: pointer;
        transition: transform 0.15s ease, filter 0.15s ease;
        z-index: 2;
    }

    .gantt-bar-item:hover {
        transform: scaleY(1.1);
        filter: brightness(1.05);
        z-index: 10;
    }

    /* Level 1 Group Bar: Dark Forest / Pine Green */
    .bar-group {
        background: #1b6b55;
        height: 22px;
    }

    /* Level 2 & 3 Task Bar: Bright Mint / Emerald Green */
    .bar-task {
        background: #38d9a9;
        height: 20px;
    }

    /* Milestone Marker: Golden Yellow Diamond */
    .gantt-diamond-marker {
        position: absolute;
        width: 11px;
        height: 11px;
        background: #f59e0b;
        transform: translate(-50%, 0) rotate(45deg);
        cursor: pointer;
        z-index: 5;
        box-shadow: 0 0 4px rgba(245, 158, 11, 0.6);
        transition: transform 0.15s ease;
    }

    .gantt-diamond-marker:hover {
        transform: translate(-50%, 0) rotate(45deg) scale(1.3);
    }

    /* Fullscreen Mode */
    .gantt-template-card.is-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
        border-radius: 0 !important;
        margin: 0 !important;
        overflow: auto !important;
    }
</style>

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Fullscreen Toggle
    const fsBtn = document.getElementById("ganttFullscreenBtn");
    const card = document.querySelector(".gantt-template-card");

    if (fsBtn && card) {
        fsBtn.addEventListener("click", function () {
            card.classList.toggle("is-fullscreen");
            const isFull = card.classList.contains("is-fullscreen");
            this.innerHTML = isFull ? '<i class="fas fa-compress me-1"></i> Exit' : '<i class="fas fa-expand me-1"></i> Fullscreen';
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && card.classList.contains("is-fullscreen")) {
                card.classList.remove("is-fullscreen");
                fsBtn.innerHTML = '<i class="fas fa-expand me-1"></i> Fullscreen';
            }
        });
    }
});
</script>
@endpush
@endsection

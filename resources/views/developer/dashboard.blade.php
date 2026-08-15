@extends('layouts.developer')

@section('title', 'Developer Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- GREETING & HEADER -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; padding: 28px 32px; border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; box-shadow: var(--shadow-md);">
        <div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                <h1 style="font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">Good morning, {{ $dev->name }} 👋</h1>
                <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 20px; border: 1px solid rgba(16, 185, 129, 0.3);">
                    ● {{ ucfirst($empDetail?->status ?? 'Available') }}
                </span>
            </div>
            <p style="color: #94a3b8; font-size: 14px; font-weight: 500;">Here's an overview of your current development work across tenant platforms.</p>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('developer.my-work') }}" class="btn-primary" style="background: #10b981; color: #ffffff; padding: 10px 18px; border-radius: var(--radius-md); text-decoration: none; font-size: 13px; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                <i class="bx bx-task" style="font-size: 18px;"></i> View My Work
            </a>
        </div>
    </div>

    <!-- WORKLOAD ALERT BANNER IF HIGH -->
    @if($kpis['workload_percentage'] >= 80)
        <div style="background: #fffbe6; border: 1px solid #ffe58f; padding: 16px 20px; border-radius: var(--radius-lg); color: #873800; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-sm);">
            <i class="bx bx-error-circle" style="font-size: 24px; color: #fa8c16;"></i>
            <div>
                <strong style="font-size: 14px; display: block;">Your current workload is high ({{ $kpis['workload_percentage'] }}%)</strong>
                <span style="font-size: 13px;">You have {{ $kpis['estimate_hours_total'] }} estimated hours assigned across active tasks. Available capacity is {{ $kpis['available_capacity'] }} hours.</span>
            </div>
        </div>
    @endif

    <!-- KPI CARDS GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px;">
        <!-- TOTAL ASSIGNED -->
        <a href="{{ route('developer.my-work') }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px; border-left: 4px solid #3b82f6; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11.5px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.5px;">TOTAL ASSIGNED</span>
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bx bx-list-check"></i>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: var(--slate-dark);">{{ $kpis['total_assigned'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Active & Past Tasks</span>
            </div>
        </a>

        <!-- IN PROGRESS -->
        <a href="{{ route('developer.my-work', ['status' => 'in_progress']) }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px; border-left: 4px solid #2563eb; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11.5px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.5px;">IN PROGRESS</span>
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bx bx-loader-alt bx-spin"></i>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #2563eb;">{{ $kpis['in_progress'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Currently Active</span>
            </div>
        </a>

        <!-- COMPLETED -->
        <a href="{{ route('developer.my-contributions') }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px; border-left: 4px solid #10b981; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11.5px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.5px;">COMPLETED</span>
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #059669;">{{ $kpis['completed'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Finished Work</span>
            </div>
        </a>

        <!-- OVERDUE -->
        <a href="{{ route('developer.deadlines') }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px; border-left: 4px solid #ef4444; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11.5px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.5px;">OVERDUE</span>
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bx bx-error"></i>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #dc2626;">{{ $kpis['overdue'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Passed Deadline</span>
            </div>
        </a>

        <!-- UPCOMING DEADLINES -->
        <a href="{{ route('developer.deadlines') }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px; border-left: 4px solid #f59e0b; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                    <span style="font-size: 11.5px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.5px;">UPCOMING DEADLINES</span>
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #fffbe6; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bx bx-time"></i>
                    </div>
                </div>
                <div style="font-size: 28px; font-weight: 800; color: #d97706;">{{ $kpis['upcoming_deadlines'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Next 7 Days</span>
            </div>
        </a>

        <!-- WORKLOAD -->
        <div class="dev-card" style="padding: 20px; border-left: 4px solid #8b5cf6;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-size: 11.5px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.5px;">WORKLOAD</span>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f3e8ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bx bx-pie-chart-alt-2"></i>
                </div>
            </div>
            <div style="font-size: 28px; font-weight: 800; color: #7c3aed;">{{ $kpis['workload_percentage'] }}%</div>
            <div style="width: 100%; height: 6px; background: #f3e8ff; border-radius: 3px; margin-top: 6px; overflow: hidden;">
                <div style="width: {{ $kpis['workload_percentage'] }}%; height: 100%; background: #7c3aed; border-radius: 3px;"></div>
            </div>
        </div>
    </div>

    <!-- MAIN DASHBOARD CONTENT GRID -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">

        <!-- LEFT COLUMN: RECENT ASSIGNED WORK -->
        <div class="dev-card" style="margin-bottom: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid var(--border-color);">
                <div>
                    <h2 style="font-size: 16px; font-weight: 700; color: var(--slate-dark);">Recent Work Assigned</h2>
                    <span style="font-size: 12px; color: var(--slate-muted);">Tasks assigned to you by Super Admin</span>
                </div>
                <a href="{{ route('developer.my-work') }}" style="font-size: 12.5px; font-weight: 700; color: var(--primary); text-decoration: none;">View All Work &rarr;</a>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 10px 12px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase;">Task</th>
                            <th style="padding: 10px 12px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase;">Company & Project</th>
                            <th style="padding: 10px 12px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase;">Priority</th>
                            <th style="padding: 10px 12px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase;">Status</th>
                            <th style="padding: 10px 12px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase;">Deadline</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWork as $task)
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 12px;">
                                <strong style="font-size: 13px; color: var(--slate-dark); display: block;">{{ $task->title }}</strong>
                                <span style="font-size: 11px; color: var(--slate-muted);">ID: TSK-{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td style="padding: 12px;">
                                <span style="font-size: 12.5px; font-weight: 600; color: var(--slate-dark); display: block;">{{ $task->company_name ?? 'Central Platform' }}</span>
                                <span style="font-size: 11px; color: var(--slate-muted);">{{ $task->project_name ?? 'General Work' }}</span>
                            </td>
                            <td style="padding: 12px;">
                                <span style="font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; background: #f1f5f9; color: #475569;">
                                    {{ strtoupper($task->priority ?? 'MEDIUM') }}
                                </span>
                            </td>
                            <td style="padding: 12px;">
                                <form method="POST" action="{{ route('developer.tasks.status', $task->id) }}" style="margin: 0;">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 6px; font-size: 11.5px; font-weight: 700; border: 1px solid var(--border-color); cursor: pointer; background: #ffffff;">
                                        <option value="assigned" {{ $task->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="on_hold" {{ $task->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                        <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                            </td>
                            <td style="padding: 12px; font-size: 12px; color: var(--slate-muted); font-weight: 600;">
                                {{ !empty($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'No Deadline' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="padding: 24px; text-align: center; color: var(--slate-muted); font-size: 13px;">
                                No work assigned yet. Enjoy your clear queue!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT COLUMN: DEVELOPER PROFILE SUMMARY CARD -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="dev-card" style="margin-bottom: 0;">
                <div style="text-align: center; padding-bottom: 16px; border-bottom: 1px solid var(--border-color); margin-bottom: 16px;">
                    <div style="width: 70px; height: 70px; border-radius: 50%; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; margin: 0 auto 12px; border: 3px solid var(--primary-border);">
                        @if(!empty($dev->profile_image) && file_exists(public_path($dev->profile_image)))
                            <img src="{{ asset($dev->profile_image) }}" alt="{{ $dev->name }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        @else
                            {{ strtoupper(substr($dev->name ?? 'Dev', 0, 2)) }}
                        @endif
                    </div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-dark);">{{ $dev->name }}</h3>
                    <span style="font-size: 12px; font-weight: 600; color: var(--primary);">{{ ucfirst($dev->designation ?? 'Backend Developer') }}</span>
                    <div style="margin-top: 6px;">
                        <span style="font-size: 11px; font-family: monospace; background: #f1f5f9; padding: 3px 8px; border-radius: 4px; color: var(--slate-muted);">DEV-{{ str_pad($dev->id, 3, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12.5px;">
                    <div style="display: flex; justify-content: space-between; color: var(--slate-muted);">
                        <span>Email:</span>
                        <strong style="color: var(--slate-dark);">{{ $dev->email }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--slate-muted);">
                        <span>Status:</span>
                        <strong style="color: #059669;">● {{ ucfirst($empDetail?->status ?? 'Available') }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--slate-muted);">
                        <span>Experience:</span>
                        <strong style="color: var(--slate-dark);">3+ Years</strong>
                    </div>
                </div>

                <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                    <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; display: block; margin-bottom: 8px;">SKILLS & TECH STACK</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        @foreach($skillsArray as $skill)
                            <span style="background: var(--primary-light); color: var(--primary); font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 12px; border: 1px solid var(--primary-border);">
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- RECENT CONTRIBUTIONS PREVIEW -->
            <div class="dev-card" style="margin-bottom: 0;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <strong style="font-size: 14px; color: var(--slate-dark);">Recent Contributions</strong>
                    <a href="{{ route('developer.my-contributions') }}" style="font-size: 11.5px; color: var(--primary); font-weight: 700; text-decoration: none;">View All</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @forelse($recentContributions as $c)
                    <div style="display: flex; align-items: center; gap: 10px; padding: 8px; background: #f8fafc; border-radius: 8px;">
                        <i class="bx bx-check-circle" style="color: #059669; font-size: 18px;"></i>
                        <div style="min-width: 0; flex: 1;">
                            <strong style="font-size: 12.5px; color: var(--slate-dark); display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $c->title }}</strong>
                            <span style="font-size: 11px; color: var(--slate-muted);">Completed {{ \Carbon\Carbon::parse($c->updated_at)->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty
                    <span style="font-size: 12px; color: var(--slate-muted);">No completed tasks yet. Completed work automatically updates here.</span>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection

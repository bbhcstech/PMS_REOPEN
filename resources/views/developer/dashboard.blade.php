@extends('layouts.developer')

@section('title', 'Developer Dashboard')
@section('page_title', 'Dashboard Overview')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- GREETING & HERO BANNER -->
    <div style="background: linear-gradient(135deg, #059669 0%, #047857 50%, #065f46 100%); color: #ffffff; padding: 30px 38px; border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px; box-shadow: var(--shadow-md); position: relative; overflow: hidden;">
        <!-- Subtle decorative glow element behind hero -->
        <div style="position: absolute; top: -50px; right: -50px; width: 240px; height: 240px; background: radial-gradient(circle, rgba(255,255,255,0.18) 0%, rgba(0,0,0,0) 70%); pointer-events: none;"></div>

        <div style="position: relative; z-index: 1;">
            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 8px; flex-wrap: wrap;">
                <h1 style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: #ffffff;">Good morning, {{ $dev->name }} 👋</h1>
                <span style="background: rgba(255, 255, 255, 0.2); color: #ffffff; font-size: 12px; font-weight: 800; padding: 4px 14px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.35); display: inline-flex; align-items: center; gap: 6px; backdrop-filter: blur(4px);">
                    <span style="width: 7px; height: 7px; border-radius: 50%; background: #34d399; display: inline-block;"></span>
                    {{ ucfirst($empDetail?->status ?? 'Available') }}
                </span>
            </div>
            <p style="color: #a7f3d0; font-size: 14.5px; font-weight: 500;">Here's an overview of your current development work across tenant platforms.</p>
        </div>

        <div style="position: relative; z-index: 1; display: flex; align-items: center; gap: 12px;">
            <a href="{{ route('developer.my-work') }}" style="background: #ffffff; color: #047857; padding: 11px 22px; border-radius: var(--radius-md); text-decoration: none; font-size: 13.5px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15); transition: all 0.2s ease;" onmouseover="this.style.background='#f0fdf4'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#ffffff'; this.style.transform='translateY(0)';">
                <i class="bx bx-check-square" style="font-size: 19px; color: #059669;"></i> View My Work
            </a>
        </div>
    </div>

    <!-- WORKLOAD ALERT BANNER IF HIGH -->
    @if($kpis['workload_percentage'] >= 80)
        <div style="background: #fffbe6; border: 1px solid #ffe58f; padding: 16px 22px; border-radius: var(--radius-lg); color: #873800; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-xs);">
            <i class="bx bx-error-circle" style="font-size: 26px; color: #fa8c16; flex-shrink: 0;"></i>
            <div>
                <strong style="font-size: 14.5px; display: block; font-weight: 700;">Your current workload is high ({{ $kpis['workload_percentage'] }}%)</strong>
                <span style="font-size: 13px; font-weight: 500;">You have {{ $kpis['estimate_hours_total'] }} estimated hours assigned across active tasks. Available capacity is {{ $kpis['available_capacity'] }} hours.</span>
            </div>
        </div>
    @endif

    <!-- 6 KPI CARDS GRID -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 16px;">
        <!-- TOTAL ASSIGNED -->
        <a href="{{ route('developer.my-work') }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px 18px; border-left: 4px solid #3b82f6; margin-bottom: 0; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-xs)';">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">TOTAL ASSIGNED</span>
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0;">
                        <i class="bx bx-slider-alt"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 900; color: var(--slate-heading); line-height: 1; margin-bottom: 6px;">{{ $kpis['total_assigned'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Active & Past Tasks</span>
            </div>
        </a>

        <!-- IN PROGRESS -->
        <a href="{{ route('developer.my-work', ['status' => 'in_progress']) }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px 18px; border-left: 4px solid #2563eb; margin-bottom: 0; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-xs)';">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">IN PROGRESS</span>
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0;">
                        <i class="bx bx-loader-alt bx-spin"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 900; color: #2563eb; line-height: 1; margin-bottom: 6px;">{{ $kpis['in_progress'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Currently Active</span>
            </div>
        </a>

        <!-- COMPLETED -->
        <a href="{{ route('developer.my-contributions') }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px 18px; border-left: 4px solid #10b981; margin-bottom: 0; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-xs)';">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">COMPLETED</span>
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0;">
                        <i class="bx bx-check-circle"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 900; color: #059669; line-height: 1; margin-bottom: 6px;">{{ $kpis['completed'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Finished Work</span>
            </div>
        </a>

        <!-- OVERDUE -->
        <a href="{{ route('developer.deadlines') }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px 18px; border-left: 4px solid #ef4444; margin-bottom: 0; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-xs)';">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">OVERDUE</span>
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #fef2f2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0;">
                        <i class="bx bx-error-alt"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 900; color: #dc2626; line-height: 1; margin-bottom: 6px;">{{ $kpis['overdue'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Passed Deadline</span>
            </div>
        </a>

        <!-- UPCOMING DEADLINES -->
        <a href="{{ route('developer.deadlines') }}" style="text-decoration: none; color: inherit;">
            <div class="dev-card" style="padding: 20px 18px; border-left: 4px solid #f59e0b; margin-bottom: 0; transition: transform 0.2s ease, box-shadow 0.2s ease;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--shadow-md)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='var(--shadow-xs)';">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">UPCOMING DEADLINES</span>
                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #fffbe6; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0;">
                        <i class="bx bx-time-five"></i>
                    </div>
                </div>
                <div style="font-size: 32px; font-weight: 900; color: #d97706; line-height: 1; margin-bottom: 6px;">{{ $kpis['upcoming_deadlines'] }}</div>
                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 600;">Next 7 Days</span>
            </div>
        </a>

        <!-- WORKLOAD -->
        <div class="dev-card" style="padding: 20px 18px; border-left: 4px solid #8b5cf6; margin-bottom: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px;">WORKLOAD</span>
                <div style="width: 34px; height: 34px; border-radius: 50%; background: #f3e8ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 19px; flex-shrink: 0;">
                    <i class="bx bx-time"></i>
                </div>
            </div>
            <div style="font-size: 32px; font-weight: 900; color: #7c3aed; line-height: 1; margin-bottom: 8px;">{{ $kpis['workload_percentage'] }}%</div>
            <div style="width: 100%; height: 7px; background: #f3e8ff; border-radius: 4px; overflow: hidden;">
                <div style="width: {{ min(100, $kpis['workload_percentage']) }}%; height: 100%; background: #7c3aed; border-radius: 4px;"></div>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT GRID -->
    <div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 24px;">

        <!-- LEFT COLUMN: RECENT ASSIGNED WORK -->
        <div class="dev-card" style="margin-bottom: 0; padding: 26px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
                <div>
                    <h2 style="font-size: 18px; font-weight: 800; color: var(--slate-heading);">Recent Work Assigned</h2>
                    <span style="font-size: 12.5px; color: var(--slate-muted); font-weight: 500;">Tasks assigned to you by Super Admin</span>
                </div>
                <a href="{{ route('developer.my-work') }}" style="font-size: 13px; font-weight: 700; color: var(--primary); text-decoration: none; display: flex; align-items: center; gap: 4px;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                    View All Work &rarr;
                </a>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: separate; border-spacing: 0; text-align: left;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="padding: 12px 14px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-color); border-radius: 6px 0 0 6px;">TASK</th>
                            <th style="padding: 12px 14px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-color);">COMPANY & PROJECT</th>
                            <th style="padding: 12px 14px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-color);">PRIORITY</th>
                            <th style="padding: 12px 14px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-color);">STATUS</th>
                            <th style="padding: 12px 14px; font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; border-bottom: 1px solid var(--border-color); border-radius: 0 6px 6px 0;">DEADLINE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWork as $task)
                        <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 16px 14px; border-bottom: 1px solid var(--border-color);">
                                <strong style="font-size: 14px; font-weight: 800; color: var(--slate-heading); display: block; margin-bottom: 2px;">{{ $task->title }}</strong>
                                <span style="font-size: 11px; font-weight: 600; color: var(--slate-muted); font-family: monospace;">ID: TSK-{{ str_pad($task->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td style="padding: 16px 14px; border-bottom: 1px solid var(--border-color);">
                                <span style="font-size: 13.5px; font-weight: 700; color: var(--slate-heading); display: block;">{{ $task->company_name ?? 'Central Platform' }}</span>
                                <span style="font-size: 11.5px; color: var(--slate-muted); font-weight: 500;">{{ $task->project_name ?? 'General Work' }}</span>
                            </td>
                            <td style="padding: 16px 14px; border-bottom: 1px solid var(--border-color);">
                                <span style="font-size: 10.5px; font-weight: 800; padding: 3px 10px; border-radius: 4px; text-transform: uppercase; background: #f1f5f9; color: #475569; letter-spacing: 0.4px;">
                                    {{ strtoupper($task->priority ?? 'MEDIUM') }}
                                </span>
                            </td>
                            <td style="padding: 16px 14px; border-bottom: 1px solid var(--border-color);">
                                <form method="POST" action="{{ route('developer.tasks.status', $task->id) }}" style="margin: 0;">
                                    @csrf
                                    <select name="status" onchange="this.form.submit()" style="padding: 6px 10px; border-radius: 8px; font-size: 12px; font-weight: 700; border: 1px solid var(--border-color); cursor: pointer; background: #ffffff; color: var(--slate-heading); box-shadow: var(--shadow-xs);">
                                        <option value="assigned" {{ $task->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                                        <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="on_hold" {{ $task->status === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                        <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                </form>
                            </td>
                            <td style="padding: 16px 14px; font-size: 12.5px; color: var(--slate-muted); font-weight: 600; border-bottom: 1px solid var(--border-color);">
                                {{ !empty($task->due_date) ? \Carbon\Carbon::parse($task->due_date)->format('Aug d, Y') : 'No Deadline' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="padding: 32px 14px; text-align: center; color: var(--slate-muted); font-size: 13.5px;">
                                <i class="bx bx-check-double" style="font-size: 28px; display: block; margin-bottom: 6px; color: #10b981;"></i>
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
            <div class="dev-card" style="margin-bottom: 0; padding: 26px;">
                <div style="text-align: center; padding-bottom: 18px; border-bottom: 1px solid var(--border-color); margin-bottom: 18px;">
                    <div style="width: 76px; height: 76px; border-radius: 50%; background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800; margin: 0 auto 14px; border: 3px solid #ffffff; box-shadow: var(--shadow-md);">
                        @if(!empty($dev->profile_image) && file_exists(public_path($dev->profile_image)))
                            <img src="{{ asset($dev->profile_image) }}" alt="{{ $dev->name }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        @else
                            {{ strtoupper(substr($dev->name ?? 'Dev', 0, 2)) }}
                        @endif
                    </div>
                    <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-heading); line-height: 1.2;">{{ $dev->name }}</h3>
                    <span style="font-size: 13px; font-weight: 700; color: var(--primary); display: block; margin-top: 2px;">{{ ucfirst($dev->designation ?? 'Full Stack Developer') }}</span>
                    <div style="margin-top: 8px;">
                        <span style="font-size: 11px; font-family: monospace; font-weight: 700; background: #f1f5f9; padding: 3px 10px; border-radius: 6px; color: var(--slate-muted); border: 1px solid #e2e8f0;">DEV-{{ str_pad($dev->id, 3, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--slate-muted); font-weight: 500;">Email:</span>
                        <strong style="color: var(--slate-heading); font-weight: 700; font-size: 12.5px;">{{ $dev->email }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--slate-muted); font-weight: 500;">Status:</span>
                        <strong style="color: #059669; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #059669; display: inline-block;"></span>
                            {{ ucfirst($empDetail?->status ?? 'Available') }}
                        </strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: var(--slate-muted); font-weight: 500;">Experience:</span>
                        <strong style="color: var(--slate-heading); font-weight: 700;">3+ Years</strong>
                    </div>
                </div>

                <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; letter-spacing: 0.6px; display: block; margin-bottom: 10px;">SKILLS & TECH STACK</span>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        @foreach($skillsArray as $skill)
                            <span style="background: var(--primary-light); color: var(--primary); font-size: 11.5px; font-weight: 700; padding: 4px 12px; border-radius: 14px; border: 1px solid var(--primary-border);">
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- RECENT CONTRIBUTIONS PREVIEW -->
            <div class="dev-card" style="margin-bottom: 0; padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <strong style="font-size: 14.5px; font-weight: 800; color: var(--slate-heading);">Recent Contributions</strong>
                    <a href="{{ route('developer.my-contributions') }}" style="font-size: 12px; color: var(--primary); font-weight: 700; text-decoration: none;">View All</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @forelse($recentContributions as $c)
                    <div style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border-color);">
                        <i class="bx bx-check-circle" style="color: #059669; font-size: 20px; flex-shrink: 0;"></i>
                        <div style="min-width: 0; flex: 1;">
                            <strong style="font-size: 13px; font-weight: 700; color: var(--slate-heading); display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $c->title }}</strong>
                            <span style="font-size: 11px; color: var(--slate-muted);">Completed {{ \Carbon\Carbon::parse($c->updated_at)->diffForHumans() }}</span>
                        </div>
                    </div>
                    @empty
                    <span style="font-size: 12.5px; color: var(--slate-muted);">No completed tasks yet. Completed work automatically updates here.</span>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

</div>
@endsection


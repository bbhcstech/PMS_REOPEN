@extends('layouts.developer')

@section('title', 'My Contributions')
@section('page_title', 'My Platform Contributions')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- HEADER BANNER -->
    <div style="background: linear-gradient(135deg, #0f744c 0%, #065f46 100%); color: #ffffff; padding: 24px 30px; border-radius: var(--radius-xl); box-shadow: var(--shadow-md); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <i class="bx bx-medal" style="font-size: 24px; color: #6ee7b7;"></i>
                <h1 style="font-size: 22px; font-weight: 800; letter-spacing: -0.4px;">Automatic Contribution Analytics</h1>
            </div>
            <p style="color: #a7f3d0; font-size: 13.5px; font-weight: 500;">
                Verified task completion telemetry derived directly from your database records across tenant platforms.
            </p>
        </div>

        <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px); padding: 12px 20px; border-radius: var(--radius-lg); text-align: center; border: 1px solid rgba(255, 255, 255, 0.2);">
            <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #d1fae5; display: block;">VERIFIED COMPLETED TASKS</span>
            <strong style="font-size: 26px; font-weight: 800; color: #ffffff;">{{ $stats['total_completed'] }}</strong>
        </div>
    </div>

    <!-- CONTRIBUTION STATS CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
        <div class="dev-card" style="padding: 18px; text-align: center; margin-bottom: 0;">
            <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; display: block; margin-bottom: 6px;">COMPANIES WORKED WITH</span>
            <strong style="font-size: 24px; font-weight: 800; color: var(--slate-dark);">{{ $stats['companies_count'] }}</strong>
        </div>

        <div class="dev-card" style="padding: 18px; text-align: center; margin-bottom: 0;">
            <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; display: block; margin-bottom: 6px;">PROJECTS WORKED ON</span>
            <strong style="font-size: 24px; font-weight: 800; color: var(--slate-dark);">{{ $stats['projects_count'] }}</strong>
        </div>

        <div class="dev-card" style="padding: 18px; text-align: center; margin-bottom: 0;">
            <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; display: block; margin-bottom: 6px;">IN PROGRESS WORK</span>
            <strong style="font-size: 24px; font-weight: 800; color: #2563eb;">{{ $stats['in_progress'] }}</strong>
        </div>

        <div class="dev-card" style="padding: 18px; text-align: center; margin-bottom: 0;">
            <span style="font-size: 11px; font-weight: 800; color: var(--slate-muted); text-transform: uppercase; display: block; margin-bottom: 6px;">ON HOLD WORK</span>
            <strong style="font-size: 24px; font-weight: 800; color: #64748b;">{{ $stats['on_hold'] }}</strong>
        </div>
    </div>

    <!-- MODULE & PLATFORM SECTION CONTRIBUTIONS BREAKDOWN -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

        <!-- BREAKDOWN BY PROJECT / MODULE -->
        <div class="dev-card" style="margin-bottom: 0;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-dark); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                Website / Module Contributions
            </h3>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @forelse($projectBreakdown as $pb)
                <div style="background: #f8fafc; padding: 14px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <strong style="font-size: 13.5px; color: var(--slate-dark);">{{ $pb['name'] }}</strong>
                        <span style="font-size: 12px; font-weight: 800; color: var(--primary); background: var(--primary-light); padding: 2px 10px; border-radius: 12px; border: 1px solid var(--primary-border);">
                            {{ $pb['completed_count'] }} Completed Tasks
                        </span>
                    </div>
                    <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                        <div style="width: {{ $stats['total_completed'] > 0 ? min(100, round(($pb['completed_count'] / $stats['total_completed']) * 100)) : 0 }}%; height: 100%; background: var(--primary); border-radius: 3px;"></div>
                    </div>
                </div>
                @empty
                <p style="font-size: 13px; color: var(--slate-muted); padding: 20px; text-align: center;">No module contribution history available yet.</p>
                @endforelse
            </div>
        </div>

        <!-- BREAKDOWN BY COMPANY -->
        <div class="dev-card" style="margin-bottom: 0;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-dark); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
                Company Contributions
            </h3>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                @forelse($companyBreakdown as $cb)
                <div style="background: #f8fafc; padding: 14px 16px; border-radius: var(--radius-md); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: #1e293b; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">
                            {{ strtoupper(substr($cb['name'], 0, 2)) }}
                        </div>
                        <div>
                            <strong style="font-size: 13.5px; color: var(--slate-dark); display: block;">{{ $cb['name'] }}</strong>
                            <span style="font-size: 11px; color: var(--slate-muted);">Tenant Company</span>
                        </div>
                    </div>
                    <span style="font-size: 13px; font-weight: 800; color: #059669; background: #ecfdf5; padding: 4px 12px; border-radius: 12px; border: 1px solid #a7f3d0;">
                        {{ $cb['completed_count'] }} Tasks
                    </span>
                </div>
                @empty
                <p style="font-size: 13px; color: var(--slate-muted); padding: 20px; text-align: center;">No company contribution history available yet.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- CHRONOLOGICAL CONTRIBUTION HISTORY -->
    <div class="dev-card">
        <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-dark); margin-bottom: 16px; padding-bottom: 10px; border-bottom: 1px solid var(--border-color);">
            Chronological Contribution Timeline
        </h3>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            @forelse($contributionHistory as $item)
            <div style="display: flex; align-items: flex-start; gap: 14px; padding: 14px; background: #f8fafc; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; margin-top: 2px;">
                    <i class="bx bx-check-circle"></i>
                </div>
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <strong style="font-size: 14px; color: var(--slate-dark);">✓ Completed: {{ $item->title }}</strong>
                        <span style="font-size: 12px; font-weight: 700; color: var(--slate-muted);">{{ \Carbon\Carbon::parse($item->updated_at)->format('d M Y · g:i A') }}</span>
                    </div>
                    <span style="font-size: 12px; color: var(--slate-muted); margin-top: 4px; display: block;">
                        Company: <strong>{{ $item->company_name ?? 'Central Platform' }}</strong> &bull; Project: <strong>{{ $item->project_name ?? 'Development Module' }}</strong> &bull; Estimated: <strong>{{ $item->estimate_hours ?? 8 }}h</strong>
                    </span>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 32px; color: var(--slate-muted);">
                <i class="bx bx-medal" style="font-size: 32px; color: var(--slate-muted); display: block; margin-bottom: 8px;"></i>
                <span>No completed contribution entries recorded yet. As you complete tasks in My Work, your history will automatically populate here.</span>
            </div>
            @endforelse
        </div>

        @if($contributionHistory->hasPages())
        <div style="margin-top: 16px; display: flex; justify-content: center;">
            {{ $contributionHistory->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

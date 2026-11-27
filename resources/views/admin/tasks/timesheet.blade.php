@php
    $timeLogs = $task->tasktimeLogs ?? collect();
    $formatSeconds = function (int $seconds): string {
        $seconds = max(0, $seconds);
        return sprintf('%02dh %02dm %02ds', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
    };
@endphp
<div class="tsh" id="taskTimesheet">
    <section class="tsh-card" aria-labelledby="tshTitle">
        <header class="tsh-head">
            <div class="tsh-head-main">
                <span class="tsh-head-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2.5M9 2h6M12 2v3"/></svg>
                </span>
                <div>
                    <h6 id="tshTitle">Recorded Time Sessions</h6>
                    <p>{{ $timeLogs->count() ? $timeLogs->count() . ' ' . \Illuminate\Support\Str::plural('session', $timeLogs->count()) : 'No sessions yet' }}</p>
                </div>
            </div>
            <span class="tsh-total">
                <span>Total Logged</span>
                <strong>{{ $task->total_logged_formatted ?? '00h 00m 00s' }}</strong>
            </span>
        </header>

        @if($timeLogs->count())
            <div class="tsh-table-wrap">
                <table class="table tsh-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Employee</th>
                            <th scope="col">Start Time</th>
                            <th scope="col">End Time</th>
                            <th scope="col">Memo / Summary</th>
                            <th scope="col" class="text-end">Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($timeLogs as $log)
                            @php
                                $logUser = $log->user->name ?? '--';
                                $startAt = $log->start_time ? \Carbon\Carbon::parse($log->start_time) : null;
                                $endAt = $log->end_time ? \Carbon\Carbon::parse($log->end_time) : null;
                            @endphp
                            <tr>
                                <td>
                                    <div class="tsh-person">
                                        <span class="tsh-avatar" aria-hidden="true">{{ strtoupper(mb_substr(trim($logUser) ?: 'U', 0, 1)) }}</span>
                                        <span class="tsh-name">{{ $logUser }}</span>
                                    </div>
                                </td>
                                <td class="tsh-nowrap"><span class="tsh-chip">{{ $startAt?->format('d M, Y h:i A') ?? '--' }}</span></td>
                                <td class="tsh-nowrap">
                                    @if($endAt)
                                        <span class="tsh-chip">{{ $endAt->format('d M, Y h:i A') }}</span>
                                    @else
                                        <span class="tsh-running"><span class="tsh-pulse" aria-hidden="true"></span>Running</span>
                                    @endif
                                </td>
                                <td><span class="tsh-memo">{{ $log->memo ?: 'No memo recorded' }}</span></td>
                                <td class="text-end tsh-nowrap">
                                    @if($startAt && $endAt)
                                        <span class="tsh-duration">{{ $formatSeconds((int) $startAt->diffInSeconds($endAt)) }}</span>
                                    @else
                                        <span class="tsh-memo">--</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="tsh-empty">
                <span class="tsh-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="13" r="8"/><path d="M12 9v4l2.5 2.5M9 2h6M12 2v3M19 6l1.5-1.5"/></svg>
                </span>
                <strong>No time sessions logged yet</strong>
                <span>Start the task timer to record work sessions. Each session will appear here with its duration.</span>
            </div>
        @endif
    </section>
</div>

<style>
    #taskTimesheet {
        --tsh-surface: #ffffff;
        --tsh-soft: #f6f7fc;
        --tsh-head: #f3f5fc;
        --tsh-ink: #10142c;
        --tsh-muted: #545d82;
        --tsh-faint: #848cb0;
        --tsh-border: rgba(16, 20, 44, .09);
        --tsh-primary: #2f6bff;
        --tsh-primary-soft: rgba(47, 107, 255, .1);
        --tsh-success: #059669;
        --tsh-success-soft: rgba(16, 185, 129, .12);
        --tsh-warn: #b45309;
        --tsh-warn-soft: rgba(245, 158, 11, .14);
        --tsh-shadow: 0 1px 3px rgba(15, 23, 42, .05), 0 1px 2px rgba(15, 23, 42, .03);
        padding: 8px 0 4px;
    }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark, body[data-pms-theme="dark"], body.dark, body.dark-mode) #taskTimesheet {
        --tsh-surface: #0f1530;
        --tsh-soft: #141b3d;
        --tsh-head: #1a2247;
        --tsh-ink: #eef1fb;
        --tsh-muted: #9aa3c7;
        --tsh-faint: #6b739a;
        --tsh-border: rgba(238, 241, 251, .1);
        --tsh-primary-soft: rgba(47, 107, 255, .18);
        --tsh-success: #34d399;
        --tsh-success-soft: rgba(52, 211, 153, .14);
        --tsh-warn: #fbbf24;
        --tsh-warn-soft: rgba(251, 191, 36, .14);
        --tsh-shadow: 0 2px 8px rgba(0, 0, 0, .25);
    }
    /* The dark theme forces every SVG to white; these icons take their colour from their own badge. */
    html #taskTimesheet svg { color: inherit !important; }
    :is(html[data-pms-theme="dark"], html[data-bs-theme="dark"], html[data-theme="dark"], html.dark) #taskTimesheet svg { color: inherit !important; }
    #taskTimesheet .tsh-card { border: 1px solid var(--tsh-border); border-radius: 16px; background: var(--tsh-surface) !important; box-shadow: var(--tsh-shadow); padding: 18px 20px; }
    #taskTimesheet .tsh-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
    #taskTimesheet .tsh-head-main { display: flex; align-items: center; gap: 12px; min-width: 0; }
    #taskTimesheet .tsh-head-icon { width: 36px; height: 36px; flex-shrink: 0; border-radius: 10px; display: grid; place-items: center; background: var(--tsh-primary-soft); color: var(--tsh-primary); }
    #taskTimesheet .tsh-head-icon svg { width: 18px; height: 18px; }
    #taskTimesheet .tsh-head h6 { margin: 0; font-size: .95rem; font-weight: 700; color: var(--tsh-ink) !important; -webkit-text-fill-color: var(--tsh-ink) !important; }
    #taskTimesheet .tsh-head p { margin: 2px 0 0; font-size: .8rem; color: var(--tsh-muted) !important; -webkit-text-fill-color: var(--tsh-muted) !important; }
    #taskTimesheet .tsh-total { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: var(--tsh-success-soft); border: 1px solid rgba(16, 185, 129, .25); }
    #taskTimesheet .tsh-total span { font-size: .74rem; font-weight: 600; color: var(--tsh-muted) !important; -webkit-text-fill-color: var(--tsh-muted) !important; }
    #taskTimesheet .tsh-total strong { font-size: .84rem; font-variant-numeric: tabular-nums; color: var(--tsh-success) !important; -webkit-text-fill-color: var(--tsh-success) !important; }

    #taskTimesheet .tsh-table-wrap { border: 1px solid var(--tsh-border); border-radius: 12px; overflow-x: auto; }
    #taskTimesheet .tsh-table { width: 100%; min-width: 720px; font-size: .86rem; background: transparent !important; }
    #taskTimesheet .tsh-table thead th { padding: 11px 14px !important; background: var(--tsh-head) !important; border-bottom: 1px solid var(--tsh-border) !important; font-size: .72rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; color: var(--tsh-muted) !important; -webkit-text-fill-color: var(--tsh-muted) !important; }
    #taskTimesheet .tsh-table tbody td { padding: 12px 14px !important; background: transparent !important; border-bottom: 1px solid var(--tsh-border) !important; color: var(--tsh-ink) !important; -webkit-text-fill-color: var(--tsh-ink) !important; vertical-align: middle; }
    #taskTimesheet .tsh-table tbody tr:last-child td { border-bottom: 0 !important; }
    #taskTimesheet .tsh-table tbody tr:hover td { background: var(--tsh-soft) !important; }
    #taskTimesheet .tsh-nowrap { white-space: nowrap; }
    #taskTimesheet .tsh-person { display: flex; align-items: center; gap: 10px; }
    #taskTimesheet .tsh-avatar { width: 30px; height: 30px; flex-shrink: 0; border-radius: 50%; display: grid; place-items: center; background: linear-gradient(135deg, #2f6bff, #8b5cf6); color: #fff !important; -webkit-text-fill-color: #fff !important; font-weight: 700; font-size: .76rem; }
    #taskTimesheet .tsh-name { font-weight: 600; color: var(--tsh-ink) !important; -webkit-text-fill-color: var(--tsh-ink) !important; }
    #taskTimesheet .tsh-chip { display: inline-block; padding: 3px 9px; border-radius: 8px; border: 1px solid var(--tsh-border); background: var(--tsh-soft); font-size: .78rem; font-variant-numeric: tabular-nums; color: var(--tsh-ink) !important; -webkit-text-fill-color: var(--tsh-ink) !important; }
    #taskTimesheet .tsh-running { display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; background: var(--tsh-warn-soft); font-size: .78rem; font-weight: 700; color: var(--tsh-warn) !important; -webkit-text-fill-color: var(--tsh-warn) !important; }
    #taskTimesheet .tsh-pulse { width: 7px; height: 7px; border-radius: 50%; background: currentColor; animation: tshPulse 1.4s ease-in-out infinite; }
    @keyframes tshPulse { 0%, 100% { opacity: 1; } 50% { opacity: .3; } }
    #taskTimesheet .tsh-memo { color: var(--tsh-muted) !important; -webkit-text-fill-color: var(--tsh-muted) !important; }
    #taskTimesheet .tsh-duration { font-weight: 700; font-variant-numeric: tabular-nums; color: var(--tsh-success) !important; -webkit-text-fill-color: var(--tsh-success) !important; }

    #taskTimesheet .tsh-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; text-align: center; padding: 28px 16px; border: 1px dashed var(--tsh-border); border-radius: 14px; background: var(--tsh-soft); }
    #taskTimesheet .tsh-empty-icon { width: 52px; height: 52px; border-radius: 16px; display: grid; place-items: center; background: var(--tsh-primary-soft); color: var(--tsh-primary); margin-bottom: 6px; }
    #taskTimesheet .tsh-empty-icon svg { width: 26px; height: 26px; }
    #taskTimesheet .tsh-empty strong { font-size: .95rem; color: var(--tsh-ink) !important; -webkit-text-fill-color: var(--tsh-ink) !important; }
    #taskTimesheet .tsh-empty > span:not(.tsh-empty-icon) { max-width: 420px; font-size: .84rem; line-height: 1.5; color: var(--tsh-muted) !important; -webkit-text-fill-color: var(--tsh-muted) !important; }

    @media (max-width: 575.98px) {
        #taskTimesheet .tsh-card { padding: 16px; }
        #taskTimesheet .tsh-total { width: 100%; justify-content: space-between; }
    }
    @media (prefers-reduced-motion: reduce) { #taskTimesheet .tsh-pulse { animation: none; } }
</style>

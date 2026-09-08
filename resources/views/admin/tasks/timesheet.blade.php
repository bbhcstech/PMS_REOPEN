<div class="py-2">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-bold text-dark mb-0"><i class="bx bx-time-five me-1 text-primary"></i> Recorded Time Sessions</h6>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
            Total Logged: {{ $task->total_logged_formatted ?? '00h 00m 00s' }}
        </span>
    </div>

    <div class="table-responsive rounded-4 border">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light">
                <tr>
                    <th class="ps-3 py-3">Employee</th>
                    <th class="py-3">Start Time</th>
                    <th class="py-3">End Time</th>
                    <th class="py-3">Memo / Summary</th>
                    <th class="py-3 pe-3 text-end">Duration</th>
                </tr>
            </thead>
            <tbody>
                @forelse($task->tasktimeLogs as $log)
                    <tr>
                        <td class="ps-3 fw-bold text-dark">
                            <div class="d-flex align-items-center gap-2">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: #e2e8f0; color: #334155; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.75rem;">
                                    {{ strtoupper(substr($log->user->name ?? 'U', 0, 1)) }}
                                </div>
                                {{ $log->user->name ?? '--' }}
                            </div>
                        </td>
                        <td style="white-space: nowrap;">
                            <span class="badge bg-light text-dark border">{{ \Carbon\Carbon::parse($log->start_time)->format('d M, Y h:i A') }}</span>
                        </td>
                        <td style="white-space: nowrap;">
                            @if($log->end_time)
                                <span class="badge bg-light text-dark border">{{ \Carbon\Carbon::parse($log->end_time)->format('d M, Y h:i A') }}</span>
                            @else
                                <span class="badge bg-warning text-dark"><i class="bx bx-loader-alt bx-spin me-1"></i> Running</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-secondary">{{ $log->memo ?: 'No memo recorded' }}</span>
                        </td>
                        <td class="pe-3 text-end fw-bold text-success" style="white-space: nowrap;">
                            @if($log->end_time)
                                @php
                                    $duration = \Carbon\Carbon::parse($log->start_time)->diff(\Carbon\Carbon::parse($log->end_time));
                                    $hours = $duration->h;
                                    $minutes = $duration->i;
                                    $seconds = $duration->s;
                                @endphp
                                {{ $hours > 0 ? $hours . 'h ' : '' }}{{ $minutes > 0 ? $minutes . 'm ' : '' }}{{ $seconds > 0 ? $seconds . 's' : '' }}
                            @else
                                --
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bx bx-timer fs-2 d-block mb-1"></i>
                            No time sessions logged for this task yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

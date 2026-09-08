<div class="py-2">
    <h6 class="fw-bold text-dark mb-3"><i class="bx bx-history me-1 text-primary"></i> Activity & Audit Trail</h6>
    @if(isset($history) && $history->isNotEmpty())
        <div class="d-flex flex-column gap-2">
            @foreach($history as $log)
                <div class="p-3 rounded-4 bg-white border shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge {{ ($log['type'] ?? '') === 'timer' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} rounded-pill px-2 py-1">
                                <i class="bx {{ ($log['type'] ?? '') === 'timer' ? 'bx-time-five' : 'bx-list-check' }} me-1"></i>
                                {{ ucfirst($log['type'] ?? 'Action') }}
                            </span>
                            <strong class="text-dark small">{{ $log['user'] ?? 'System' }}</strong>
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            <i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::parse($log['created_at'])->format('d M, Y h:i A') }}
                        </small>
                    </div>
                    <div class="text-secondary ps-2" style="font-size: 0.9rem;">
                        {{ $log['description'] }}
                        @if(!empty($log['subtask']))
                            <div class="mt-1 small text-muted">
                                <i class="bx bx-subdirectory-right me-1"></i> Subtask: <strong>{{ $log['subtask'] }}</strong>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-4 bg-light rounded-4 border">
            <i class="bx bx-time fs-1 text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">No activity history recorded for this task yet.</p>
        </div>
    @endif
</div>

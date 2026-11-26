<div class="py-2">
    <div class="p-4 rounded-4 bg-light border mb-4">
        <h6 class="fw-bold text-dark mb-3"><i class="bx bx-note me-1 text-primary"></i> Add Internal Note</h6>
        <form action="{{ route('tasks.notes.store', $task->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <textarea name="note" class="form-control rounded-3" rows="3" placeholder="Enter private note for this task..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4">
                <i class="bx bx-plus-circle me-1"></i> Save Note
            </button>
        </form>
    </div>

    <h6 class="fw-bold text-dark mb-3"><i class="bx bx-list-ul me-1 text-success"></i> Saved Notes</h6>
    @if($task->notes && $task->notes->count())
        <div class="d-flex flex-column gap-3">
            @foreach($task->notes as $note)
                <div class="p-3 rounded-4 bg-white border shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 30px; height: 30px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.75rem;">
                                <i class="bx bx-bookmark"></i>
                            </div>
                            <strong class="text-dark">{{ $note->user->name ?? 'Unknown' }}</strong>
                        </div>
                        <small class="text-muted"><i class="bx bx-time-five me-1"></i>{{ $note->created_at->format('d M, Y h:i A') }}</small>
                    </div>
                    <div class="text-secondary ps-4" style="font-size: 0.92rem; line-height: 1.5;">
                        {{ $note->note }}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-4 bg-light rounded-4 border">
            <i class="bx bx-notepad fs-1 text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">No notes added yet for this task.</p>
        </div>
    @endif
</div>

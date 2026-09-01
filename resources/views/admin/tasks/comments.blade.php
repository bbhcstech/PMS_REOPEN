<div class="py-2">
    {{-- Comment Form --}}
    <div class="p-4 rounded-4 bg-light border mb-4">
        <h6 class="fw-bold text-dark mb-3"><i class="bx bx-message-dots me-1 text-primary"></i> Leave a Comment</h6>
        <form action="{{ route('task-comments.store', $task->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <textarea name="comment" class="form-control rounded-3" rows="3" placeholder="Write your thoughts, questions, or updates on this task..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4">
                <i class="bx bx-send me-1"></i> Post Comment
            </button>
        </form>
    </div>

    {{-- Comments List --}}
    <h6 class="fw-bold text-dark mb-3"><i class="bx bx-conversation me-1 text-success"></i> Discussion History</h6>
    @if($task->comments && $task->comments->count())
        <div class="d-flex flex-column gap-3">
            @foreach($task->comments as $comment)
                <div class="p-3 rounded-4 bg-white border shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #0f744c, #10b981); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem;">
                                {{ strtoupper(substr($comment->user->name ?? 'U', 0, 1)) }}
                            </div>
                            <strong class="text-dark">{{ $comment->user->name ?? 'Unknown' }}</strong>
                        </div>
                        <small class="text-muted"><i class="bx bx-time-five me-1"></i>{{ $comment->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="text-secondary ps-4" style="font-size: 0.92rem; line-height: 1.5;">
                        {{ $comment->comment }}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-4 bg-light rounded-4 border">
            <i class="bx bx-chat fs-1 text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">No comments posted yet. Be the first to start the discussion!</p>
        </div>
    @endif
</div>
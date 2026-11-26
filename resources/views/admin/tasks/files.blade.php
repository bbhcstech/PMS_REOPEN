<div class="py-2">
    <div class="p-4 rounded-4 bg-light border mb-4">
        <h6 class="fw-bold text-dark mb-3"><i class="bx bx-cloud-upload me-1 text-primary"></i> Upload New Attachment</h6>
        <form action="{{ route('tasks.uploadFile', $task->id) }}" method="POST" enctype="multipart/form-data" class="d-flex flex-column flex-sm-row gap-2">
            @csrf
            <input type="file" name="attachment" class="form-control rounded-3" required>
            <button type="submit" class="btn btn-primary rounded-pill px-4 text-nowrap">
                <i class="bx bx-upload me-1"></i> Upload File
            </button>
        </form>
    </div>

    <h6 class="fw-bold text-dark mb-3"><i class="bx bx-paperclip me-1 text-success"></i> Attached Documents & Media</h6>
    @if($task->image_url)
        <div class="card border rounded-4 shadow-sm p-3 d-flex flex-row align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="p-3 rounded-3 bg-primary-subtle text-primary fs-3">
                    <i class="bx bx-file"></i>
                </div>
                <div>
                    <h6 class="mb-1 fw-bold text-dark">{{ basename($task->image_url) }}</h6>
                    <small class="text-muted">Task Attachment</small>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                @if(Str::contains(strtolower($task->image_url), ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
                    <a href="{{ asset($task->image_url) }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bx bx-show me-1"></i> View
                    </a>
                @endif
                <a href="{{ asset($task->image_url) }}" download class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="bx bx-download me-1"></i> Download
                </a>
            </div>
        </div>
        @if(Str::contains(strtolower($task->image_url), ['.jpg', '.jpeg', '.png', '.gif', '.webp']))
            <div class="mt-3">
                <img src="{{ asset($task->image_url) }}" alt="Attachment Preview" class="img-fluid rounded-4 border shadow-sm" style="max-height: 280px; object-fit: contain;">
            </div>
        @endif
    @else
        <div class="text-center py-4 bg-light rounded-4 border">
            <i class="bx bx-folder-open fs-1 text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">No files uploaded yet for this task.</p>
        </div>
    @endif
</div>

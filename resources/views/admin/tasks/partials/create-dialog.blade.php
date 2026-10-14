<div class="modal fade" id="taskCreateDialog" tabindex="-1" aria-labelledby="taskCreateDialogTitle" aria-hidden="true" data-live-preserve>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskCreateDialogTitle">Add Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <iframe title="Add task form" style="width:100%;height:75vh;border:0;display:block" data-task-form-frame></iframe>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('click', function (event) {
    const trigger = event.target.closest('[data-task-form-open]');
    if (!trigger || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    const dialog = document.getElementById('taskCreateDialog');
    dialog.querySelector('[data-task-form-frame]').src = trigger.href;
    bootstrap.Modal.getOrCreateInstance(dialog).show();
});
window.addEventListener('message', function (event) {
    const dialog = document.getElementById('taskCreateDialog');
    const frame = dialog?.querySelector('[data-task-form-frame]');
    if (event.origin !== window.location.origin || event.source !== frame?.contentWindow) return;
    if (event.data === 'task-form-cancel') bootstrap.Modal.getOrCreateInstance(dialog).hide();
    if (event.data === 'task-form-saved') window.location.reload();
});
</script>
@endpush

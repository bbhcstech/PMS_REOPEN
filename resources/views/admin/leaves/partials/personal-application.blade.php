<form id="hr-leave-application" class="hl-form" method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data" data-live-preserve>
    @csrf

    @if($errors->any())
        <div class="hl-form-error" role="alert">
            <strong>Review your request</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($leaveBalance)
        <p class="hl-balance">
            <i class="bx bx-wallet" aria-hidden="true"></i>
            Remaining paid leave <strong>{{ (float) $leaveBalance->remaining_leaves }} days</strong>
        </p>
    @endif

    <div class="hl-form-grid">
        <div class="hl-field">
            <label for="hl-apply-type">Leave type</label>
            <select id="hl-apply-type" name="leave_type_id" required>
                <option value="">Select a leave type</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->id }}" data-document="{{ (int) $type->requires_document }}" @selected((int) old('leave_type_id') === (int) $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="hl-field">
            <label for="hl-apply-contact">Contact during leave</label>
            <input type="tel" id="hl-apply-contact" name="contact_during_leave" value="{{ old('contact_during_leave') }}" placeholder="+919876543210" autocomplete="tel" pattern="\+[1-9][0-9]{6,14}" maxlength="25">
            <small>Optional. Include the country code.</small>
        </div>
        <div class="hl-field">
            <label for="hl-apply-start">Start date</label>
            <input type="date" id="hl-apply-start" name="start_date" value="{{ old('start_date') }}" required>
        </div>
        <div class="hl-field">
            <label for="hl-apply-end">End date</label>
            <input type="date" id="hl-apply-end" name="end_date" value="{{ old('end_date') }}" required>
        </div>
        <div class="hl-field">
            <label for="hl-apply-reason">Reason</label>
            <textarea id="hl-apply-reason" name="reason" rows="3" maxlength="2000" required>{{ old('reason') }}</textarea>
        </div>
        <div class="hl-field">
            <label for="hl-apply-note">Regularization note</label>
            <textarea id="hl-apply-note" name="apology_note" rows="3" maxlength="2000" aria-describedby="hl-note-hint">{{ old('apology_note') }}</textarea>
            <small id="hl-note-hint">Required for past sick leave when your policy allows it.</small>
        </div>
        <div class="hl-field">
            <label for="hl-apply-file">Supporting document</label>
            <input type="file" id="hl-apply-file" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" aria-describedby="hl-attachment-hint">
            <small id="hl-attachment-hint">PDF, Word, JPG, or PNG up to 5 MB.</small>
        </div>
    </div>

    <div class="hl-options">
        <label><input type="checkbox" name="half_day_flag" value="1" @checked(old('half_day_flag'))> Half-day leave</label>
        <label><input type="checkbox" name="emergency_flag" value="1" @checked(old('emergency_flag'))> Emergency request</label>
    </div>
    <div class="hl-form-footer">
        <output class="hl-duration" for="hl-apply-start hl-apply-end" aria-live="polite"></output>
        <a class="hl-button" href="{{ $cancelUrl }}">Cancel</a>
        <button type="submit" class="hl-button hl-primary"><i class="bx bx-send" aria-hidden="true"></i> Send to admin</button>
    </div>
</form>

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('hr-leave-application');
        if (!form) return;
        const { start_date: start, end_date: end, half_day_flag: halfDay, leave_type_id: type, attachment } = form.elements;
        const duration = form.querySelector('output');
        const attachmentHint = form.querySelector('#hl-attachment-hint');

        function updateDates() {
            end.min = start.value;
            end.max = halfDay.checked ? start.value : '';
            const days = (end.valueAsNumber - start.valueAsNumber) / 86400000 + 1;
            const count = halfDay.checked && days === 1 ? 0.5 : days;
            duration.textContent = Number.isFinite(count) && count > 0
                ? count + (count === 1 ? ' day requested' : ' days requested')
                : '';
        }

        start.addEventListener('change', () => {
            if (!end.value || end.value < start.value || halfDay.checked) end.value = start.value;
            updateDates();
        });
        end.addEventListener('change', updateDates);
        halfDay.addEventListener('change', () => {
            if (halfDay.checked && start.value) end.value = start.value;
            updateDates();
        });

        function updateAttachment() {
            attachment.required = type.selectedOptions[0]?.dataset.document === '1';
            attachmentHint.textContent = (attachment.required ? 'Required. ' : '') + 'PDF, Word, JPG, or PNG up to 5 MB.';
        }

        type.addEventListener('change', updateAttachment);
        const submit = form.querySelector('button[type="submit"]');
        form.addEventListener('submit', () => { submit.disabled = true; });
        window.addEventListener('pageshow', () => { submit.disabled = false; });
        updateDates();
        updateAttachment();
    })();
</script>
@endpush

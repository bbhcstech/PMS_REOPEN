@extends('admin.layout.app')

@section('title', 'Send Apology Letter')

@section('content')
<div class="leave-form-page">
    <div class="leave-breadcrumb"><i class="fas fa-envelope-open-text"></i> Dashboard / Leaves / Apology Letter</div>

    <section class="leave-form-hero">
        <div>
            <h1>Send Apology Letter</h1>
            <p>Use the professional sample, edit your message, and send it to HR for review.</p>
        </div>
        <a href="{{ route('leaves.apology-letters.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back to Letters</a>
    </section>

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix these issues:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="form-card">
        <div class="form-grid two">
            <div>
                <h3>Professional Sample</h3>
                <textarea id="sampleLetter" class="form-control" rows="16" readonly>{{ $sample }}</textarea>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <button type="button" class="btn btn-secondary" id="copySampleBtn"><i class="fas fa-copy"></i> Copy Sample</button>
                    <button type="button" class="btn btn-light" id="useSampleBtn"><i class="fas fa-pen"></i> Use Sample</button>
                    <a href="{{ route('leaves.apology-letters.sample.download', ['leave_id' => $leave?->id]) }}" class="btn btn-light"><i class="fas fa-download"></i> Download Demo</a>
                </div>
            </div>

            <form method="POST" action="{{ route('leaves.apology-letters.store') }}">
                @csrf

                <div class="mb-3">
                    <label>Related Leave</label>
                    <select name="leave_id" class="form-control">
                        <option value="">No specific leave</option>
                        @foreach($leaves as $item)
                            <option value="{{ $item->id }}" {{ (string) old('leave_id', $leave?->id) === (string) $item->id ? 'selected' : '' }}>
                                {{ $item->type_label }} - {{ optional($item->start_date)->format('d M Y') }} to {{ optional($item->end_date)->format('d M Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label>HR Email</label>
                    <input type="email" name="recipient_email" class="form-control" value="{{ old('recipient_email') }}" placeholder="hr@example.com">
                </div>

                <div class="mb-3">
                    <label>Subject <span>*</span></label>
                    <input type="text" name="subject" class="form-control" required value="{{ old('subject', 'Apology Letter Regarding Leave') }}">
                </div>

                <div class="mb-3">
                    <label>Apology Letter <span>*</span></label>
                    <textarea name="body" id="body" class="form-control" rows="13" required>{{ old('body') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('leaves.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                    <button class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send to HR</button>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sample = document.getElementById('sampleLetter');
    const body = document.getElementById('body');

    document.getElementById('copySampleBtn')?.addEventListener('click', async function () {
        await navigator.clipboard.writeText(sample.value);
        this.innerHTML = '<i class="fas fa-check"></i> Copied';
        setTimeout(() => this.innerHTML = '<i class="fas fa-copy"></i> Copy Sample', 1600);
    });

    document.getElementById('useSampleBtn')?.addEventListener('click', function () {
        body.value = sample.value;
        body.focus();
    });
});
</script>

<style>
    .leave-form-page { padding: 30px 35px; min-height: 100vh; background: linear-gradient(135deg, #f0f9f4, #f7fbff); color: #102119; }
    .leave-breadcrumb, .leave-form-hero, .form-card { border: 1px solid rgba(16,185,129,.12); background: rgba(255,255,255,.96); box-shadow: 0 16px 36px -20px rgba(15,23,42,.22); }
    .leave-breadcrumb { display: inline-flex; gap: 8px; align-items: center; padding: 12px 18px; border-radius: 14px; color: #0f744c; font-weight: 900; margin-bottom: 22px; }
    .leave-form-hero { display: flex; justify-content: space-between; gap: 18px; align-items: center; padding: 28px; border-radius: 24px; margin-bottom: 20px; }
    .leave-form-hero h1 { margin: 0 0 6px; font-size: 34px; font-weight: 900; }
    .leave-form-hero p { margin: 0; color: #667085; font-weight: 650; }
    .form-card { padding: 24px; border-radius: 22px; }
    .form-grid.two { display: grid; grid-template-columns: 1fr 1fr; gap: 22px; }
    label { display: block; color: #667085; text-transform: uppercase; font-size: .76rem; font-weight: 900; margin-bottom: 6px; }
    label span { color: #dc2626; }
    .form-control { min-height: 46px; border-radius: 12px; border: 1px solid #dbe7e1; font-weight: 650; }
    textarea.form-control { font-family: inherit; white-space: pre-wrap; }
    .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; min-height: 44px; font-weight: 900; border: 0; }
    .btn-primary { background: linear-gradient(145deg, #34d399, #059669); color: #fff; }
    .btn-light, .btn-secondary { background: #f0f9f4; color: #0f744c; border: 1px solid rgba(16,185,129,.18); }
    @media (max-width: 992px) { .leave-form-page { padding: 18px; } .leave-form-hero, .form-grid.two { grid-template-columns: 1fr; flex-direction: column; align-items: flex-start; } }
</style>
@endsection

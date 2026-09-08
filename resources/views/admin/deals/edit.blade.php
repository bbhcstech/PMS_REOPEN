@extends('admin.layout.app')

@section('content')

<style>
.form-card-modern {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}
html[data-pms-theme="dark"] .form-card-modern {
    background: #102119;
    border-color: rgba(225, 255, 240, 0.12);
}
.form-card-header-modern {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 1.1rem 1.5rem;
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
}
html[data-pms-theme="dark"] .form-card-header-modern {
    background: #102119;
    border-bottom-color: rgba(225, 255, 240, 0.08);
}
.form-label-modern {
    font-size: 0.84rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 0.4rem;
    display: block;
}
html[data-pms-theme="dark"] .form-label-modern {
    color: #cbd5e1;
}
.form-control-modern, .form-select-modern {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.55rem 0.9rem;
    font-size: 0.875rem;
    color: #0f172a;
    font-weight: 500;
    min-height: 42px;
    transition: all 0.18s ease-in-out;
}
html[data-pms-theme="dark"] .form-control-modern,
html[data-pms-theme="dark"] .form-select-modern {
    background-color: #183026;
    border-color: rgba(225, 255, 240, 0.15);
    color: #ffffff;
}
.form-control-modern:focus, .form-select-modern:focus {
    background-color: #ffffff;
    border-color: #0f744c;
    box-shadow: 0 0 0 3.5px rgba(15, 116, 76, 0.12);
    color: #0f172a;
    outline: none;
}
html[data-pms-theme="dark"] .form-control-modern:focus,
html[data-pms-theme="dark"] .form-select-modern:focus {
    background-color: #102119;
    border-color: #40d48c;
    box-shadow: 0 0 0 3.5px rgba(64, 212, 140, 0.18);
    color: #ffffff;
}
.form-control-modern::placeholder {
    color: #94a3b8;
    font-weight: 400;
}
html[data-pms-theme="dark"] .form-control-modern::placeholder {
    color: #64748b;
}
textarea.form-control-modern {
    min-height: auto;
}
.btn-back-pill {
    background-color: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    border-radius: 50px;
    padding: 0.45rem 1.15rem;
    font-size: 0.85rem;
    font-weight: 600;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    transition: all 0.2s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.btn-back-pill:hover {
    background-color: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
html[data-pms-theme="dark"] .btn-back-pill {
    background-color: #183026;
    border-color: rgba(225, 255, 240, 0.2);
    color: #e2e8f0;
}
html[data-pms-theme="dark"] .btn-back-pill:hover {
    background-color: #204033;
    color: #ffffff;
}
.btn-submit-emerald {
    background: linear-gradient(135deg, #0f744c 0%, #10b981 100%);
    color: #ffffff;
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1.75rem;
    font-size: 0.9rem;
    font-weight: 600;
    box-shadow: 0 4px 14px rgba(15, 116, 76, 0.25);
    transition: all 0.2s ease;
}
.btn-submit-emerald:hover {
    background: linear-gradient(135deg, #094c32 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 6px 18px rgba(15, 116, 76, 0.35);
    transform: translateY(-1px);
}
.btn-cancel-modern {
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #475569;
    border-radius: 10px;
    padding: 0.65rem 1.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.2s ease;
    text-decoration: none;
}
.btn-cancel-modern:hover {
    background: #e2e8f0;
    color: #1e293b;
}
html[data-pms-theme="dark"] .btn-cancel-modern {
    background: #183026;
    border-color: rgba(225, 255, 240, 0.15);
    color: #cbd5e1;
}
html[data-pms-theme="dark"] .btn-cancel-modern:hover {
    background: #204033;
    color: #ffffff;
}
</style>

<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: #0f172a;">
                <span class="fs-4">✏️</span> Edit Deal Opportunity
            </h4>
            <p class="text-muted small mb-0">Update deal information, stage, probability, and deal value for {{ $deal->deal_name }}</p>
        </div>
        <a href="{{ route('admin.deals.index') }}" class="btn-back-pill">
            <i class="fas fa-arrow-left me-1"></i> Back to Deals
        </a>
    </div>

    {{-- FORM CARD --}}
    <form action="{{ route('admin.deals.update', $deal->id) }}" method="POST" id="dealEditForm">
        @csrf
        @method('PUT')

        <div class="card form-card-modern mb-4">
            <div class="card-header form-card-header-modern">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2"><span class="fs-5">🤝</span> Deal Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label-modern">Deal Name <span class="text-danger">*</span></label>
                        <input type="text" name="deal_name" class="form-control form-control-modern @error('deal_name') is-invalid @enderror" value="{{ old('deal_name', $deal->deal_name) }}" required placeholder="e.g. Enterprise License Expansion">
                        @error('deal_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label-modern">Associate Lead Contact</label>
                        <select name="lead_id" id="leadSelect" class="form-select form-select-modern">
                            <option value="">Select Existing Lead (Optional)</option>
                            @foreach($leads as $ld)
                                <option value="{{ $ld->id }}" {{ old('lead_id', $deal->lead_id) == $ld->id ? 'selected' : '' }}>
                                    {{ $ld->contact_name }} {{ $ld->company_name ? "({$ld->company_name})" : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Lead Contact Name <span class="text-danger">*</span></label>
                        <input type="text" name="lead_name" class="form-control form-control-modern @error('lead_name') is-invalid @enderror" value="{{ old('lead_name', $deal->lead_name) }}" required placeholder="e.g. John Doe">
                        @error('lead_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Company Name</label>
                        <input type="text" name="company_name" class="form-control form-control-modern" value="{{ old('company_name', $deal->company_name) }}" placeholder="e.g. Acme Inc">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Contact Details <span class="text-danger">*</span></label>
                        <input type="text" name="contact_details" class="form-control form-control-modern @error('contact_details') is-invalid @enderror" value="{{ old('contact_details', $deal->contact_details) }}" required placeholder="Email / Phone">
                        @error('contact_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Deal Value <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="value" class="form-control form-control-modern @error('value') is-invalid @enderror" value="{{ old('value', $deal->value) }}" required placeholder="0">
                        @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label-modern">Currency</label>
                        <select name="currency" class="form-select form-select-modern">
                            <option value="INR" {{ old('currency', $deal->currency) == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                            <option value="USD" {{ old('currency', $deal->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="EUR" {{ old('currency', $deal->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            <option value="GBP" {{ old('currency', $deal->currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-modern">Pipeline Stage <span class="text-danger">*</span></label>
                        <select name="deal_stage_id" id="stageSelect" class="form-select form-select-modern" required>
                            @foreach($stages as $stg)
                                <option value="{{ $stg->id }}" data-prob="{{ $stg->default_probability }}" {{ old('deal_stage_id', $deal->deal_stage_id) == $stg->id ? 'selected' : '' }}>
                                    {{ $stg->name }} ({{ $stg->default_probability }}% Prob)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label-modern">Win Probability (%)</label>
                        <input type="number" min="0" max="100" name="probability" id="inputProbability" class="form-control form-control-modern" value="{{ old('probability', $deal->probability) }}" placeholder="10">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Pipeline</label>
                        <select name="pipeline" class="form-select form-select-modern">
                            <option value="Sales Pipeline" {{ old('pipeline', $deal->pipeline) == 'Sales Pipeline' ? 'selected' : '' }}>Sales Pipeline</option>
                            <option value="Marketing Pipeline" {{ old('pipeline', $deal->pipeline) == 'Marketing Pipeline' ? 'selected' : '' }}>Marketing Pipeline</option>
                            <option value="Other Pipeline" {{ old('pipeline', $deal->pipeline) == 'Other Pipeline' ? 'selected' : '' }}>Other Pipeline</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Category</label>
                        <select name="deal_category_id" class="form-select form-select-modern">
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('deal_category_id', $deal->deal_category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Deal Priority</label>
                        <select name="priority" class="form-select form-select-modern">
                            <option value="medium" {{ old('priority', $deal->priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ old('priority', $deal->priority) == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="high" {{ old('priority', $deal->priority) == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ old('priority', $deal->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Assigned Deal Agent</label>
                        <select name="deal_agent_id" class="form-select form-select-modern">
                            <option value="">Select Agent</option>
                            @foreach($agents as $ag)
                                <option value="{{ $ag->id }}" {{ old('deal_agent_id', $deal->deal_agent_id) == $ag->id ? 'selected' : '' }}>{{ $ag->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Expected Close Date <span class="text-danger">*</span></label>
                        <input type="date" name="close_date" class="form-control form-control-modern @error('close_date') is-invalid @enderror" value="{{ old('close_date', $deal->close_date ? $deal->close_date->format('Y-m-d') : '') }}" required>
                        @error('close_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label-modern">Next Follow-up Date</label>
                        <input type="date" name="next_follow_up" class="form-control form-control-modern" value="{{ old('next_follow_up', $deal->next_follow_up ? $deal->next_follow_up->format('Y-m-d') : '') }}" placeholder="dd-mm-yyyy">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label-modern">Product / Service Description</label>
                        <input type="text" name="product" class="form-control form-control-modern" value="{{ old('product', $deal->product) }}" placeholder="e.g. ERP Software License 1-Year Subscription">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label-modern">Notes / Description</label>
                        <textarea name="notes" class="form-control form-control-modern" rows="3" placeholder="Enter opportunity details, negotiation notes, or client preferences...">{{ old('notes', $deal->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-5">
            <a href="{{ route('admin.deals.index') }}" class="btn-cancel-modern">Cancel</a>
            <button type="submit" class="btn-submit-emerald"><i class="fas fa-save me-1"></i> Update Deal</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stageSelect = document.getElementById('stageSelect');
    const inputProbability = document.getElementById('inputProbability');

    if (stageSelect && inputProbability) {
        stageSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (opt && opt.dataset.prob !== undefined) {
                inputProbability.value = opt.dataset.prob;
            }
        });
    }
});
</script>

@endsection

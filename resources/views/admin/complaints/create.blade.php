@extends('admin.layout.app')

@section('content')
<div class="container-fluid px-4 py-4" style="max-width: 960px;">

  <div class="mb-3">
    <a href="{{ route('admin.company-complaints.index') }}" class="btn btn-sm btn-light border fw-bold" style="border-radius: 8px;">
      <i class="bx bx-left-arrow-alt"></i> Back to Complaints List
    </a>
  </div>

  <div class="card border-0 shadow-sm" style="border-radius: 16px;">
    <div class="card-header bg-dark text-white p-4" style="border-radius: 16px 16px 0 0;">
      <h4 class="fw-bold mb-1"><i class="bx bx-plus-circle me-2 text-emerald" style="color: #10b981;"></i>Create Support Ticket / Raise Complaint</h4>
      <p class="mb-0 text-white-50 fs-7">Submit your technical issue, billing query, or service request directly to the Super Admin team.</p>
    </div>

    <div class="card-body p-4">
      <form method="POST" action="{{ route('admin.company-complaints.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="row g-3 mb-3">
          <div class="col-md-8">
            <label class="form-label fw-bold text-dark">Subject <span class="text-danger">*</span></label>
            <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Brief summary of your issue..." class="form-control" required style="border-radius: 8px;" />
          </div>

          <div class="col-md-4">
            <label class="form-label fw-bold text-dark">Priority <span class="text-danger">*</span></label>
            <select name="priority" class="form-select" required style="border-radius: 8px;">
              <option value="LOW" {{ old('priority') === 'LOW' ? 'selected' : '' }}>LOW — General Inquiry</option>
              <option value="MEDIUM" {{ old('priority', 'MEDIUM') === 'MEDIUM' ? 'selected' : '' }}>MEDIUM — Normal Issue</option>
              <option value="HIGH" {{ old('priority') === 'HIGH' ? 'selected' : '' }}>HIGH — Urgent Feature/Data Need</option>
              <option value="CRITICAL" {{ old('priority') === 'CRITICAL' ? 'selected' : '' }}>CRITICAL — System Down / Severe Bug</option>
            </select>
          </div>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-bold text-dark">Category <span class="text-danger">*</span></label>
            <select name="category" class="form-select" required style="border-radius: 8px;">
              @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-bold text-dark">Related Module (Optional)</label>
            <select name="related_module" class="form-select" style="border-radius: 8px;">
              <option value="">None / General</option>
              <option value="Payroll">Payroll &amp; Payslips</option>
              <option value="HR & Attendance">HR &amp; Attendance</option>
              <option value="Projects & Tasks">Projects &amp; Tasks</option>
              <option value="Subscription & Billing">Subscription &amp; Billing</option>
              <option value="User Accounts & Permissions">User Accounts &amp; Permissions</option>
              <option value="System Security">System Security</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold text-dark">Detailed Description <span class="text-danger">*</span></label>
          <textarea name="description" rows="5" class="form-control" placeholder="Please describe the issue in detail, including steps to reproduce or affected employees/records..." required style="border-radius: 8px;">{{ old('description') }}</textarea>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold text-dark">Attachment / Screenshot (Optional)</label>
          <input type="file" name="attachment" class="form-control" accept="image/*,.pdf,.docx,.xlsx" style="border-radius: 8px;" />
          <div class="form-text fs-7">Allowed file formats: PNG, JPG, WEBP, PDF, DOCX, XLSX (Max 5MB).</div>
        </div>

        <div class="d-flex justify-content-end gap-2 border-top pt-3">
          <a href="{{ route('admin.company-complaints.index') }}" class="btn btn-light border fw-bold px-4" style="border-radius: 8px;">Cancel</a>
          <button type="submit" class="btn btn-emerald px-4 fw-bold text-white shadow-sm" style="background: #10b981; border: none; border-radius: 8px;">
            <i class="bx bx-send me-1"></i> Submit Complaint
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

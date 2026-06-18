@extends('admin.layout.app')

@section('title', 'Government ID Verifications')

@section('content')
<main id="main" class="main">
    <div class="container-fluid">
        <div class="pagetitle d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1>Government ID Verifications</h1>
                <nav>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pending Verifications</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('dashboard') }}" class="btn-close" aria-label="Close"></a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="section">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Submitted DOB</th>
                                    <th>Uploaded ID</th>
                                    <th>OCR Detected DOB</th>
                                    <th>OCR Text</th>
                                    <th style="min-width: 260px;">Review</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($verifications as $verification)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $verification->user?->name ?? 'N/A' }}</div>
                                            <div class="text-muted small">{{ $verification->user?->email }}</div>
                                        </td>
                                        <td>{{ optional($verification->submitted_dob)->format('d M Y') ?? 'N/A' }}</td>
                                        <td>
                                            <a href="{{ asset($verification->image_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                View Image
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                {{ $verification->ocr_detected_dob ?: 'Not detected' }}
                                            </span>
                                            @if($verification->ocr_message)
                                                <div class="text-muted small mt-1">{{ $verification->ocr_message }}</div>
                                            @endif
                                        </td>
                                        <td style="min-width: 320px;">
                                            <textarea class="form-control form-control-sm" rows="5" readonly>{{ $verification->ocr_text ?: 'No OCR text captured.' }}</textarea>
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.government-id-verifications.approve', $verification) }}" class="mb-2">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-success btn-sm w-100">
                                                    Approve
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.government-id-verifications.reject', $verification) }}">
                                                @csrf
                                                @method('PATCH')
                                                <label class="form-label small text-muted" for="rejection_reason_{{ $verification->id }}">Rejection Reason</label>
                                                <textarea name="rejection_reason" id="rejection_reason_{{ $verification->id }}" class="form-control form-control-sm mb-2" rows="2" required></textarea>
                                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                                    Reject
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            No pending government ID verifications.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                {{ $verifications->links() }}
            </div>
        </section>
    </div>
</main>
@endsection

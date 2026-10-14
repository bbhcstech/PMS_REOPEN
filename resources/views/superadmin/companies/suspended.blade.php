@extends('layouts.superadmin')
@section('title', 'Suspended Companies')
@section('page_title', 'Suspended Companies')
@section('content')
@include('superadmin.companies.partials.suspension-styles')
<div class="company-suspension-page">
    <h2>Suspended Companies</h2>
    <p class="text-muted">Review the recorded reason, restore access, or permanently delete a suspended company.</p>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
    <div class="card p-3"><div class="table-responsive">
        <table class="table align-middle"><thead><tr><th>Company</th><th>Suspended on</th><th>Reason</th><th>Actions</th></tr></thead><tbody>
        @forelse($companies as $company)
        <tr data-company-id="{{ $company->id }}">
            <td><strong>{{ $company->name }}</strong><div class="text-muted">{{ $company->email }}</div></td>
            <td>{{ $company->suspended_at ?? 'Not recorded' }}</td>
            <td style="max-width:450px;overflow-wrap:anywhere"><strong>{{ $company->suspension_category ?? 'Legacy suspension' }}</strong><div>{{ $company->suspension_reason ?? 'No reason was recorded for this earlier suspension.' }}</div></td>
            <td><div class="d-flex flex-wrap gap-2">
                <form method="POST" action="{{ route('superadmin.companies.activate', $company->id) }}">@csrf<button class="btn btn-success btn-sm">Reactivate</button></form>
                <form method="POST" action="{{ route('superadmin.companies.delete', $company->id) }}" onsubmit="return confirm('Permanently delete this company and its database? This cannot be undone.');">@csrf @method('DELETE')<button class="btn btn-outline-danger btn-sm">Permanently delete</button></form>
                @if($company->suspension_reason && ($whatsapp = app(\App\Services\CompanySuspension::class)->whatsappUrl($company)))
                <a class="btn btn-outline-success btn-sm" href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer">Send reason on WhatsApp</a>
                @else<span class="small text-muted">WhatsApp needs a recorded reason and an international phone number.</span>@endif
            </div></td>
        </tr>
        @empty<tr><td colspan="4" class="text-center py-4">No suspended companies.</td></tr>@endforelse
        </tbody></table>
    </div>{{ $companies->links() }}</div>
</div>
@endsection

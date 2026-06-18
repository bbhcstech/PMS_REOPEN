@extends('admin.layout.app')

@section('title', $company->name)

@section('content')
@php $links = $company->social_links ?? []; @endphp
<div class="partner-page">
    <section class="partner-hero">
        <div>
            <span class="partner-eyebrow"><i class="fas fa-building"></i> Collaborating Company</span>
            <h1>{{ $company->name }}</h1>
            <p>{{ $company->industry ?: 'Company collaboration details' }}</p>
        </div>
        <div class="partner-actions">
            @if($isAdmin)
                <a href="{{ route('collaborating-companies.edit', $company) }}" class="btn btn-primary"><i class="fas fa-pen"></i> Edit</a>
            @endif
            <a href="{{ route('collaborating-companies.index') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </section>

    <section class="partner-detail-grid">
        <article class="partner-detail-card">
            <h2>Company Details</h2>
            <dl>
                <dt>Status</dt><dd><span class="partner-status {{ $company->status }}">{{ ucfirst($company->status) }}</span></dd>
                <dt>Industry</dt><dd>{{ $company->industry ?: 'Not specified' }}</dd>
                <dt>Collaboration</dt><dd>{{ $company->collaboration_type ?: 'Not specified' }}</dd>
                <dt>Started On</dt><dd>{{ $company->started_on?->format('d M Y') ?? 'Not specified' }}</dd>
                <dt>Contact</dt><dd>{{ $company->contact_person ?: 'Not specified' }}</dd>
                <dt>Email</dt><dd>{{ $company->contact_email ?: 'Not specified' }}</dd>
                <dt>Phone</dt><dd>{{ $company->contact_phone ?: 'Not specified' }}</dd>
            </dl>
        </article>

        <article class="partner-detail-card">
            <h2>Online Profiles</h2>
            <div class="partner-social-list">
                @foreach(['website' => ['Website', 'fas fa-globe'], 'linkedin' => ['LinkedIn', 'fab fa-linkedin'], 'facebook' => ['Facebook', 'fab fa-facebook'], 'instagram' => ['Instagram', 'fab fa-instagram'], 'x' => ['X / Twitter', 'fab fa-x-twitter'], 'youtube' => ['YouTube', 'fab fa-youtube']] as $key => [$label, $icon])
                    @php $url = $key === 'website' ? $company->website : ($links[$key] ?? null); @endphp
                    @if($url)
                        <a href="{{ $url }}" target="_blank" rel="noopener"><i class="{{ $icon }}"></i>{{ $label }}</a>
                    @endif
                @endforeach
                @if(! $company->website && empty($links))
                    <p class="partner-muted">No social media details added.</p>
                @endif
            </div>
        </article>
    </section>

    <section class="partner-detail-card">
        <h2>What They Do</h2>
        <p>{{ $company->description ?: 'No description added yet.' }}</p>
    </section>

    <section class="partner-detail-card">
        <h2>Services / Collaboration Details</h2>
        <p>{{ $company->services ?: 'No service details added yet.' }}</p>
    </section>

    @if($isAdmin && $company->notes)
        <section class="partner-detail-card">
            <h2>Internal Notes</h2>
            <p>{{ $company->notes }}</p>
        </section>
    @endif
</div>
@include('admin.collaborating-companies.styles')
@endsection

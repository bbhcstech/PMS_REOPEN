@extends('admin.layout.app')

@section('title', 'Collaborating Companies')

@section('content')
<div class="partner-page">
    <section class="partner-hero">
        <div>
            <span class="partner-eyebrow"><i class="fas fa-handshake"></i> Company Network</span>
            <h1>Collaborating Companies</h1>
            <p>{{ $isAdmin ? 'Add and maintain the companies collaborating with our organization.' : 'View the companies currently collaborating with our organization.' }}</p>
        </div>
        <div class="partner-actions">
            @if($isAdmin)
                <a href="{{ route('collaborating-companies.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Company</a>
            @endif
            <a href="{{ route('dashboard') }}" class="btn btn-light"><i class="fas fa-arrow-left"></i> Dashboard</a>
        </div>
    </section>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <section class="partner-stats">
        <div><span>Total</span><strong>{{ $stats['total'] }}</strong></div>
        <div><span>Active</span><strong>{{ $stats['active'] }}</strong></div>
        <div><span>Inactive</span><strong>{{ $isAdmin ? $stats['inactive'] : 0 }}</strong></div>
    </section>

    <section class="partner-filter">
        <form method="GET" action="{{ route('collaborating-companies.index') }}">
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search company, industry, services">
            @if($isAdmin)
                <select name="status" class="form-select">
                    <option value="all">All Status</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            @endif
            <button class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('collaborating-companies.index') }}" class="btn btn-light"><i class="fas fa-rotate-left"></i> Reset</a>
        </form>
    </section>

    <section class="partner-grid">
        @forelse($companies as $company)
            @php $links = $company->social_links ?? []; @endphp
            <article class="partner-card">
                <div class="partner-card-head">
                    <div class="partner-avatar">{{ strtoupper(mb_substr($company->name, 0, 1)) }}</div>
                    <div>
                        <h2>{{ $company->name }}</h2>
                        <p>{{ $company->industry ?: 'Industry not specified' }}</p>
                    </div>
                    <span class="partner-status {{ $company->status }}">{{ ucfirst($company->status) }}</span>
                </div>
                <div class="partner-meta">
                    <span><i class="fas fa-briefcase"></i>{{ $company->collaboration_type ?: 'Collaboration' }}</span>
                    @if($company->started_on)
                        <span><i class="fas fa-calendar"></i>{{ $company->started_on->format('d M Y') }}</span>
                    @endif
                </div>
                <p class="partner-description">{{ \Illuminate\Support\Str::limit($company->description ?: $company->services ?: 'No company details added yet.', 170) }}</p>
                <div class="partner-socials">
                    @foreach(['website' => 'globe', 'linkedin' => 'linkedin', 'facebook' => 'facebook', 'instagram' => 'instagram', 'x' => 'x-twitter', 'youtube' => 'youtube'] as $key => $icon)
                        @php $url = $key === 'website' ? $company->website : ($links[$key] ?? null); @endphp
                        @if($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener" title="{{ ucfirst($key) }}"><i class="fab fa-{{ $icon }}"></i></a>
                        @endif
                    @endforeach
                </div>
                <div class="partner-card-actions">
                    <a href="{{ route('collaborating-companies.show', $company) }}" class="btn btn-sm btn-light"><i class="fas fa-eye"></i> View</a>
                    @if($isAdmin)
                        <a href="{{ route('collaborating-companies.edit', $company) }}" class="btn btn-sm btn-primary"><i class="fas fa-pen"></i> Edit</a>
                        <form method="POST" action="{{ route('collaborating-companies.destroy', $company) }}" onsubmit="return confirm('Delete this collaborating company?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <div class="partner-empty">
                <i class="fas fa-building-circle-exclamation"></i>
                <h2>No collaborating companies found</h2>
                <p>{{ $isAdmin ? 'Add the first company to share it with employees.' : 'No active collaboration details are available yet.' }}</p>
            </div>
        @endforelse
    </section>

    <div class="partner-pagination">{{ $companies->links() }}</div>
</div>
@include('admin.collaborating-companies.styles')
@endsection

@extends('layouts.superadmin')
@section('title', 'Suspend Company')
@section('page_title', 'Suspend Company')
@section('content')
@include('superadmin.companies.partials.suspension-styles')
<div class="company-suspension-page" style="max-width:850px">
    <h2>Suspend {{ $company->name }}</h2>
    <p class="text-muted">Restrict company access while preserving its database and subscription dates.</p>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="card p-4">
        <form method="POST" action="{{ route('superadmin.companies.suspend', $company->id) }}">
            @csrf
            <label for="reason_category" class="form-label">Suspension reason</label>
            <select id="reason_category" name="reason_category" class="form-select mb-3" required>
                <option value="">Select a reason</option>
                @foreach($reasons as $reason)<option @selected(old('reason_category') === $reason)>{{ $reason }}</option>@endforeach
            </select>
            <label for="reason" class="form-label">Explain why this company is being suspended</label>
            <textarea id="reason" name="reason" class="form-control mb-3" rows="5" minlength="10" maxlength="2000" required>{{ old('reason') }}</textarea>
            <p class="text-muted">This explanation will be visible to the company and included in the prepared WhatsApp message.</p>
            <a class="btn btn-outline-secondary" href="{{ route('superadmin.companies.index') }}">Cancel</a>
            <button class="btn btn-danger" type="submit">Suspend company</button>
        </form>
    </div>
</div>
@endsection

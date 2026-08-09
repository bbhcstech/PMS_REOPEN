@extends('layouts.superadmin')

@section('title', 'Tenant Companies')

@section('content')
<div class="space-y-6">

    @if($currentCompanyDb)
        <div class="flex items-center justify-between rounded-2xl bg-amber-500/10 border border-amber-500/30 p-4 text-amber-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white font-bold">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold">Active Tenant Impersonation Session</p>
                    <p class="text-xs text-amber-700">Current session DB: <code class="font-mono bg-amber-200/60 px-1.5 py-0.5 rounded">{{ $currentCompanyDb }}</code></p>
                </div>
            </div>
            <form method="POST" action="{{ route('super-admin.leave-impersonation') }}">
                @csrf
                <button type="submit" class="rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-amber-700 transition">
                    Leave Impersonation
                </button>
            </form>
        </div>
    @endif

    <!-- Header Actions -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Registered Tenant Companies</h2>
            <p class="text-xs text-slate-500">Manage tenant databases, view details, or switch session context.</p>
        </div>
        <div>
            <a href="{{ route('super-admin.companies.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-600/20 hover:bg-cyan-500 transition">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create New Company
            </a>
        </div>
    </div>

    <!-- Companies Table Card -->
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <tr>
                        <th scope="col" class="px-6 py-4 font-bold">ID / Code</th>
                        <th scope="col" class="px-6 py-4 font-bold">Company Name</th>
                        <th scope="col" class="px-6 py-4 font-bold">Database Name</th>
                        <th scope="col" class="px-6 py-4 font-bold">Contact Email</th>
                        <th scope="col" class="px-6 py-4 font-bold">Status</th>
                        <th scope="col" class="px-6 py-4 font-bold">Created At</th>
                        <th scope="col" class="px-6 py-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($companies as $company)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="px-6 py-4 whitespace-nowrap font-mono text-xs font-semibold text-slate-500">
                                #{{ $company->id }}
                                @if($company->company_code)
                                    <span class="ml-1 inline-block rounded bg-slate-100 px-1.5 py-0.5 text-slate-700 font-bold">{{ $company->company_code }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $company->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-semibold text-cyan-700 bg-cyan-50 px-2.5 py-1 rounded-lg border border-cyan-200">
                                    {{ $company->db_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-600">
                                {{ $company->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($company->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active
                                    </span>
                                @elseif($company->status === 'trial')
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 border border-indigo-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span> Trial
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> {{ ucfirst($company->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                                {{ $company->created_at?->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <form method="POST" action="{{ route('super-admin.companies.enter', $company) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-slate-800 transition shadow-sm">
                                        <svg class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                        Enter Company
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                No tenant companies registered yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

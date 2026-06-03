@extends('layouts.superadmin')

@section('title', 'Super Admin Dashboard')

@section('content')
    <section class="space-y-6">
        <div class="rounded-lg bg-[linear-gradient(135deg,#152047_0%,#155e75_52%,#7c3aed_100%)] p-6 text-white shadow-sm">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wide text-cyan-100">Control Center</p>
                    <h2 class="mt-2 max-w-3xl text-3xl font-black leading-tight sm:text-4xl">Manage product companies, subscriptions, modules, billing, and admin access.</h2>
                    <p class="mt-3 max-w-2xl text-sm font-medium text-cyan-50">Use Company Admins for full admin account management with view, edit, archive, delete, pagination, and export.</p>
                </div>
                <a href="{{ route('superadmin.admins.index') }}" class="inline-flex items-center justify-center rounded-lg bg-white px-5 py-3 text-sm font-black text-[#152047] shadow-sm hover:bg-cyan-50">Open Company Admins</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Total Companies', $stats['companies'], 'from-cyan-500 to-blue-600', 'M3 21h18M5 21V5a2 2 0 012-2h7a2 2 0 012 2v16'],
                ['Active Companies', $stats['active_companies'], 'from-emerald-500 to-teal-600', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Expiring Soon', $stats['expiring_soon'], 'from-amber-500 to-orange-600', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Company Admins', $stats['company_admins'], 'from-fuchsia-500 to-violet-600', 'M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-4a4 4 0 100-8 4 4 0 000 8z'],
            ] as [$label, $value, $gradient, $path])
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
                            <p class="mt-2 text-3xl font-bold text-slate-950">{{ $value }}</p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br {{ $gradient }} text-white">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}" />
                            </svg>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section id="companies" class="mt-8 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Companies Management</h2>
                <p class="text-sm font-medium text-slate-500">Manage each client company and its assigned admin accounts.</p>
            </div>
            <a href="#create-company" class="inline-flex items-center justify-center rounded-lg bg-cyan-600 px-4 py-2 text-sm font-bold text-white hover:bg-cyan-700">Add Company</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-bold text-slate-500">Company</th>
                        <th class="px-6 py-3 text-left font-bold text-slate-500">Plan</th>
                        <th class="px-6 py-3 text-left font-bold text-slate-500">Status</th>
                        <th class="px-6 py-3 text-left font-bold text-slate-500">Admins</th>
                        <th class="px-6 py-3 text-left font-bold text-slate-500">Subscription</th>
                        <th class="px-6 py-3 text-right font-bold text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($companies as $company)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white">{{ strtoupper(substr($company->name, 0, 2)) }}</div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $company->name }}</p>
                                        <p class="text-xs font-medium text-slate-500">{{ $company->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-cyan-50 px-2.5 py-1 text-xs font-bold text-cyan-700">{{ $company->activeSubscription?->plan?->name ?? 'No plan' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClass = match($company->status) {
                                        'active' => 'bg-emerald-50 text-emerald-700',
                                        'trial' => 'bg-amber-50 text-amber-700',
                                        'suspended' => 'bg-rose-50 text-rose-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClass }}">{{ ucfirst($company->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $company->users->pluck('name')->join(', ') ?: 'Not assigned' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $company->activeSubscription?->ends_at?->format('M d, Y') ?? 'Not started' }}</td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ route('superadmin.companies.status', $company) }}" class="inline-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="rounded-lg border-slate-300 text-sm font-semibold">
                                        @foreach(['active', 'trial', 'suspended', 'inactive'] as $status)
                                            <option value="{{ $status }}" @selected($company->status === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-700">Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-sm font-semibold text-slate-500">No companies yet. Create the first company below.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-6 py-4">{{ $companies->links() }}</div>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-3">
        <form id="create-company" method="POST" action="{{ route('superadmin.companies.store') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
            @csrf
            <h2 class="text-lg font-bold text-slate-950">Create Company And Admin</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <input name="name" value="{{ old('name') }}" required placeholder="Company name" class="rounded-lg border-slate-300">
                <input name="email" value="{{ old('email') }}" required type="email" placeholder="Company email" class="rounded-lg border-slate-300">
                <input name="phone" value="{{ old('phone') }}" placeholder="Phone" class="rounded-lg border-slate-300">
                <input name="subdomain" value="{{ old('subdomain') }}" placeholder="Subdomain" class="rounded-lg border-slate-300">
                <select name="status" class="rounded-lg border-slate-300">
                    @foreach(['trial', 'active', 'suspended', 'inactive'] as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <select name="plan_id" class="rounded-lg border-slate-300">
                    <option value="">No plan yet</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} - ${{ $plan->monthly_price }}/mo</option>
                    @endforeach
                </select>
                <select name="billing_cycle" class="rounded-lg border-slate-300">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
                <input name="ends_at" type="date" value="{{ old('ends_at', now()->addMonth()->toDateString()) }}" class="rounded-lg border-slate-300">
                <input name="admin_name" value="{{ old('admin_name') }}" required placeholder="Admin name" class="rounded-lg border-slate-300">
                <input name="admin_email" value="{{ old('admin_email') }}" required type="email" placeholder="Admin email" class="rounded-lg border-slate-300">
                <input name="admin_password" required type="password" placeholder="Admin password" class="rounded-lg border-slate-300 md:col-span-2">
            </div>

            <div class="mt-5">
                <p class="mb-3 text-sm font-bold text-slate-700">Enable Modules</p>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($modules as $module)
                        <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="module_ids[]" value="{{ $module->id }}" class="rounded border-slate-300 text-cyan-600" @checked($module->is_core)>
                            {{ $module->name }}
                        </label>
                    @empty
                        <p class="text-sm font-medium text-slate-500">No modules created yet.</p>
                    @endforelse
                </div>
            </div>

            <button class="mt-6 rounded-lg bg-cyan-600 px-5 py-3 text-sm font-bold text-white hover:bg-cyan-700">Create Company</button>
        </form>

        <form method="POST" action="{{ route('superadmin.admins.store') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <h2 class="text-lg font-bold text-slate-950">Quick Add Company Admin</h2>
            <div class="mt-5 space-y-4">
                <select name="company_id" required class="w-full rounded-lg border-slate-300">
                    <option value="">Select company</option>
                    @foreach($companyOptions as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
                <input name="name" required placeholder="Admin name" class="w-full rounded-lg border-slate-300">
                <input name="email" required type="email" placeholder="Admin email" class="w-full rounded-lg border-slate-300">
                <input name="password" required type="password" placeholder="Admin password" class="w-full rounded-lg border-slate-300">
                <button class="w-full rounded-lg bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-700">Create Admin</button>
                <a href="{{ route('superadmin.admins.index') }}" class="block text-center text-sm font-bold text-cyan-700 hover:text-cyan-900">Manage all admins</a>
            </div>
        </form>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-2">
        <div id="plans-pricing" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-950">Plans & Pricing</h2>
            <div class="mt-5 space-y-3">
                @forelse($plans as $plan)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 p-4">
                        <div>
                            <p class="font-bold text-slate-900">{{ $plan->name }}</p>
                            <p class="text-xs font-medium text-slate-500">{{ $plan->max_users }} users, {{ $plan->max_projects }} projects</p>
                        </div>
                        <p class="font-bold text-slate-900">${{ $plan->monthly_price }}/mo</p>
                    </div>
                @empty
                    <p class="text-sm font-medium text-slate-500">No plans created yet.</p>
                @endforelse
            </div>
        </div>

        <div id="audit-logs" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-950">Recent Activity Logs</h2>
            <div class="mt-5 space-y-4">
                @forelse($recentActivities as $activity)
                    <div class="flex gap-3">
                        <div class="mt-1 h-2 w-2 rounded-full bg-cyan-500"></div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ str_replace('.', ' ', ucfirst($activity->action)) }}</p>
                            <p class="text-xs font-medium text-slate-500">{{ $activity->company?->name ?? 'System' }} - {{ $activity->created_at?->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm font-medium text-slate-500">No activity logs yet.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

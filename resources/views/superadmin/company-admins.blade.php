@extends('layouts.superadmin')

@section('title', 'Company Admins')

@section('content')
    <section class="space-y-6">
        <div class="overflow-hidden rounded-lg bg-[linear-gradient(135deg,#152047_0%,#155e75_48%,#7c3aed_100%)] text-white shadow-sm">
            <div class="grid gap-6 p-6 lg:grid-cols-[1fr_360px] lg:p-8">
                <div>
                    <p class="text-sm font-black uppercase tracking-wide text-cyan-100">Company Admin Control</p>
                    <h2 class="mt-3 max-w-3xl text-3xl font-black leading-tight sm:text-4xl">Create, review, edit, archive, restore, delete, and export every company admin.</h2>
                    <p class="mt-3 max-w-2xl text-sm font-medium text-cyan-50">This page is built for superadmin operations, with clean pagination and account-state controls.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#create-admin" class="rounded-lg bg-white px-5 py-3 text-sm font-black text-[#152047] hover:bg-cyan-50">Add Admin</a>
                        <a href="{{ route('superadmin.admins.export', request()->only(['admin_search', 'admin_status'])) }}" class="rounded-lg bg-white/15 px-5 py-3 text-sm font-black text-white ring-1 ring-white/20 hover:bg-white/25">Export Details</a>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['Total', $adminStats['total']],
                        ['Active', $adminStats['active']],
                        ['Archived', $adminStats['archived']],
                        ['Blocked', $adminStats['blocked']],
                    ] as [$label, $value])
                        <div class="rounded-lg bg-white/12 p-4 ring-1 ring-white/15">
                            <p class="text-xs font-bold uppercase text-cyan-100">{{ $label }}</p>
                            <p class="mt-2 text-3xl font-black">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-200 px-6 py-5 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Company Admin Details</h2>
                    <p class="text-sm font-medium text-slate-500">Use filters, entry count, next/previous pages, and account actions.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('superadmin.admins.index') }}" class="grid gap-4 border-b border-slate-200 bg-slate-50/70 px-6 py-4 md:grid-cols-[140px_1fr_160px_auto] md:items-end">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Show Entry</label>
                    <select name="admin_per_page" class="w-full rounded-lg border-slate-300 text-sm font-semibold">
                        @foreach([10, 20, 30, 40, 50] as $entry)
                            <option value="{{ $entry }}" @selected($adminPerPage === $entry)>{{ $entry }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Search</label>
                    <input name="admin_search" value="{{ $adminSearch }}" placeholder="Search admin, email, or company" class="w-full rounded-lg border-slate-300 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase text-slate-500">Status</label>
                    <select name="admin_status" class="w-full rounded-lg border-slate-300 text-sm font-semibold">
                        <option value="active" @selected($adminStatus === 'active')>Active</option>
                        <option value="archived" @selected($adminStatus === 'archived')>Archived</option>
                        <option value="all" @selected($adminStatus === 'all')>All</option>
                    </select>
                </div>
                <button class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-bold text-white hover:bg-slate-700">Apply</button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-6 py-4 text-left font-black text-slate-500">Admin</th>
                            <th class="px-6 py-4 text-left font-black text-slate-500">Company</th>
                            <th class="px-6 py-4 text-left font-black text-slate-500">Login</th>
                            <th class="px-6 py-4 text-left font-black text-slate-500">Status</th>
                            <th class="px-6 py-4 text-left font-black text-slate-500">Created</th>
                            <th class="px-6 py-4 text-right font-black text-slate-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($companyAdmins as $admin)
                            <tr x-data="{ viewOpen: false, editOpen: false }" class="hover:bg-cyan-50/40">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-500 to-violet-600 text-sm font-black text-white">{{ strtoupper(substr($admin->name, 0, 2)) }}</div>
                                        <div>
                                            <p class="font-black text-slate-900">{{ $admin->name }}</p>
                                            <p class="text-xs font-semibold text-slate-500">{{ $admin->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-bold text-slate-800">{{ $admin->company?->name ?? 'No company assigned' }}</p>
                                    <p class="text-xs font-medium text-slate-500">{{ $admin->company?->email }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-black {{ $admin->login_allowed ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $admin->login_allowed ? 'Allowed' : 'Blocked' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs font-black {{ $admin->archived_at ? 'bg-amber-50 text-amber-700' : 'bg-cyan-50 text-cyan-700' }}">
                                        {{ $admin->archived_at ? 'Archived' : 'Active' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $admin->created_at?->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <button type="button" @click="viewOpen = true" class="rounded-lg bg-cyan-50 px-3 py-2 text-xs font-black text-cyan-700 hover:bg-cyan-100">View</button>
                                        <button type="button" @click="editOpen = true" class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-black text-slate-700 hover:bg-slate-200">Edit</button>
                                        @if($admin->archived_at)
                                            <form method="POST" action="{{ route('superadmin.admins.restore', $admin) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700 hover:bg-emerald-100">Restore</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('superadmin.admins.archive', $admin) }}" onsubmit="return confirm('Archive this company admin? Login access will be blocked.');">
                                                @csrf
                                                @method('PATCH')
                                                <button class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-black text-amber-700 hover:bg-amber-100">Archive</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('superadmin.admins.delete', $admin) }}" onsubmit="return confirm('Delete this company admin permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg bg-rose-50 px-3 py-2 text-xs font-black text-rose-700 hover:bg-rose-100">Delete</button>
                                        </form>
                                    </div>

                                    <div x-cloak x-show="viewOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4">
                                        <div @click.outside="viewOpen = false" class="w-full max-w-lg overflow-hidden rounded-lg bg-white shadow-2xl">
                                            <div class="bg-[linear-gradient(135deg,#152047,#155e75)] p-6 text-white">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <h3 class="text-2xl font-black">{{ $admin->name }}</h3>
                                                        <p class="text-sm font-semibold text-cyan-100">{{ $admin->email }}</p>
                                                    </div>
                                                    <button type="button" @click="viewOpen = false" class="rounded-lg bg-white/10 px-3 py-2 text-sm font-black hover:bg-white/20">X</button>
                                                </div>
                                            </div>
                                            <div class="grid gap-4 p-6 sm:grid-cols-2">
                                                @foreach([
                                                    ['Company', $admin->company?->name ?? 'Not assigned'],
                                                    ['Company Email', $admin->company?->email ?? '-'],
                                                    ['Login Allowed', $admin->login_allowed ? 'Yes' : 'No'],
                                                    ['Status', $admin->archived_at ? 'Archived' : 'Active'],
                                                    ['Created', $admin->created_at?->format('M d, Y h:i A')],
                                                    ['Archived', $admin->archived_at?->format('M d, Y h:i A') ?? '-'],
                                                ] as [$label, $value])
                                                    <div class="rounded-lg bg-slate-50 p-4">
                                                        <p class="text-xs font-black uppercase text-slate-500">{{ $label }}</p>
                                                        <p class="mt-1 font-bold text-slate-900">{{ $value }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    <div x-cloak x-show="editOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4">
                                        <form method="POST" action="{{ route('superadmin.admins.update', $admin) }}" @click.outside="editOpen = false" class="w-full max-w-xl overflow-hidden rounded-lg bg-white shadow-2xl">
                                            @csrf
                                            @method('PATCH')
                                            <div class="bg-[linear-gradient(135deg,#152047,#7c3aed)] p-6 text-white">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <h3 class="text-2xl font-black">Edit Company Admin</h3>
                                                        <p class="text-sm font-semibold text-cyan-100">Update login details and company assignment.</p>
                                                    </div>
                                                    <button type="button" @click="editOpen = false" class="rounded-lg bg-white/10 px-3 py-2 text-sm font-black hover:bg-white/20">X</button>
                                                </div>
                                            </div>
                                            <div class="grid gap-4 p-6 sm:grid-cols-2">
                                                <select name="company_id" required class="rounded-lg border-slate-300 sm:col-span-2">
                                                    @foreach($companyOptions as $company)
                                                        <option value="{{ $company->id }}" @selected($admin->company_id === $company->id)>{{ $company->name }}</option>
                                                    @endforeach
                                                </select>
                                                <input name="name" value="{{ $admin->name }}" required placeholder="Admin name" class="rounded-lg border-slate-300">
                                                <input name="email" value="{{ $admin->email }}" required type="email" placeholder="Admin email" class="rounded-lg border-slate-300">
                                                <input name="password" type="password" placeholder="New password optional" class="rounded-lg border-slate-300 sm:col-span-2">
                                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                                                    <input type="hidden" name="login_allowed" value="0">
                                                    <input type="checkbox" name="login_allowed" value="1" class="rounded border-slate-300 text-cyan-600" @checked($admin->login_allowed)>
                                                    Login allowed
                                                </label>
                                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700">
                                                    <input type="hidden" name="email_notifications" value="0">
                                                    <input type="checkbox" name="email_notifications" value="1" class="rounded border-slate-300 text-cyan-600" @checked($admin->email_notifications)>
                                                    Email notifications
                                                </label>
                                                <button class="rounded-lg bg-cyan-600 px-5 py-3 text-sm font-black text-white hover:bg-cyan-700 sm:col-span-2">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm font-semibold text-slate-500">No company admins found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-semibold text-slate-500">
                    Showing {{ $companyAdmins->firstItem() ?? 0 }} to {{ $companyAdmins->lastItem() ?? 0 }} of {{ $companyAdmins->total() }} entries
                </p>
                <div>{{ $companyAdmins->onEachSide(1)->links() }}</div>
            </div>
        </section>

        <form id="create-admin" method="POST" action="{{ route('superadmin.admins.store') }}" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <h2 class="text-lg font-bold text-slate-950">Create New Company Admin</h2>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <select name="company_id" required class="rounded-lg border-slate-300 md:col-span-2">
                    <option value="">Select company</option>
                    @foreach($companyOptions as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
                <input name="name" required placeholder="Admin name" class="rounded-lg border-slate-300">
                <input name="email" required type="email" placeholder="Admin email" class="rounded-lg border-slate-300">
                <input name="password" required type="password" placeholder="Admin password" class="rounded-lg border-slate-300 md:col-span-2">
            </div>
            <button class="mt-5 rounded-lg bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-slate-700">Create Admin</button>
        </form>
    </section>
@endsection

@extends('layouts.superadmin')

@section('title', 'Create Tenant Company')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Provision New Tenant Company</h2>
            <p class="text-xs text-slate-500">Create a dedicated physical database and seed default company admin.</p>
        </div>
        <a href="{{ route('super-admin.companies.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
            &larr; Back to Companies
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
        <form method="POST" action="{{ route('super-admin.companies.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Company Information -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 mb-4">Company Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 mb-1">Company Name <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                               placeholder="e.g. Acme Corporation">
                    </div>

                    <div>
                        <label for="slug" class="block text-xs font-bold text-slate-700 mb-1">Slug / Code Identifier <span class="text-rose-500">*</span></label>
                        <div class="flex rounded-xl border border-slate-300 overflow-hidden focus-within:border-cyan-500 focus-within:ring-1 focus-within:ring-cyan-500">
                            <span class="inline-flex items-center bg-slate-100 px-3 text-xs font-mono text-slate-500 border-r border-slate-200">pms_</span>
                            <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required
                                   class="w-full px-3 py-2.5 text-sm border-0 focus:ring-0 font-mono"
                                   placeholder="acme">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Database will be created as <code class="font-mono">pms_&lt;slug&gt;</code></p>
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1">Company Email <span class="text-rose-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                               placeholder="contact@acme.com">
                    </div>

                    <div>
                        <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                               placeholder="+1 555-0199">
                    </div>
                </div>
            </div>

            <!-- Section 2: Default Admin User -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2 mb-4">Tenant Admin Account</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="admin_name" class="block text-xs font-bold text-slate-700 mb-1">Admin Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required
                               class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                               placeholder="Acme Admin">
                    </div>

                    <div>
                        <label for="admin_email" class="block text-xs font-bold text-slate-700 mb-1">Admin Login Email <span class="text-rose-500">*</span></label>
                        <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required
                               class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                               placeholder="admin@acme.com">
                    </div>

                    <div>
                        <label for="admin_password" class="block text-xs font-bold text-slate-700 mb-1">Admin Password <span class="text-rose-500">*</span></label>
                        <input type="password" id="admin_password" name="admin_password" required
                               class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-cyan-500 focus:ring-cyan-500"
                               placeholder="••••••••">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-6">
                <a href="{{ route('super-admin.companies.index') }}" class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 transition">
                    Cancel
                </a>
                <button type="submit" class="rounded-xl bg-cyan-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-600/20 hover:bg-cyan-500 transition">
                    Provision Company & Run Migrations
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

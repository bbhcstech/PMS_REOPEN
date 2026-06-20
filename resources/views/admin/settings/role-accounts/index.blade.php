@extends('admin.layout.app')

@section('title', $title)

@section('content')
<style>
    .role-account-form .form-control {
        min-width: 0;
    }

    .role-account-table {
        min-width: 760px;
    }

    .role-account-table .form-control {
        min-width: 150px;
    }

    .role-account-password-reset {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        justify-content: flex-end;
    }

    .role-account-password-reset .form-control {
        flex: 1 1 220px;
        max-width: 260px;
        min-width: 180px;
    }

    .role-account-password-reset .btn {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    @media (max-width: 767.98px) {
        .role-account-password-reset {
            justify-content: stretch;
        }

        .role-account-password-reset .form-control,
        .role-account-password-reset .btn {
            max-width: none;
            width: 100%;
        }
    }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold mb-4">{{ $title }}</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Create {{ ucfirst($role) }} Account</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.role-accounts.store', $role) }}" class="row g-3 role-account-form">
                @csrf
                <div class="col-lg-2 col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
                <div class="col-lg-3 col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
                <div class="col-lg-2 col-md-6">
                    <label class="form-label">Company</label>
                    <select name="company_id" class="form-select" required>
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6"><label class="form-label">Password</label><input name="password" type="password" class="form-control" required></div>
                <div class="col-lg-2 col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="login_allowed" value="1" checked id="loginAllowed"><label class="form-check-label" for="loginAllowed">Active</label></div></div>
                <div class="col-lg-1 col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Create</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table role-account-table">
                <thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Status</th><th>Password</th><th class="text-end">Actions</th></tr></thead>
                <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <form method="POST" action="{{ route('admin.role-accounts.update', [$role, $account]) }}">
                                @csrf
                                @method('PUT')
                                <td><input name="name" class="form-control" value="{{ $account->name }}" required></td>
                                <td><input name="email" type="email" class="form-control" value="{{ $account->email }}" required></td>
                                <td>
                                    <select name="company_id" class="form-select" required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $company)
                                            <option value="{{ $company->id }}" @selected($account->company_id == $company->id)>{{ $company->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="login_allowed" value="1" @checked($account->login_allowed)></div></td>
                                <td><input name="password" type="password" class="form-control" placeholder="Leave blank to keep"></td>
                                <td class="text-end"><button class="btn btn-sm btn-primary">Save</button></td>
                            </form>
                        </tr>
                        <tr>
                            <td colspan="6">
                                <form method="POST" action="{{ route('admin.role-accounts.reset-password', [$role, $account]) }}" class="role-account-password-reset">
                                    @csrf
                                    <input name="password" type="password" class="form-control" placeholder="New password" required>
                                    <button class="btn btn-sm btn-outline-primary">Reset Password</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No {{ $role }} accounts found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

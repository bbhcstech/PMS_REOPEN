@extends('admin.layout.app')

@section('title', 'Change Password & Staff Credentials')

@push('styles')
<style>
    .change-pwd-page {
        min-height: calc(100vh - 100px);
        padding: 2rem 1.75rem;
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 50%, #f4fbf7 100%);
        color: #0a2e1f;
    }

    .change-pwd-shell {
        position: relative;
        max-width: 1400px;
        margin: 0 auto;
    }

    .ambient-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(130px);
        pointer-events: none;
        z-index: 0;
    }

    .ambient-orb-1 {
        top: -100px;
        left: -100px;
        width: 400px;
        height: 400px;
        background: rgba(16, 185, 129, 0.15);
    }

    .ambient-orb-2 {
        bottom: -100px;
        right: -100px;
        width: 450px;
        height: 450px;
        background: rgba(5, 150, 105, 0.12);
    }

    .pwd-content-wrapper {
        position: relative;
        z-index: 10;
    }

    .breadcrumb-custom {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 12px;
    }

    .breadcrumb-custom a {
        color: #059669;
        text-decoration: none;
    }

    .pwd-header-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        padding: 1.75rem 2.25rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(16, 185, 129, 0.15);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .pwd-header-badge {
        width: 52px;
        height: 52px;
        border-radius: 18px;
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 8px 20px -4px rgba(5, 150, 105, 0.4);
    }

    .btn-back-settings {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.65rem 1.4rem;
        border-radius: 40px;
        background: #ffffff;
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: #059669;
        font-weight: 700;
        font-size: 0.88rem;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        transition: all 0.25s ease;
    }

    .btn-back-settings:hover {
        background: #ecfdf5;
        border-color: #059669;
        color: #047857;
        transform: translateX(-2px);
    }

    .pwd-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    @media (max-width: 1200px) {
        .pwd-stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .pwd-stats-grid { grid-template-columns: 1fr; }
    }

    .pwd-stat-card {
        background: #ffffff !important;
        backdrop-filter: blur(15px);
        border-radius: 22px !important;
        padding: 1.4rem 1.6rem !important;
        border: 1px solid rgba(16, 185, 129, 0.18) !important;
        box-shadow: 0 10px 30px -5px rgba(16, 185, 129, 0.07) !important;
        display: flex !important;
        align-items: center !important;
        gap: 1.25rem !important;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
        position: relative !important;
        overflow: hidden !important;
    }

    .pwd-stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #10b981, #059669);
    }

    .pwd-stat-icon {
        width: 52px !important;
        height: 52px !important;
        border-radius: 18px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 1.4rem !important;
        flex-shrink: 0 !important;
    }

    .pwd-stat-icon.total { background: linear-gradient(135deg, #10b981, #059669) !important; color: #fff !important; }
    .pwd-stat-icon.hr { background: linear-gradient(135deg, #0284c7, #0369a1) !important; color: #fff !important; }
    .pwd-stat-icon.manager { background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important; color: #fff !important; }
    .pwd-stat-icon.employee { background: linear-gradient(135deg, #f59e0b, #d97706) !important; color: #fff !important; }

    .pwd-stat-info h6 {
        font-size: 0.78rem !important;
        font-weight: 800 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 4px !important;
    }

    .pwd-stat-info h3 {
        font-size: 1.35rem !important;
        font-weight: 900 !important;
        color: #0f172a !important;
        margin: 0 !important;
    }

    .pwd-main-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border-radius: 28px;
        border: 1px solid rgba(16, 185, 129, 0.18);
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.08);
        overflow: hidden;
        margin-bottom: 2.5rem;
    }

    .pwd-card-header {
        padding: 1.5rem 2.25rem;
        border-bottom: 1px solid rgba(16, 185, 129, 0.12);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .pwd-card-avatar {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: linear-gradient(145deg, #d1fae5, #a7f3d0);
        color: #059669;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .btn-submit-pwd {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 40px;
        padding: 0.75rem 2rem;
        font-weight: 800;
        font-size: 0.95rem;
        box-shadow: 0 6px 20px -4px rgba(5, 150, 105, 0.4);
        transition: all 0.25s ease;
    }

    .btn-submit-pwd:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px -4px rgba(5, 150, 105, 0.5);
    }
</style>
@endpush

@section('content')
<div class="change-pwd-page">
    <div class="change-pwd-shell">
        <div class="ambient-orb ambient-orb-1"></div>
        <div class="ambient-orb ambient-orb-2"></div>

        <div class="pwd-content-wrapper">
            <!-- Breadcrumbs -->
            <div class="breadcrumb-custom">
                <a href="{{ route('admin.settings.index') }}"><i class="fas fa-cog"></i> Settings</a>
                <i class="fas fa-chevron-right text-xs"></i>
                <span class="text-emerald-700 fw-bold">Change Password</span>
            </div>

            <!-- Page Header Card -->
            <div class="pwd-header-card">
                <div class="d-flex align-items-center gap-3">
                    <div class="pwd-header-badge">
                        <i class="fas fa-key"></i>
                    </div>
                    <div>
                        <h1 class="fs-4 fw-bold text-dark mb-1">Staff Password Management</h1>
                        <p class="text-muted small mb-0">Change account passwords for HR, Managers, and Employees with security alert notifications and 1-minute auto-logout.</p>
                    </div>
                </div>

                <a href="{{ route('admin.settings.index') }}" class="btn-back-settings">
                    <i class="fas fa-arrow-left"></i> Back to Settings
                </a>
            </div>

            <!-- Alert Notifications -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" style="background: rgba(220, 252, 231, 0.95); color: #065f46; border-left: 5px solid #10b981 !important;" role="alert">
                    <i class="fas fa-check-circle fs-4 me-2"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm rounded-4 border-0" role="alert">
                    <i class="fas fa-exclamation-circle fs-4 me-2"></i>
                    <div>{{ session('error') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm rounded-4 border-0" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Summary Stats Grid (Admin Only) -->
            @if(strtolower(Auth::user()->role) === 'admin')
            <div class="pwd-stats-grid">
                <div class="pwd-stat-card">
                    <div class="pwd-stat-icon total"><i class="fas fa-users"></i></div>
                    <div class="pwd-stat-info">
                        <h6>Manageable Accounts</h6>
                        <h3>{{ $totalCount }} Accounts</h3>
                    </div>
                </div>
                <div class="pwd-stat-card">
                    <div class="pwd-stat-icon hr"><i class="fas fa-user-shield"></i></div>
                    <div class="pwd-stat-info">
                        <h6>HR Accounts</h6>
                        <h3>{{ $hrCount }} Active</h3>
                    </div>
                </div>
                <div class="pwd-stat-card">
                    <div class="pwd-stat-icon manager"><i class="fas fa-user-tie"></i></div>
                    <div class="pwd-stat-info">
                        <h6>Manager Accounts</h6>
                        <h3>{{ $managerCount }} Active</h3>
                    </div>
                </div>
                <div class="pwd-stat-card">
                    <div class="pwd-stat-icon employee"><i class="fas fa-id-badge"></i></div>
                    <div class="pwd-stat-info">
                        <h6>Employee Accounts</h6>
                        <h3>{{ $employeeCount }} Active</h3>
                    </div>
                </div>
            </div>
            @endif

            <!-- Change Own Password Section Card (Full Width Horizontal) -->
            <div class="pwd-main-card mb-4" id="changeOwnPasswordSection">
                <div class="pwd-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="pwd-card-avatar" style="background: linear-gradient(145deg, #e0e7ff, #c7d2fe); color: #4338ca;">
                            <i class="fas fa-user-lock"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark fs-5">Change My Own Password</h5>
                            <small class="text-muted">Update password for your logged-in account ({{ Auth::user()->name }} - {{ ucfirst(Auth::user()->role) }}).</small>
                        </div>
                    </div>
                    <span class="badge px-3 py-1.5 rounded-pill fw-bold" style="background: #eef2ff; color: #4f46e5; font-size: 0.82rem;">
                        <i class="fas fa-shield-alt me-1"></i> {{ ucfirst(Auth::user()->role) }} Account
                    </span>
                </div>

                <div class="p-4 p-md-5">
                    <form method="POST" action="{{ route('user.change-own-password') }}">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small"><i class="fas fa-lock me-1 text-emerald-600"></i> Current Password</label>
                                <input type="password" name="current_password" class="form-control rounded-4 py-2.5 @error('current_password') is-invalid @enderror" placeholder="Enter current password" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small"><i class="fas fa-key me-1 text-emerald-600"></i> New Password</label>
                                <input type="password" name="new_password" class="form-control rounded-4 py-2.5 @error('new_password') is-invalid @enderror" placeholder="Minimum 8 characters" required minlength="8">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold text-dark small"><i class="fas fa-check-double me-1 text-emerald-600"></i> Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control rounded-4 py-2.5" placeholder="Re-enter new password" required minlength="8">
                            </div>
                        </div>

                        <!-- Info Notice -->
                        <div class="alert alert-info border-0 rounded-4 mt-4 p-3.5 d-flex align-items-start gap-3" style="background: rgba(224, 242, 254, 0.95); color: #0369a1;">
                            <i class="fas fa-info-circle fs-3 text-info flex-shrink-0 mt-0.5"></i>
                            <div class="small">
                                <strong>Admin Notification Notice:</strong> Changing your account password will dispatch a view-only security notification strictly to the <strong>Admin Dashboard</strong>. The notification will inform the Admin that your password was updated and cannot be clicked to view confidential details.
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-submit-pwd" style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);">
                                <i class="fas fa-shield-halved me-1.5"></i> Change My Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if(in_array(strtolower(Auth::user()->role), ['admin', 'hr', 'manager'], true))
            <!-- Password Change Form Card -->
            <div class="pwd-main-card">
                <div class="pwd-card-header">
                    <div class="pwd-card-avatar">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark fs-5">Reset Staff Account Password</h5>
                        <small class="text-muted">Target user will receive a security pop-up alert and 1-minute countdown timer on their active session.</small>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    <form method="POST" action="{{ route('admin.settings.change-password.update') }}">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small"><i class="fas fa-user me-1 text-emerald-600"></i> Select Staff Member</label>
                                <select name="user_id" id="targetStaffSelect" class="form-select rounded-4 py-2.5" required>
                                    <option value="">-- Select HR, Manager, or Employee --</option>
                                    @foreach($staffUsers as $sUser)
                                        <option value="{{ $sUser->id }}" {{ old('user_id') == $sUser->id ? 'selected' : '' }}>
                                            {{ $sUser->name }} ({{ ucfirst($sUser->role) }} - {{ $sUser->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small"><i class="fas fa-key me-1 text-emerald-600"></i> New Password</label>
                                <input type="password" name="new_password" class="form-control rounded-4 py-2.5" placeholder="Minimum 8 characters" required minlength="8">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-dark small"><i class="fas fa-check-double me-1 text-emerald-600"></i> Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control rounded-4 py-2.5" placeholder="Re-enter password" required minlength="8">
                            </div>
                        </div>

                        <!-- Security Notice Box -->
                        <div class="alert alert-warning border-0 rounded-4 mt-4 p-3.5 d-flex align-items-start gap-3" style="background: rgba(254, 243, 199, 0.95); color: #92400e;">
                            <i class="fas fa-shield-halved fs-3 text-warning flex-shrink-0 mt-0.5"></i>
                            <div class="small">
                                <strong>Automatic Security Protocol:</strong> Upon submitting this form, the selected user's account password will be updated immediately. If the user is currently logged in, an on-screen alert will pop up informing them that their password was updated by {{ Auth::user()->role === 'admin' ? 'Admin' : (Auth::user()->role === 'hr' ? 'HR' : 'Manager') }}. A 1-minute (60 seconds) live countdown timer will force logout after expiration, and they will only be able to log back in using this new password.
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-submit-pwd">
                                <i class="fas fa-save me-1.5"></i> Update Password & Logout User
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Staff Accounts Quick Directory Table -->
            <div class="pwd-main-card">
                <div class="pwd-card-header">
                    <div class="pwd-card-avatar">
                        <i class="fas fa-list"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark fs-5">Staff Accounts Directory</h5>
                        <small class="text-muted">Quickly select any account to reset password.</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.92rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 px-4 fw-bold text-muted">Staff Name</th>
                                <th class="py-3 px-4 fw-bold text-muted">Email</th>
                                <th class="py-3 px-4 fw-bold text-muted">Role</th>
                                <th class="py-3 px-4 fw-bold text-muted">Last Password Change</th>
                                <th class="py-3 px-4 text-end fw-bold text-muted">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffUsers as $sUser)
                                <tr>
                                    <td class="py-3 px-4 fw-bold text-dark">
                                        <i class="fas fa-user-circle text-emerald-600 me-2"></i> {{ $sUser->name }}
                                    </td>
                                    <td class="py-3 px-4 text-muted">{{ $sUser->email }}</td>
                                    <td class="py-3 px-4">
                                        @if(strtolower($sUser->role) === 'admin')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">Admin</span>
                                        @elseif(strtolower($sUser->role) === 'hr')
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1">HR</span>
                                        @elseif(strtolower($sUser->role) === 'manager')
                                            <span class="badge bg-purple-subtle text-purple border border-purple-subtle rounded-pill px-3 py-1" style="background: #f3e8ff; color: #7e22ce;">Manager</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Employee</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-muted small">
                                        @if($sUser->password_changed_at)
                                            {{ $sUser->password_changed_at->format('M d, Y g:i A') }} (by {{ $sUser->password_changed_by_role ?? 'System' }})
                                        @else
                                            Original Credentials
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold" onclick="selectStaffForPasswordChange('{{ $sUser->id }}')">
                                            <i class="fas fa-key me-1"></i> Select to Reset
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No staff accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function selectStaffForPasswordChange(userId) {
    const select = document.getElementById('targetStaffSelect');
    if (select) {
        select.value = userId;
        select.scrollIntoView({ behavior: 'smooth', block: 'center' });
        select.focus();
    }
}
</script>
@endsection

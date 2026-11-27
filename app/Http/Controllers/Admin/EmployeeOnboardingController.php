<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    Company,
    Country,
    Department,
    Designation,
    EmployeeInvitation,
    EmployeeOnboardingApplication,
    ParentDepartment,
    User
};
use App\Services\{
    EmployeeCreationService,
    EmployeeOnboardingService,
    GovernmentIdDobVerifier,
    TenantScope
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Schema, Storage};
use Illuminate\Validation\ValidationException;

class EmployeeOnboardingController extends Controller
{
    public function __construct(
        private EmployeeOnboardingService $onboardingService,
        private EmployeeCreationService $creationService
    ) {}

    private function ensureReviewer(string $permission = 'view'): User
    {
        $user = auth()->user();
        $role = $user ? strtolower((string) ($user->role ?? '')) : '';
        abort_unless($user && in_array($role, ['admin', 'hr', 'administrator', 'superadmin'], true), 403, 'Unauthorized');

        if (! in_array($role, ['admin', 'administrator', 'superadmin'], true)) {
            abort_unless($user->hasModulePermission('employees', $permission), 403, 'You do not have permission to perform this action.');
        }

        return $user;
    }

    private function resolveCompanyId(?int $requestedId = null): int
    {
        return (int) $this->creationService->resolveCompany($requestedId)->id;
    }

    public function index(Request $request)
    {
        $this->ensureReviewer('view');
        $this->onboardingService->ensureSchema();

        $viewer = auth()->user();
        $isPlatformAdmin = TenantScope::isPlatformAdmin();
        $selectedCompanyId = $isPlatformAdmin
            ? ($request->integer('company_id') ?: null)
            : $this->resolveCompanyId();

        $baseQuery = EmployeeInvitation::query()
            ->with(['inviter', 'application.reviewer', 'application.approvedUser'])
            ->when($selectedCompanyId, fn ($q) => $q->where('company_id', $selectedCompanyId));

        $allForCounts = (clone $baseQuery)->get();
        $now = now();

        $counts = [
            'total_invitations' => $allForCounts->count(),
            'awaiting_submission' => $allForCounts->filter(fn (EmployeeInvitation $inv) => in_array($inv->effectiveStatus(), [EmployeeInvitation::STATUS_PENDING, EmployeeInvitation::STATUS_SENT], true))->count(),
            'pending_review' => $allForCounts->filter(fn (EmployeeInvitation $inv) => $inv->application?->status === EmployeeOnboardingApplication::STATUS_PENDING_REVIEW)->count(),
            'correction_requested' => $allForCounts->filter(fn (EmployeeInvitation $inv) => $inv->application?->status === EmployeeOnboardingApplication::STATUS_CORRECTION_REQUESTED)->count(),
            'approved' => $allForCounts->filter(fn (EmployeeInvitation $inv) => $inv->application?->status === EmployeeOnboardingApplication::STATUS_APPROVED)->count(),
            'rejected' => $allForCounts->filter(fn (EmployeeInvitation $inv) => $inv->application?->status === EmployeeOnboardingApplication::STATUS_REJECTED)->count(),
            'expired_invitations' => $allForCounts->filter(fn (EmployeeInvitation $inv) => $inv->effectiveStatus() === EmployeeInvitation::STATUS_EXPIRED)->count(),
        ];

        $query = clone $baseQuery;

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%' . $search . '%')
                    ->orWhereHas('application', function ($aq) use ($search) {
                        $aq->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('approved_employee_id', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($method = $request->input('method')) {
            if (in_array($method, [EmployeeInvitation::METHOD_EMAIL, EmployeeInvitation::METHOD_LINK], true)) {
                $query->where('invitation_method', $method);
            }
        }

        $statusFilter = $request->input('status');
        if ($statusFilter) {
            match ($statusFilter) {
                'awaiting' => $query->whereIn('status', [EmployeeInvitation::STATUS_PENDING, EmployeeInvitation::STATUS_SENT])
                    ->whereNull('submitted_at')
                    ->whereNull('revoked_at')
                    ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now)),
                'expired' => $query->where(function ($q) use ($now) {
                    $q->where('status', EmployeeInvitation::STATUS_EXPIRED)
                        ->orWhere(function ($sub) use ($now) {
                            $sub->whereIn('status', [EmployeeInvitation::STATUS_PENDING, EmployeeInvitation::STATUS_SENT])
                                ->whereNull('submitted_at')
                                ->whereNull('revoked_at')
                                ->whereNotNull('expires_at')
                                ->where('expires_at', '<=', $now);
                        });
                }),
                'revoked' => $query->where('status', EmployeeInvitation::STATUS_REVOKED),
                'pending_review' => $query->whereHas('application', fn ($q) => $q->where('status', EmployeeOnboardingApplication::STATUS_PENDING_REVIEW)),
                'correction_requested' => $query->whereHas('application', fn ($q) => $q->where('status', EmployeeOnboardingApplication::STATUS_CORRECTION_REQUESTED)),
                'approved' => $query->whereHas('application', fn ($q) => $q->where('status', EmployeeOnboardingApplication::STATUS_APPROVED)),
                'rejected' => $query->whereHas('application', fn ($q) => $q->where('status', EmployeeOnboardingApplication::STATUS_REJECTED)),
                default => null,
            };
        }

        $records = $query->orderByRaw('COALESCE(submitted_at, created_at) DESC')
            ->orderByDesc('id')
            ->paginate((int) ($request->input('per_page', 15)))
            ->appends($request->query());

        return view('admin.employees.onboarding.index', [
            'records' => $records,
            'counts' => $counts,
            'companies' => $isPlatformAdmin ? Company::where('status', 'active')->orderBy('name')->get() : collect(),
            'selectedCompanyId' => $selectedCompanyId,
            'isPlatformAdmin' => $isPlatformAdmin,
            'viewer' => $viewer,
        ]);
    }

    public function review(int $application)
    {
        $this->ensureReviewer('view');
        $this->onboardingService->ensureSchema();

        $appRecord = EmployeeOnboardingApplication::with(['invitation.inviter', 'audits', 'approvedUser'])->findOrFail($application);
        TenantScope::authorizeCompany($appRecord->company_id);

        $company = $this->creationService->resolveCompany((int) $appRecord->company_id);
        $nextEmployeeId = $this->creationService->computeNextEmployeeIdWithLock((int) $company->id);

        return view('admin.employees.onboarding.review', [
            'application' => $appRecord,
            'company' => $company,
            'companies' => collect([$company]),
            'designations' => Designation::withinLevelLimit()->orderBy('name')->get(),
            'departments' => Department::with('parent')
                ->when(Schema::hasColumn('departments', 'dpt_name'), fn ($q) => $q->orderBy('dpt_name'), fn ($q) => $q->orderBy('id'))
                ->get(),
            'prtdepartments' => ParentDepartment::when(Schema::hasColumn('parent_departments', 'dpt_name'), fn ($q) => $q->orderBy('dpt_name'), fn ($q) => $q->orderBy('id'))
                ->get(),
            'users' => User::whereIn('role', ['admin', 'manager', 'hr', 'employee', 'administrator', 'superadmin'])
                ->where('company_id', $company->id)
                ->whereNull('archived_at')
                ->orderBy('name')
                ->get(),
            'countries' => Country::forForms(),
            'nextEmployeeId' => $nextEmployeeId,
        ]);
    }

    public function approve(Request $request, int $application, GovernmentIdDobVerifier $dobVerifier)
    {
        $actor = $this->ensureReviewer('create');
        if (! in_array($actor->normalizedRole(), ['admin', 'administrator', 'superadmin'], true)) {
            $requestedRole = $request->input('user_role', $request->input('role', 'employee'));
            abort_unless($requestedRole === 'employee', 403, 'Only a company admin can assign privileged roles.');
        }

        $appRecord = EmployeeOnboardingApplication::with('invitation')->findOrFail($application);
        TenantScope::authorizeCompany($appRecord->company_id);

        try {
            $result = $this->onboardingService->approveApplication(
                $actor,
                (int) $appRecord->company_id,
                $appRecord,
                $request,
                $dobVerifier
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Could not approve onboarding application: ' . $e->getMessage()])->withInput();
        }

        $user = $result['user'];
        if (! $result['activation_email_sent']) {
            return redirect()
                ->route('employees.show', $user->id)
                ->with('warning', 'Employee ' . $user->name . ' was created, but the activation email could not be sent. You can resend it from Onboarding Applications.');
        }

        return redirect()
            ->route('employees.show', $user->id)
            ->with('success', 'Employee ' . $user->name . ' approved, created, and sent their welcome email.');
    }

    public function requestCorrection(Request $request, int $application)
    {
        $actor = $this->ensureReviewer('edit');
        $appRecord = EmployeeOnboardingApplication::with('invitation')->findOrFail($application);
        TenantScope::authorizeCompany($appRecord->company_id);

        $data = $request->validate([
            'correction_reason' => 'required|string|min:5|max:2000',
        ]);

        try {
            $this->onboardingService->requestCorrection($actor, (int) $appRecord->company_id, $appRecord, $data['correction_reason'], $request);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Could not send correction email. Check Settings > Email and try again.']);
        }

        return redirect()->route('employees.onboarding.index')->with('success', 'Correction request sent to ' . $appRecord->email . '.');
    }

    public function reject(Request $request, int $application)
    {
        $actor = $this->ensureReviewer('edit');
        $appRecord = EmployeeOnboardingApplication::with('invitation')->findOrFail($application);
        TenantScope::authorizeCompany($appRecord->company_id);

        $data = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:2000',
        ]);

        $this->onboardingService->rejectApplication($actor, (int) $appRecord->company_id, $appRecord, $data['rejection_reason'], $request);

        return redirect()->route('employees.onboarding.index')->with('success', 'Onboarding application for ' . $appRecord->name . ' has been rejected.');
    }

    public function previewDocument(Request $request, int $application, string $type)
    {
        $this->ensureReviewer('view');
        $appRecord = EmployeeOnboardingApplication::findOrFail($application);
        TenantScope::authorizeCompany($appRecord->company_id);

        $map = [
            'profile_picture' => ['profile_picture_path', 'profile_picture_name'],
            'cv_file' => ['cv_file_path', 'cv_file_name'],
            'government_id_card' => ['government_id_card_path', 'government_id_card_name'],
        ];
        abort_unless(isset($map[$type]), 404);

        [$pathCol, $nameCol] = $map[$type];
        $path = $appRecord->{$pathCol};
        $disk = $this->onboardingService->privateDisk();

        abort_unless($path && Storage::disk($disk)->exists($path), 404);

        $downloadName = basename((string) ($appRecord->{$nameCol} ?: $path));
        if ($request->boolean('download')) {
            return Storage::disk($disk)->download($path, $downloadName);
        }

        $fullPath = Storage::disk($disk)->path($path);
        $mime = Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . addslashes($downloadName) . '"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function resendInvitation(Request $request, int $invitation)
    {
        $actor = $this->ensureReviewer('create');
        $record = EmployeeInvitation::findOrFail($invitation);
        TenantScope::authorizeCompany($record->company_id);

        try {
            $result = $this->onboardingService->resendEmailInvitation($actor, (int) $record->company_id, $record, $request);

            return response()->json([
                'message' => 'Invitation resent to ' . $record->email . '.',
                'url' => $result['url'],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not resend invitation email. Check Settings > Email and try again.'], 500);
        }
    }

    public function regenerateLink(Request $request, int $invitation)
    {
        $actor = $this->ensureReviewer('create');
        $record = EmployeeInvitation::findOrFail($invitation);
        TenantScope::authorizeCompany($record->company_id);

        try {
            $result = $this->onboardingService->regenerateInvitationLink($actor, (int) $record->company_id, $record, $request);

            return response()->json([
                'message' => 'Invitation link regenerated cleanly. The previous link is now invalid.',
                'url' => $result['url'],
                'status' => $result['invitation']->effectiveStatus(),
                'expires_at' => $result['invitation']->expires_at?->format('d M Y, h:i A'),
                'invitation_id' => $result['invitation']->id,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        }
    }

    public function revokeInvitation(Request $request, int $invitation)
    {
        $actor = $this->ensureReviewer('edit');
        $record = EmployeeInvitation::findOrFail($invitation);
        TenantScope::authorizeCompany($record->company_id);

        try {
            $updated = $this->onboardingService->revokeInvitation($actor, (int) $record->company_id, $record, $request);

            return response()->json([
                'message' => 'Invitation revoked successfully.',
                'status' => $updated->effectiveStatus(),
            ]);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        }
    }

    public function copyLink(int $invitation)
    {
        $this->ensureReviewer('view');
        $record = EmployeeInvitation::findOrFail($invitation);
        TenantScope::authorizeCompany($record->company_id);

        $url = $record->isUsable() ? $record->onboardingUrl() : null;
        if (! $url) {
            return response()->json(['message' => 'This invitation has no active link. Regenerate or resend it to issue a fresh link.'], 422);
        }

        return response()->json(['url' => $url]);
    }

    public function resendActivation(int $application)
    {
        $this->ensureReviewer('edit');
        $appRecord = EmployeeOnboardingApplication::with('approvedUser')->findOrFail($application);
        TenantScope::authorizeCompany($appRecord->company_id);

        if ($appRecord->status !== EmployeeOnboardingApplication::STATUS_APPROVED || ! $appRecord->approvedUser) {
            return back()->withErrors(['error' => 'Only approved applications with an employee account can resend welcome emails.']);
        }

        $company = $this->onboardingService->resolveCompanyModel((int) $appRecord->company_id);
        abort_unless($company, 404);

        $sent = $this->onboardingService->sendApprovalEmail($company, $appRecord, $appRecord->approvedUser);

        return $sent
            ? back()->with('success', 'Welcome & activation email resent to ' . $appRecord->approvedUser->email . '.')
            : back()->withErrors(['error' => 'Activation email delivery failed again. Please verify Settings > Email.']);
    }
}

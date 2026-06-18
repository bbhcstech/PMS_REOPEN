<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GovernmentIdVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GovernmentIdVerificationController extends Controller
{
    public function index(): View
    {
        $verifications = GovernmentIdVerification::with(['user', 'employeeDetail'])
            ->where('verification_status', 'pending_admin_verification')
            ->latest()
            ->paginate(15);

        return view('admin.government-id-verifications.index', compact('verifications'));
    }

    public function approve(GovernmentIdVerification $verification): RedirectResponse
    {
        $verification->update([
            'verification_status' => 'approved',
            'rejection_reason' => null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->syncProfileStatus($verification, 'approved');

        return back()->with('success', 'Government ID verification approved.');
    }

    public function reject(Request $request, GovernmentIdVerification $verification): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $verification->update([
            'verification_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->syncProfileStatus($verification, 'rejected');

        return back()->with('success', 'Government ID verification rejected.');
    }

    private function syncProfileStatus(GovernmentIdVerification $verification, string $status): void
    {
        if ($verification->employeeDetail) {
            $verification->employeeDetail->forceFill([
                'government_id_verification_status' => $status,
            ])->save();

            return;
        }

        if ($verification->user) {
            $verification->user->forceFill([
                'government_id_verification_status' => $status,
            ])->save();
        }
    }
}

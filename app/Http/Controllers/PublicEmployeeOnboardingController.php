<?php

namespace App\Http\Controllers;

use App\Models\{Country, EmployeeInvitation, EmployeeOnboardingApplication};
use App\Services\EmployeeOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicEmployeeOnboardingController extends Controller
{
    public function __construct(
        private EmployeeOnboardingService $onboardingService
    ) {}

    public function show(string $token)
    {
        $resolved = $this->onboardingService->resolvePublicInvitation($token);
        $company = $resolved['company'];
        $invitation = $resolved['invitation'];

        if ($resolved['state'] !== 'valid' || ! $invitation || ! $company) {
            return response()->view('onboarding.employee-status', [
                'company' => $company,
                'state' => $resolved['state'],
                'message' => $resolved['message'],
                'application' => $invitation?->application,
            ], $resolved['state'] === 'submitted' ? 200 : 410);
        }

        return view('onboarding.employee-form', [
            'company' => $company,
            'invitation' => $invitation,
            'application' => null,
            'isCorrection' => false,
            'submitUrl' => route('employee-onboarding.submit', ['token' => $token]),
            'sendOtpUrl' => route('employee-onboarding.send-otp', ['token' => $token]),
            'verifyOtpUrl' => route('employee-onboarding.verify-otp', ['token' => $token]),
            'countries' => Country::forForms(),
        ]);
    }

    public function sendOtp(Request $request, string $token)
    {
        $resolved = $this->onboardingService->resolvePublicInvitation($token);
        $company = $resolved['company'];
        $invitation = $resolved['invitation'];

        if ($resolved['state'] !== 'valid' || ! $invitation || ! $company) {
            return response()->json(['message' => $resolved['message'] ?: 'This invitation link is invalid.'], 410);
        }

        $request->validate(['email' => 'required|email:rfc|max:191']);

        try {
            $this->onboardingService->sendVerificationOtp($invitation, $company, (string) $request->email);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Could not send verification code. Please check with HR or try again later.'], 500);
        }

        return response()->json(['message' => 'A 6-digit verification code has been sent to ' . $this->onboardingService->sanitizeEmail((string) $request->email) . '.']);
    }

    public function verifyOtp(Request $request, string $token)
    {
        $resolved = $this->onboardingService->resolvePublicInvitation($token);
        $invitation = $resolved['invitation'];

        if ($resolved['state'] !== 'valid' || ! $invitation) {
            return response()->json(['message' => $resolved['message'] ?: 'This invitation link is invalid.'], 410);
        }

        $request->validate([
            'email' => 'required|email:rfc|max:191',
            'otp' => 'required|digits:6',
        ]);

        if (! $this->onboardingService->verifyEmailOtp($invitation, (string) $request->email, (string) $request->otp)) {
            return response()->json(['message' => 'Invalid or expired verification code. Please request a new code.'], 422);
        }

        return response()->json([
            'verified' => true,
            'email' => $invitation->fresh()->email,
            'message' => 'Email ownership verified successfully.',
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $resolved = $this->onboardingService->resolvePublicInvitation($token);
        $company = $resolved['company'];
        $invitation = $resolved['invitation'];

        if ($resolved['state'] !== 'valid' || ! $invitation || ! $company) {
            return response()->view('onboarding.employee-status', [
                'company' => $company,
                'state' => $resolved['state'],
                'message' => $resolved['message'],
                'application' => $invitation?->application,
            ], 410);
        }

        $application = $this->onboardingService->submitPublicApplication($invitation, $company, $request);

        return response()->view('onboarding.employee-status', [
            'company' => $company,
            'state' => 'submitted_success',
            'message' => 'Your employee onboarding application has been submitted successfully. Our HR team will review your information and notify you once your employee account is approved.',
            'application' => $application,
        ]);
    }

    public function showCorrection(string $token)
    {
        $resolved = $this->onboardingService->resolveCorrectionApplication($token);
        $company = $resolved['company'];
        $application = $resolved['application'];

        if ($resolved['state'] !== 'valid' || ! $application || ! $company) {
            return response()->view('onboarding.employee-status', [
                'company' => $company,
                'state' => $resolved['state'],
                'message' => $resolved['message'],
                'application' => $application,
            ], 410);
        }

        return view('onboarding.employee-form', [
            'company' => $company,
            'invitation' => $application->invitation,
            'application' => $application,
            'isCorrection' => true,
            'submitUrl' => route('employee-onboarding.correction.submit', ['token' => $token]),
            'sendOtpUrl' => null,
            'verifyOtpUrl' => null,
            'countries' => Country::forForms(),
        ]);
    }

    public function submitCorrection(Request $request, string $token)
    {
        $resolved = $this->onboardingService->resolveCorrectionApplication($token);
        $company = $resolved['company'];
        $application = $resolved['application'];

        if ($resolved['state'] !== 'valid' || ! $application || ! $company) {
            return response()->view('onboarding.employee-status', [
                'company' => $company,
                'state' => $resolved['state'],
                'message' => $resolved['message'],
                'application' => $application,
            ], 410);
        }

        $updated = $this->onboardingService->resubmitCorrectionApplication($application, $company, $request);

        return response()->view('onboarding.employee-status', [
            'company' => $company,
            'state' => 'submitted_success',
            'message' => 'Your corrected onboarding application has been resubmitted successfully and returned to HR for review.',
            'application' => $updated,
        ]);
    }
}

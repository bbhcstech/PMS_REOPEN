<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Services\{EmployeeCreationService, EmployeeOnboardingService};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait EmployeeInvitations
{
    /**
     * Send an employee self-onboarding invitation via email.
     */
    public function sendInvite(Request $request): JsonResponse
    {
        $this->ensureAdmin();
        $actor = auth()->user();
        if (! in_array($actor?->normalizedRole(), ['admin', 'administrator', 'superadmin'], true)) {
            abort_unless($actor?->hasModulePermission('employees', 'create'), 403, 'You do not have permission to invite employees.');
        }

        $service = app(EmployeeOnboardingService::class);
        $rawEmail = (string) $request->input('email', '');
        $request->merge(['email' => $service->sanitizeEmail($rawEmail)]);

        $request->validate([
            'email' => 'required|email:rfc|max:191',
            'message' => 'nullable|string|max:1000',
            'company_id' => 'nullable|integer',
        ]);

        $company = app(EmployeeCreationService::class)->resolveCompany($request->integer('company_id') ?: null, $actor);

        try {
            $result = $service->sendEmailInvitation(
                $actor,
                (int) $company->id,
                (string) $request->email,
                $request->input('message'),
                $request
            );
            $invitation = $result['invitation'];

            return response()->json([
                'message' => 'Onboarding invitation sent to ' . $invitation->email . '. Valid for ' . $service->expiryHours() . ' hours.',
                'invitation_id' => $invitation->id,
                'status' => $invitation->effectiveStatus(),
                'expires_at' => $invitation->expires_at?->format('d M Y, h:i A'),
                'url' => $result['url'],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Employee onboarding email invite failed', [
                'company_id' => $company->id,
                'exception' => get_class($e),
            ]);

            return response()->json([
                'message' => 'The invitation email could not be sent, so no active invite was created. Check Settings > Email and try again.',
            ], 500);
        }
    }

    /**
     * Generate a single-use employee self-onboarding link (email-bound or open with OTP verification).
     */
    public function generateInviteLink(Request $request): JsonResponse
    {
        $this->ensureAdmin();
        $actor = auth()->user();
        if (! in_array($actor?->normalizedRole(), ['admin', 'administrator', 'superadmin'], true)) {
            abort_unless($actor?->hasModulePermission('employees', 'create'), 403, 'You do not have permission to generate invitation links.');
        }

        $service = app(EmployeeOnboardingService::class);
        $rawEmail = trim((string) $request->input('email', ''));
        if ($rawEmail !== '') {
            $request->merge(['email' => $service->sanitizeEmail($rawEmail)]);
            $request->validate(['email' => 'required|email:rfc|max:191']);
        } else {
            $request->merge(['email' => null]);
        }

        $company = app(EmployeeCreationService::class)->resolveCompany($request->integer('company_id') ?: null, $actor);

        try {
            $result = $service->createLinkInvitation(
                $actor,
                (int) $company->id,
                $request->input('email'),
                $request
            );
            $invitation = $result['invitation'];

            return response()->json([
                'url' => $result['url'],
                'invitation_id' => $invitation->id,
                'status' => $invitation->effectiveStatus(),
                'email_bound' => ! empty($invitation->email),
                'expires_at' => $invitation->expires_at?->format('d M Y, h:i A'),
                'expires_in_hours' => $service->expiryHours(),
                'message' => ! empty($invitation->email)
                    ? 'Single-use invitation link created for ' . $invitation->email . '.'
                    : 'Single-use invitation link created. Applicant email will be verified via OTP before submission.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Employee invite link generation failed', [
                'company_id' => $company->id,
                'exception' => get_class($e),
            ]);

            return response()->json([
                'message' => 'The invitation link could not be generated. Please try again.',
            ], 500);
        }
    }
}

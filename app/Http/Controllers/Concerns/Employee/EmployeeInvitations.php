<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Models\EmployeeDetail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

trait EmployeeInvitations
{
    /**
     * Send employee invite.
     */
    public function sendInvite(Request $request): JsonResponse
    {
        return $this->createInvitation($request, true);
    }

    public function generateInviteLink(Request $request): JsonResponse
    {
        return $this->createInvitation($request, false);
    }

    private function createInvitation(Request $request, bool $sendEmail): JsonResponse
    {
        $this->ensureAdmin();

        // Sanitize email
        $rawEmail = (string) $request->input('email', '');
        $sanitized = trim($rawEmail);
        $sanitized = preg_replace('/^(www\.|mailto:)/i', '', $sanitized);
        $request->merge(['email' => strtolower(trim($sanitized))]);

        $request->validate([
            'email' => 'required|email:rfc',
            'message' => 'nullable|string|max:1000'
        ]);

        if ($sendEmail) {
            app(\App\Services\CompanyMailSettings::class)->requireDelivery();
        }

        $companyId = (int) (\App\Services\TenantScope::companyId() ?: auth()->user()?->company_id);
        $company = $companyId ? \App\Models\Central\Company::on('central')->find($companyId) : null;
        if (! $company) {
            return response()->json(['message' => 'Your company workspace could not be determined. Please sign in again.'], 422);
        }

        $email = $request->email;
        $existing = User::query()->withoutGlobalScopes()->where('email', $email)->first();
        if ($existing && (int) $existing->company_id !== $companyId) {
            return response()->json(['message' => 'This email address is already used by another account.'], 422);
        }
        if ($existing && (! $sendEmail || $existing->role !== 'employee' || $existing->archived_at)) {
            return response()->json(['message' => 'This account already exists. Use a new employee email address for an invitation link.'], 422);
        }

        DB::connection('tenant')->beginTransaction();
        try {
            $user = $existing ?: User::create([
                'name' => Str::before($email, '@'),
                'email' => $email,
                'company_id' => $companyId,
                'password' => Hash::make(Str::random(32)),
                'role' => 'employee',
                'login_allowed' => 1,
            ]);
            if (! $user->company_id) {
                $user->forceFill(['company_id' => $companyId])->save();
            }

            if (! $user->employeeDetail) {
                EmployeeDetail::create([
                    'user_id' => $user->id,
                    'status' => 'Active',
                    'business_address' => '',
                    'employee_id' => $this->computeNextEmployeeIdWithLock()
                ]);
            }

            $inviteUrl = \App\Http\Controllers\EmployeeInviteController::inviteUrl($user, $companyId);

            // Email failures roll back the account; manual links do not need SMTP.
            if ($sendEmail) {
                Mail::to($user->email)->send(new \App\Mail\EmployeeInvite($user, $request->message, $inviteUrl, $company->name));
            }

            DB::connection('tenant')->commit();
            Log::info('Employee invitation created', ['company_id' => $companyId, 'delivery' => $sendEmail ? 'email' : 'link']);

            if (! $sendEmail) {
                return response()->json(['message' => 'Invitation link created. Share it with '.$user->email.'.', 'url' => $inviteUrl]);
            }

            return response()->json(['message' => 'Invitation sent to ' . $user->email . '. The link is valid for ' . \App\Http\Controllers\EmployeeInviteController::VALID_DAYS . ' days.']);
        } catch (\Throwable $e) {
            DB::connection('tenant')->rollBack();
            Log::error('Employee invite failed', ['company_id' => $companyId, 'exception' => get_class($e)]);

            return response()->json([
                'message' => $sendEmail
                    ? 'The invitation email could not be sent, so no invite was created. Check Settings > Email and try again.'
                    : 'The invitation link could not be created. No invite was saved. Please try again.',
            ], 500);
        }
    }
}

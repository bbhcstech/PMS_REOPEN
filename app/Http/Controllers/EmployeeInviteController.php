<?php

namespace App\Http\Controllers;

use App\Models\Central\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Hash, URL};
use Illuminate\Validation\Rules\Password;

/**
 * Employee invitations: the emailed link is signed, tied to one company workspace and one user,
 * and stops working as soon as the invitee has set a password (single use).
 */
class EmployeeInviteController extends Controller
{
    public const VALID_DAYS = 7;

    /** Signed link that is emailed to the invitee. */
    public static function inviteUrl(User $user, int $companyId): string
    {
        return URL::temporarySignedRoute('employee-invite.show', now()->addDays(self::VALID_DAYS), [
            'company' => $companyId,
            'user' => $user->id,
            'v' => self::fingerprint($user),
        ]);
    }

    /** Changes when the password changes, so a used (or superseded) link no longer validates. */
    public static function fingerprint(User $user): string
    {
        return substr(hash_hmac('sha256', (string) $user->getAuthPassword() . '|' . $user->id, (string) config('app.key')), 0, 20);
    }

    public function show(Request $request, int $company, int $user)
    {
        [$companyModel, $invitee] = $this->resolve($request, $company, $user);
        if (! $invitee) {
            return response()->view('auth.employee-invite-accept', ['invalid' => true, 'company' => $companyModel], 410);
        }

        $submitUrl = URL::temporarySignedRoute('employee-invite.complete', now()->addHours(2), [
            'company' => $companyModel->id,
            'user' => $invitee->id,
            'v' => self::fingerprint($invitee),
        ]);

        return view('auth.employee-invite-accept', [
            'invalid' => false,
            'company' => $companyModel,
            'user' => $invitee,
            'submitUrl' => $submitUrl,
        ]);
    }

    public function complete(Request $request, int $company, int $user)
    {
        [$companyModel, $invitee] = $this->resolve($request, $company, $user);
        if (! $invitee) {
            return response()->view('auth.employee-invite-accept', ['invalid' => true, 'company' => $companyModel], 410);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        DB::connection('tenant')->transaction(function () use ($invitee, $data) {
            $invitee->forceFill([
                'name' => $data['name'],
                'password' => Hash::make($data['password']),
                'login_allowed' => 1,
            ])->save();
            if ($detail = $invitee->employeeDetail) {
                $detail->status = 'Active';
                $detail->save();
            }
        });

        // Sign in to this company workspace, exactly as the regular login does.
        $request->session()->regenerate();
        $request->session()->put([
            'current_company_id' => $companyModel->id,
            'current_company_db' => $companyModel->db_name,
            'current_company_name' => $companyModel->name,
        ]);
        Auth::guard('web')->login($invitee);

        return redirect()->route('dashboard')->with('success', 'Welcome to ' . $companyModel->name . '! Your account is ready.');
    }

    /** @return array{0: ?Company, 1: ?User} */
    private function resolve(Request $request, int $companyId, int $userId): array
    {
        $company = Company::on('central')->whereNotNull('db_name')->find($companyId);
        if (! $company) {
            abort(404);
        }
        $this->useCompanyDatabase($company->db_name);

        $user = User::query()->withoutGlobalScopes()->where('company_id', $company->id)->find($userId);
        if (! $user || ! hash_equals(self::fingerprint($user), (string) $request->query('v', ''))) {
            return [$company, null];
        }

        return [$company, $user];
    }

    private function useCompanyDatabase(string $database): void
    {
        config([
            'database.connections.tenant.database' => $database,
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.url' => null,
        ]);
        DB::purge('tenant');
        DB::purge('mysql');
    }
}

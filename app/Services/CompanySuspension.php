<?php
namespace App\Services;
use App\Models\Central\{Company, Subscription, CentralNotification};
use Illuminate\Support\Facades\DB;

class CompanySuspension
{
    public const REASONS = ['Data leak', 'Security incident', 'Fraud or misuse', 'Policy violation', 'Payment dispute', 'Legal or compliance concern', 'Other'];

    public function suspend(int $id, array $data): Company
    {
        abort_unless(TenantScope::isPlatformAdmin(), 403);
        $data['reason'] = trim((string) ($data['reason'] ?? ''));
        validator($data, ['reason_category' => ['required', \Illuminate\Validation\Rule::in(self::REASONS)],
            'reason' => 'required|string|min:10|max:2000'])->validate();
        return DB::connection('central')->transaction(function () use ($id, $data) {
            $company = Company::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_if($company->manually_suspended, 409, 'This company is already suspended.');
            $company->status = 'suspended';
            $company->manually_suspended = true;
            $company->suspended_at = now();
            $company->suspension_category = $data['reason_category'];
            $company->suspension_reason = trim($data['reason']);
            $company->suspended_by = (auth('super_admin')->user() ?? auth()->user())?->id;
            $company->save();
            Subscription::where('company_id', $id)->where('status', 'active')->update(['status' => 'suspended']);
            $this->notify($company, 'COMPANY_SUSPENDED', 'Company access suspended', $this->message($company));
            return $company;
        });
    }

    public function reactivate(int $id): Company
    {
        abort_unless(TenantScope::isPlatformAdmin(), 403);
        return DB::connection('central')->transaction(function () use ($id) {
            $company = Company::whereKey($id)->lockForUpdate()->firstOrFail();
            abort_unless($company->manually_suspended || $company->status === 'suspended', 409, 'This company is not suspended.');
            $company->manually_suspended = false;
            $company->suspended_at = null;
            $company->status = 'active';
            // Clear the manual status before evaluating the remaining subscription term.
            $company->save();
            $company->status = app(SubscriptionService::class)->evaluateCompanyStatus($company);
            $company->save();
            Subscription::where('company_id', $id)->where('status', 'suspended')
                ->where('ends_at', '>=', now())->update(['status' => 'active']);
            Subscription::where('company_id', $id)->where('status', 'suspended')
                ->where('ends_at', '<', now())->update(['status' => 'expired']);
            $this->notify($company, 'COMPANY_REACTIVATED', 'Company suspension lifted', 'Super Admin lifted your company suspension. Subscription dates remain unchanged. Current status: ' . $company->status . '.');
            return $company;
        });
    }

    public function message(Company $company): string
    {
        return "Company {$company->name} has been suspended by Super Admin. Reason: {$company->suspension_category}. {$company->suspension_reason} Contact platform support for review. Your company data has been retained.";
    }

    public function whatsappUrl(Company $company): ?string
    {
        $rawPhone = trim((string) $company->phone);
        if (!str_starts_with($rawPhone, '+') && !str_starts_with($rawPhone, '00')) return null;
        $phone = preg_replace('/[^0-9]/', '', $rawPhone);
        if (str_starts_with($rawPhone, '00')) $phone = substr($phone, 2);
        // A complete international number is required; never guess a country code.
        return strlen($phone) >= 8 && strlen($phone) <= 15
            ? 'https://wa.me/' . $phone . '?text=' . rawurlencode($this->message($company)) : null;
    }

    private function notify(Company $company, string $type, string $title, string $message): void
    {
        CentralNotification::createNotification(['company_id' => $company->id, 'type' => $type,
            'title' => $title, 'message' => $message, 'severity' => $type === 'COMPANY_SUSPENDED' ? 'WARNING' : 'INFO',
            'related_module' => 'Companies', 'related_record_id' => (string) $company->id,
            'action_url' => route('subscription.suspended'), 'target_audience' => 'company_admin']);
    }
}

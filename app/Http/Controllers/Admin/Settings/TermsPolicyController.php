<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TermsPolicyController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(): View
    {
        $this->ensureDefaults();

        return view('admin.settings.terms-policy.index', [
            'title' => AppSetting::valueFor('legal_terms_title', 'Terms & Conditions'),
            'content' => AppSetting::valueFor('legal_terms_content', $this->defaultContent()),
            'effectiveDate' => AppSetting::valueFor('legal_terms_effective_date'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'legal_terms_title' => ['required', 'string', 'max:255'],
            'legal_terms_content' => ['required', 'string', 'min:20'],
            'legal_terms_effective_date' => ['nullable', 'date'],
        ]);

        $this->saveSetting('legal_terms_title', 'Terms Page Title', $validated['legal_terms_title'], 'text', 10);
        $this->saveSetting('legal_terms_content', 'Terms & Conditions Content', $validated['legal_terms_content'], 'textarea', 20);
        $this->saveSetting('legal_terms_effective_date', 'Effective Date', $validated['legal_terms_effective_date'] ?? null, 'date', 30);

        return back()->with('success', 'Terms & Conditions updated successfully.');
    }

    private function ensureDefaults(): void
    {
        $this->createSettingIfMissing('legal_terms_title', 'Terms Page Title', 'Terms & Conditions', 'text', 10);
        $this->createSettingIfMissing('legal_terms_content', 'Terms & Conditions Content', $this->defaultContent(), 'textarea', 20);
        $this->createSettingIfMissing('legal_terms_effective_date', 'Effective Date', now()->toDateString(), 'date', 30);
    }

    private function saveSetting(string $key, string $label, ?string $value, string $type, int $sortOrder): void
    {
        AppSetting::updateOrCreate(
            ['key' => $key],
            [
                'label' => $label,
                'description' => 'Shown on the public Terms & Conditions page and linked from login.',
                'value' => $value,
                'type' => $type,
                'section' => 'Legal Policy',
                'page' => 'terms-policy',
                'sort_order' => $sortOrder,
            ]
        );
    }

    private function createSettingIfMissing(string $key, string $label, ?string $value, string $type, int $sortOrder): void
    {
        AppSetting::firstOrCreate(
            ['key' => $key],
            [
                'label' => $label,
                'description' => 'Shown on the public Terms & Conditions page and linked from login.',
                'value' => $value,
                'type' => $type,
                'section' => 'Legal Policy',
                'page' => 'terms-policy',
                'sort_order' => $sortOrder,
            ]
        );
    }

    private function defaultContent(): string
    {
        return "These Terms & Conditions explain the expected use of Bitroxia PMS for organization users.\n\nUsers must access the system only with their assigned account, keep login credentials confidential, and follow company policies while using project, HR, attendance, payroll, client, ticket, and reporting modules.\n\nThe organization may update these terms when policies, workflows, or compliance requirements change. Continued use of the system means the user accepts the latest published terms.";
    }
}

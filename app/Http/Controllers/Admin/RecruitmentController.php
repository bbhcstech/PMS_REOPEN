<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\BusinessAddress;
use App\Models\CompanySetting;
use App\Models\Department;
use App\Models\ParentDepartment;
use App\Models\RecruitmentBrandingAsset;
use App\Models\RecruitmentRequirement;
use App\Services\SystemNotificationService;
use App\Services\TenantScope;
use App\Support\RecruitmentPdfBranding;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RecruitmentController extends Controller
{
    public function index(Request $request)
    {
        $query = RecruitmentRequirement::with(['department', 'creator'])
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('department_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $requirements = $query->get();
        $departments = \Illuminate\Support\Facades\Schema::hasColumn('departments', 'dpt_name')
            ? Department::orderBy('dpt_name')->get()
            : Department::get();

        $parentQuery = ParentDepartment::query();
        if (\Illuminate\Support\Facades\Schema::hasColumn('parent_departments', 'archived_at')) {
            $parentQuery->whereNull('archived_at');
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('parent_departments', 'dpt_name')) {
            $parentQuery->orderBy('dpt_name');
        }
        $parentDepartments = $parentQuery->get();

        // Metrics Calculation
        $totalOpen = RecruitmentRequirement::where('status', 'open')->count();
        $totalInProgress = RecruitmentRequirement::where('status', 'in_progress')->count();
        $totalPositionsOpen = RecruitmentRequirement::whereIn('status', ['open', 'in_progress'])->sum('positions');
        $totalClosed = RecruitmentRequirement::where('status', 'closed')->count();

        // Auto-Generated Recruitment Policy Card Data
        $policyCard = $this->generatePolicyCard();

        $currencies = \Illuminate\Support\Facades\Schema::hasTable('currencies')
            ? \App\Models\Currency::all()
            : collect();
        $defaultCurrencySymbol = AppSetting::valueFor('loc_currency_symbol', '$');
        $companyLocations = $this->companyLocations();

        $brandingAssets = collect();
        if ($request->user()?->role === 'admin' && ($companyId = TenantScope::companyId())
            && Schema::connection('tenant')->hasTable('recruitment_branding_assets')) {
            $brandingAssets = RecruitmentBrandingAsset::where('company_id', $companyId)
                ->orderBy('name')->orderByDesc('id')->get(['id', 'kind', 'name'])->groupBy('kind');
        }

        return view('admin.recruitment.index', compact(
            'requirements',
            'departments',
            'parentDepartments',
            'totalOpen',
            'totalInProgress',
            'totalPositionsOpen',
            'totalClosed',
            'policyCard',
            'currencies',
            'defaultCurrencySymbol',
            'companyLocations',
            'brandingAssets'
        ));
    }

    /**
     * Store a new company location (BusinessAddress) inline from the recruitment modal.
     */
    public function storeLocation(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. Only Admin can add company locations.',
            ], 403);
        }

        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:1000',
        ]);

        $locationName = trim($validated['location']);
        $branchName = isset($validated['branch_name']) ? trim($validated['branch_name']) : '';
        $branchName = $branchName !== '' ? $branchName : null;

        $conn = (new BusinessAddress())->getConnectionName() ?: config('database.default');
        if (! \Illuminate\Support\Facades\Schema::connection($conn)->hasTable('business_addresses')) {
            return response()->json([
                'success' => false,
                'message' => 'Company locations table is not available.',
            ], 500);
        }

        $hasCompanyId = \Illuminate\Support\Facades\Schema::connection($conn)->hasColumn('business_addresses', 'company_id');
        $companyId = auth()->user()?->company_id;

        $existingQuery = BusinessAddress::query()
            ->whereRaw('LOWER(location) = ?', [mb_strtolower($locationName)]);
        if ($hasCompanyId && $companyId) {
            $existingQuery->where('company_id', $companyId);
        }
        if ($branchName !== null) {
            $existingQuery->whereRaw('LOWER(COALESCE(branch_name, "")) = ?', [mb_strtolower($branchName)]);
        } else {
            $existingQuery->where(function ($q) {
                $q->whereNull('branch_name')->orWhere('branch_name', '');
            });
        }

        $addressRecord = $existingQuery->first();
        $created = false;

        if (! $addressRecord) {
            $defaultCountry = BusinessAddress::query()
                ->when($hasCompanyId && $companyId, fn ($q) => $q->where('company_id', $companyId))
                ->where('is_default', true)
                ->value('country') ?: 'India';

            $country = trim((string) ($validated['country'] ?? ''));
            $streetAddress = trim((string) ($validated['address'] ?? ''));

            $addressRecord = new BusinessAddress([
                'branch_name' => $branchName,
                'location' => $locationName,
                'address' => $streetAddress !== '' ? $streetAddress : $locationName,
                'country' => $country !== '' ? $country : $defaultCountry,
                'is_default' => BusinessAddress::query()
                    ->when($hasCompanyId && $companyId, fn ($q) => $q->where('company_id', $companyId))
                    ->count() === 0,
            ]);
            if ($hasCompanyId && $companyId) {
                $addressRecord->setAttribute('company_id', $companyId);
            }
            $addressRecord->save();
            $created = true;
        }

        $option = $this->formatLocationOption($addressRecord, $this->companyLocations());

        return response()->json([
            'success' => true,
            'message' => $created ? 'Location added successfully.' : 'Location already exists and was selected.',
            'location' => $option,
        ], $created ? 201 : 200);
    }

    public function store(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            return back()->with('error', 'Unauthorized action. Only Admin can create recruitment requirements.');
        }

        $companyId = TenantScope::companyId();
        abort_unless($companyId, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|integer',
            'positions' => 'required|integer|min:1',
            'employment_type' => 'required|string|max:100',
            'experience_required' => 'nullable|string|max:100',
            // Salary Range (CTC) in LPA: numbers only, up to 2 decimals; max may not be below min.
            'salary_min' => ['nullable', 'numeric', 'min:0', 'max:999', 'regex:/^\d+(\.\d{1,2})?$/', 'required_with:salary_max'],
            'salary_max' => ['nullable', 'numeric', 'min:0', 'max:999', 'regex:/^\d+(\.\d{1,2})?$/', 'gte:salary_min'],
            'location' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'requirements_summary' => 'nullable|string',
        ] + RecruitmentPdfBranding::selectionRules($companyId), RecruitmentPdfBranding::messages() + [
            'salary_min.numeric' => 'Enter the minimum CTC as a number in LPA (e.g. 4.5).',
            'salary_min.regex' => 'Enter the minimum CTC as a number in LPA (e.g. 4.5).',
            'salary_min.required_with' => 'Enter the minimum CTC as well as the maximum.',
            'salary_max.numeric' => 'Enter the maximum CTC as a number in LPA (e.g. 6).',
            'salary_max.regex' => 'Enter the maximum CTC as a number in LPA (e.g. 6).',
            'salary_max.gte' => 'The maximum CTC cannot be lower than the minimum.',
        ]);

        $departmentName = null;
        if ($request->filled('department_id')) {
            $dept = Department::find($request->department_id);
            $departmentName = $dept?->dpt_name;
        }

        $salaryRange = $this->ctcRange($request->input('salary_min'), $request->input('salary_max'));

        $user = auth()->user();

        $attributes = [
            'company_id' => $companyId,
            'title' => $request->title,
            'department_id' => $request->department_id,
            'department_name' => $departmentName ?? $request->department_name ?? 'General',
            'positions' => $request->positions,
            'employment_type' => $request->employment_type,
            'experience_required' => $request->experience_required,
            'salary_range' => $salaryRange,
            'location' => $request->location ?? 'Headquarters',
            'description' => $request->description,
            'requirements_summary' => $request->requirements_summary,
            'status' => 'open',
            'created_by' => $user?->id,
        ];

        $requirement = DB::connection('tenant')->transaction(function () use ($validated, $attributes, $companyId) {
            foreach (array_keys(RecruitmentPdfBranding::HEIGHTS) as $kind) {
                $column = 'pdf_'.$kind.'_asset_id';
                $upload = $validated['pdf_'.$kind.'_image'] ?? null;
                $attributes[$column] = $upload
                    ? $this->storePdfBrandingImage($companyId, $kind, $upload, $validated['pdf_'.$kind.'_name'] ?? null)
                    : $validated[$column];
            }

            return RecruitmentRequirement::create($attributes);
        }, 3);

        // SHARE NOTIFICATION WITH ALL EMPLOYEES, MANAGERS, AND HR STAFF
        $title = "📢 New Job Requirement: {$requirement->title}";
        $deptText = $requirement->department_name ? " in {$requirement->department_name}" : '';
        $message = "A new requirement for \"{$requirement->title}\" ({$requirement->positions} position(s)){$deptText} has been published by {$user?->name}.";
        $url = route('recruitment.index');

        try {
            SystemNotificationService::notifyAllRoles($title, $message, $url, [
                'type' => 'recruitment_requirement',
                'requirement_id' => $requirement->id,
                'icon' => 'fa-briefcase',
                'color' => 'success',
            ], $user?->company_id);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed sending recruitment notification: ' . $e->getMessage());
        }

        return redirect()->route('recruitment.index')
            ->with('success', "Requirement created successfully! Notification shared with all employees, managers, and HR.");
    }

    public function show($id)
    {
        $requirement = RecruitmentRequirement::with(['department', 'creator'])->findOrFail($id);
        $policyCard = $this->generatePolicyCard();

        return view('admin.recruitment.show', compact('requirement', 'policyCard'));
    }

    public function brandingPreview(int $asset)
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
        $companyId = TenantScope::companyId();
        abort_unless($companyId, 403);

        $image = RecruitmentBrandingAsset::where('company_id', $companyId)->findOrFail($asset);

        return response(base64_decode($image->image_data, true), 200, [
            'Content-Type' => $image->mime_type,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download($id)
    {
        $requirement = RecruitmentRequirement::with(['department', 'creator'])->findOrFail($id);
        $policyCard = $this->generatePolicyCard();
        $fileName = 'Job_Requirement_' . \Illuminate\Support\Str::slug($requirement->title) . '.pdf';

        // Single-page sheet built with pdfkit + pdf-lib; falls back to the DomPDF template below.
        try {
            $bytes = app(\App\Services\RecruitmentPdfRenderer::class)->render($requirement, $policyCard);
            return response($bytes, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'private, no-store',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Recruitment PDF (pdfkit/pdf-lib) unavailable, using DomPDF: ' . $e->getMessage());
        }

        try {
            $pdf = \App\Services\PdfDocument::loadView('admin.recruitment.pdf', compact('requirement', 'policyCard'))
                ->setPaper('a4', 'portrait')
                ->setWarnings(false);

            $fileName = 'Job_Requirement_' . \Illuminate\Support\Str::slug($requirement->title) . '.pdf';

            return $pdf->download($fileName);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PDF generation failed for recruitment requirement: ' . $e->getMessage());
            // Fallback response header for html print/download
            return response()->view('admin.recruitment.pdf', compact('requirement', 'policyCard'))
                ->header('Content-Type', 'text/html');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        if (auth()->user()?->role !== 'admin') {
            return back()->with('error', 'Unauthorized action. Only Admin can update recruitment requirements.');
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,closed,cancelled',
        ]);

        $requirement = RecruitmentRequirement::findOrFail($id);
        $requirement->update(['status' => $request->status]);

        return redirect()->route('recruitment.index')
            ->with('success', "Requirement status updated to " . ucfirst(str_replace('_', ' ', $request->status)) . ".");
    }

    public function destroy($id)
    {
        if (auth()->user()?->role !== 'admin') {
            return back()->with('error', 'Unauthorized action. Only Admin can delete recruitment requirements.');
        }

        $requirement = RecruitmentRequirement::findOrFail($id);
        $requirement->delete();

        return redirect()->route('recruitment.index')
            ->with('success', "Requirement removed successfully.");
    }

    /**
     * Collect distinct company locations configured via Branches / Locations (and fallback company profile location).
     */
    private function companyLocations(): \Illuminate\Support\Collection
    {
        $locations = collect();
        $conn = (new BusinessAddress())->getConnectionName() ?: config('database.default');
        $companyId = auth()->user()?->company_id;

        if (\Illuminate\Support\Facades\Schema::connection($conn)->hasTable('business_addresses')) {
            $query = BusinessAddress::query();
            if ($companyId && \Illuminate\Support\Facades\Schema::connection($conn)->hasColumn('business_addresses', 'company_id')) {
                $query->where('company_id', $companyId);
            }
            $addresses = $query->orderByDesc('is_default')->orderBy('location')->get();
            $locationCounts = $addresses
                ->map(fn ($a) => mb_strtolower(trim((string) ($a->location ?? ''))))
                ->filter()
                ->countBy();

            foreach ($addresses as $addr) {
                $loc = trim((string) ($addr->location ?? ''));
                $branch = trim((string) ($addr->branch_name ?? ''));
                $base = $loc !== '' ? $loc : $branch;
                if ($base === '') {
                    continue;
                }
                $hasBranchDiff = $branch !== '' && strcasecmp($branch, $loc) !== 0;
                $label = $hasBranchDiff ? "{$branch} ({$loc})" : $base;
                $isDuplicateCity = $loc !== '' && ($locationCounts->get(mb_strtolower($loc), 0) > 1);
                $value = ($hasBranchDiff && $isDuplicateCity) ? "{$branch} - {$loc}" : $base;

                $locations->push([
                    'id' => $addr->id,
                    'value' => $value,
                    'label' => $label,
                    'is_default' => (bool) $addr->is_default,
                ]);
            }
        }

        $csConn = (new CompanySetting())->getConnectionName() ?: config('database.default');
        if (\Illuminate\Support\Facades\Schema::connection($csConn)->hasTable('company_settings')) {
            $companyLocation = trim((string) (CompanySetting::query()->value('company_location') ?? ''));
            if ($companyLocation !== '') {
                $exists = $locations->contains(fn ($item) => strcasecmp($item['value'], $companyLocation) === 0);
                if (! $exists) {
                    $locations->push([
                        'id' => null,
                        'value' => $companyLocation,
                        'label' => $companyLocation,
                        'is_default' => $locations->isEmpty(),
                    ]);
                }
            }
        }

        return $locations->unique(fn ($item) => mb_strtolower($item['value']))->values();
    }

    /**
     * Format a single BusinessAddress into the dropdown option payload.
     */
    private function formatLocationOption(BusinessAddress $addr, \Illuminate\Support\Collection $allLocations): array
    {
        $matched = $allLocations->firstWhere('id', $addr->id);
        if ($matched) {
            return $matched;
        }

        $loc = trim((string) ($addr->location ?? ''));
        $branch = trim((string) ($addr->branch_name ?? ''));
        $base = $loc !== '' ? $loc : $branch;
        $hasBranchDiff = $branch !== '' && strcasecmp($branch, $loc) !== 0;

        return [
            'id' => $addr->id,
            'value' => $base,
            'label' => $hasBranchDiff ? "{$branch} ({$loc})" : $base,
            'is_default' => (bool) $addr->is_default,
        ];
    }

    /** "4.5 - 6 LPA" (or "5 LPA" for a single figure); null when no CTC was given. */
    private function ctcRange($min, $max): ?string
    {
        $format = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
        $min = ($min === null || $min === '') ? null : $format($min);
        $max = ($max === null || $max === '') ? null : $format($max);
        if ($min === null) return null;
        return ($max === null || $max === $min ? $min : $min . ' - ' . $max) . ' LPA';
    }

    private function storePdfBrandingImage(int $companyId, string $kind, UploadedFile $file, ?string $name): int
    {
        $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $defaultName = Str::of($filename)->replaceMatches('/[_-]+/u', ' ')->squish()->limit(100, '')->trim()->toString();
        $name = Str::squish($name ?? '');
        $image = $file->get();

        return RecruitmentBrandingAsset::query()->select('id')->lockForUpdate()->firstOrCreate([
            'company_id' => $companyId,
            'kind' => $kind,
            'name' => $name !== '' ? $name : ($defaultName !== '' ? $defaultName : 'Company '.$kind),
            'sha256' => hash('sha256', $image),
        ], [
            'mime_type' => $file->getMimeType(),
            'image_data' => base64_encode($image),
        ])->id;
    }

    /**
     * Auto-Generates structured Recruitment Policy Card details dynamically.
     */
    private function generatePolicyCard(): array
    {
        $jobCategories = AppSetting::valueFor('recruit_job_categories', 'Engineering, Design, Marketing, Sales, HR, Customer Support');
        $pipelineStages = AppSetting::valueFor('recruit_pipeline_stages', 'Applied, Screening, Technical Assessment, HR Interview, Final Offer, Onboarding');
        $autoReply = AppSetting::valueFor('recruit_auto_reply', '1');
        $maxResumeSize = AppSetting::valueFor('recruit_max_resume_size', '5');
        $allowedFileTypes = AppSetting::valueFor('recruit_allowed_file_types', 'pdf,doc,docx');
        
        $probationMonths = AppSetting::valueFor('recruit_probation_period_months', '3');
        $slaDays = AppSetting::valueFor('recruit_hiring_sla_days', '30');

        $probationStr = AppSetting::valueFor('recruit_probation_period', $probationMonths . ' Months (' . ($probationMonths * 30) . ' Days standard evaluation)');
        $hiringSlaStr = AppSetting::valueFor('recruit_hiring_sla', $slaDays . ' Days from Requirement posting to Offer');

        return [
            'title' => 'Standard Corporate Recruitment & Talent Acquisition Policy',
            'code' => 'POL-REC-' . date('Y'),
            'generated_at' => now()->format('M d, Y'),
            'status' => 'Auto-Generated & Active',
            'probation_period' => $probationStr,
            'hiring_sla' => $hiringSlaStr,
            'job_categories' => explode(',', $jobCategories),
            'pipeline_stages' => explode(',', $pipelineStages),
            'auto_reply_enabled' => $autoReply === '1',
            'max_resume_size' => $maxResumeSize . ' MB',
            'allowed_file_types' => strtoupper((string) $allowedFileTypes),
            'equal_opportunity' => 'This organization provides equal employment opportunities (EEO) to all employees and applicants for employment without regard to race, color, religion, sex, national origin, age, disability, or genetics.',
            'referral_policy' => 'Employees are eligible for a Referral Bonus upon successful placement and completion of the candidate\'s probation period.',
            'background_check' => 'Mandatory background check, identity verification, and employment reference check prior to formal onboarding.',
        ];
    }
}

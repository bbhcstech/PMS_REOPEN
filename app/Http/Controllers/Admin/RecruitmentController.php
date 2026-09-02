<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Department;
use App\Models\ParentDepartment;
use App\Models\RecruitmentRequirement;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;

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
        $departments = Department::orderBy('dpt_name')->get();
        $parentDepartments = ParentDepartment::whereNull('archived_at')->orderBy('dpt_name')->get();

        // Metrics Calculation
        $totalOpen = RecruitmentRequirement::where('status', 'open')->count();
        $totalInProgress = RecruitmentRequirement::where('status', 'in_progress')->count();
        $totalPositionsOpen = RecruitmentRequirement::whereIn('status', ['open', 'in_progress'])->sum('positions');
        $totalClosed = RecruitmentRequirement::where('status', 'closed')->count();

        // Auto-Generated Recruitment Policy Card Data
        $policyCard = $this->generatePolicyCard();

        return view('admin.recruitment.index', compact(
            'requirements',
            'departments',
            'parentDepartments',
            'totalOpen',
            'totalInProgress',
            'totalPositionsOpen',
            'totalClosed',
            'policyCard'
        ));
    }

    public function store(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            return back()->with('error', 'Unauthorized action. Only Admin can create recruitment requirements.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'department_id' => 'nullable|integer',
            'positions' => 'required|integer|min:1',
            'employment_type' => 'required|string|max:100',
            'experience_required' => 'nullable|string|max:100',
            'salary_range' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:150',
            'description' => 'nullable|string',
            'requirements_summary' => 'nullable|string',
        ]);

        $departmentName = null;
        if ($request->filled('department_id')) {
            $dept = Department::find($request->department_id);
            $departmentName = $dept?->dpt_name;
        }

        $user = auth()->user();

        $requirement = RecruitmentRequirement::create([
            'company_id' => $user?->company_id,
            'title' => $request->title,
            'department_id' => $request->department_id,
            'department_name' => $departmentName ?? $request->department_name ?? 'General',
            'positions' => $request->positions,
            'employment_type' => $request->employment_type,
            'experience_required' => $request->experience_required,
            'salary_range' => $request->salary_range,
            'location' => $request->location ?? 'Headquarters',
            'description' => $request->description,
            'requirements_summary' => $request->requirements_summary,
            'status' => 'open',
            'created_by' => $user?->id,
        ]);

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

    public function download($id)
    {
        $requirement = RecruitmentRequirement::with(['department', 'creator'])->findOrFail($id);
        $policyCard = $this->generatePolicyCard();

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.recruitment.pdf', compact('requirement', 'policyCard'))
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
     * Auto-Generates structured Recruitment Policy Card details dynamically.
     */
    private function generatePolicyCard(): array
    {
        $jobCategories = AppSetting::valueFor('recruit_job_categories', 'Engineering, Design, Marketing, Sales, HR, Customer Support');
        $pipelineStages = AppSetting::valueFor('recruit_pipeline_stages', 'Applied, Screening, Technical Assessment, HR Interview, Final Offer, Onboarding');
        $autoReply = AppSetting::valueFor('recruit_auto_reply', '1');
        $maxResumeSize = AppSetting::valueFor('recruit_max_resume_size', '5');
        $allowedFileTypes = AppSetting::valueFor('recruit_allowed_file_types', 'pdf,doc,docx');

        return [
            'title' => 'Standard Corporate Recruitment & Talent Acquisition Policy',
            'code' => 'POL-REC-' . date('Y'),
            'generated_at' => now()->format('M d, Y'),
            'status' => 'Auto-Generated & Active',
            'probation_period' => '90 Days (3 Months standard evaluation)',
            'hiring_sla' => '14 - 30 Business Days from Requirement posting to Offer',
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

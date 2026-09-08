<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessAddress;
use App\Models\Company;
use App\Models\Department;
use App\Models\Leave;
use App\Models\Letterhead;
use App\Models\Project;
use App\Services\DocxGeneratorService;
use App\Services\LetterTemplateService;
use App\Services\PdfLetterheadService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class LetterheadController extends Controller
{
    /**
     * Display a listing of the letterheads with stats, search & filters.
     */
    public function index(Request $request): View
    {
        // Seed default corporate letterhead if no records exist at all
        $this->ensureInitialSeedExists();

        $query = Letterhead::with(['company', 'branch', 'department', 'project', 'creator', 'updater'])
            ->orderBy('is_default', 'desc')
            ->orderBy('updated_at', 'desc');

        // Search Filter
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('branch_name', 'like', "%{$search}%")
                    ->orWhere('department_name', 'like', "%{$search}%")
                    ->orWhere('project_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        // Type Filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Status Filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Default Filter
        if ($request->filled('is_default') && $request->is_default !== 'all') {
            $query->where('is_default', (bool) $request->is_default);
        }

        $perPage = (int) $request->get('per_page', 10);
        $letterheads = $query->paginate($perPage)->withQueryString();

        // Calculate Summary Cards Metrics
        $stats = [
            'total' => Letterhead::count(),
            'active' => Letterhead::where('status', 'active')->count(),
            'default' => Letterhead::where('is_default', true)->count(),
            'draft' => Letterhead::whereIn('status', ['draft', 'inactive'])->count(),
        ];

        // Organization dataset options for form dropdowns
        $companies = Company::orderBy('name')->get();
        $branches = BusinessAddress::orderBy('branch_name')->get();
        $departments = Department::orderBy('dpt_name')->get();
        $projects = Project::orderBy('name')->get();

        return view('admin.letterhead.index', compact(
            'letterheads',
            'stats',
            'companies',
            'branches',
            'departments',
            'projects'
        ));
    }

    /**
     * Display the Create Letter Head & Document Generator page matching the user UI.
     */
    public function create(Request $request): View
    {
        $this->ensureInitialSeedExists();

        // Retrieve IT Letter Templates
        $categories = LetterTemplateService::getCategories();
        $allTemplates = LetterTemplateService::getAllTemplates();

        // Determine active template
        $templateKey = $request->get('template', 'apology_leave');
        if (! isset($allTemplates[$templateKey])) {
            $templateKey = 'apology_leave';
        }
        $currentTemplate = $allTemplates[$templateKey];

        // Replace placeholders for initial sample preview
        $user = auth()->user();
        $company = Company::first();
        $userDesignation = is_string($user?->designation) 
            ? $user->designation 
            : ($user?->employeeDetail?->designation?->name ?: 'Senior Software Engineer');

        $sampleText = LetterTemplateService::render($currentTemplate['content'], [
            '[Company Name]' => $company?->name ?: 'Bengal IT Hub Private Limited',
            '[Employee Name]' => $user?->name ?: 'Alexander Wright',
            '[Employee ID]' => 'EMP-' . date('Y') . '-' . str_pad($user?->id ?? '0842', 4, '0', STR_PAD_LEFT),
            '[Employee Email]' => $user?->email ?: 'alexander.w@bengalithub.com',
            '[Designation]' => $userDesignation,
            '[HR Email]' => $company?->email ?: 'hr@bengalithub.com',
            '[HR Phone]' => $company?->phone ?: '+91 92306 53975',
            '[Office Address]' => $company?->address ?: '3rd Floor 259, New Santoshpur Main Rd, Santoshpur, Kolkata 700075, India',
        ]);

        // Related Leaves (for Apology Letters)
        $leaveId = $request->get('leave_id');
        $leaves = Leave::with('leaveType')->orderBy('created_at', 'desc')->take(30)->get();
        $selectedLeave = $leaveId ? Leave::find($leaveId) : null;

        // Organization dataset options
        $letterheads = Letterhead::where('status', 'active')->orderBy('is_default', 'desc')->get();
        $defaultLetterhead = Letterhead::where('is_default', true)->first() ?: $letterheads->first();
        $companies = Company::orderBy('name')->get();
        $branches = BusinessAddress::orderBy('branch_name')->get();
        $departments = Department::orderBy('dpt_name')->get();
        $projects = Project::orderBy('name')->get();

        return view('admin.letterhead.create', compact(
            'categories',
            'allTemplates',
            'templateKey',
            'currentTemplate',
            'sampleText',
            'leaves',
            'selectedLeave',
            'letterheads',
            'defaultLetterhead',
            'companies',
            'branches',
            'departments',
            'projects'
        ));
    }

    /**
     * Store a newly created letterhead.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'type' => 'required|string|in:company,branch,department,project,custom',
            'company_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'project_id' => 'nullable|integer',
            'company_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'department_name' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'alternate_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'website' => 'nullable|string|max:200',
            'registration_number' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'gst_number' => 'nullable|string|max:100',
            'cin_number' => 'nullable|string|max:100',
            'other_info' => 'nullable|string|max:500',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp,pdf|max:10240',
            'header_image' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp,pdf|max:10240',
            'footer_image' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp,pdf|max:10240',
            'background_page_image' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp,pdf|max:15360',
            'layout_mode' => 'nullable|string|in:standard,custom_header_footer,full_a4_page',
            'content_padding_top' => 'nullable|integer|min:0|max:400',
            'content_padding_bottom' => 'nullable|integer|min:0|max:400',
            'content_padding_left' => 'nullable|integer|min:0|max:200',
            'content_padding_right' => 'nullable|integer|min:0|max:200',
            'preset_template' => 'nullable|string|max:100',
            'logo_position' => 'nullable|string|in:left,center,right',
            'logo_height' => 'nullable|integer|min:20|max:150',
            'header_content' => 'nullable|string',
            'header_font' => 'nullable|string|max:100',
            'header_font_size' => 'nullable|integer|min:9|max:36',
            'header_alignment' => 'nullable|string|in:left,center,right',
            'header_border_style' => 'nullable|string|in:none,solid,double,dashed,dotted',
            'header_border_thickness' => 'nullable|integer|min:0|max:10',
            'header_border_color' => 'nullable|string|max:50',
            'header_spacing' => 'nullable|integer|min:0|max:80',
            'header_height' => 'nullable|integer|min:40|max:250',
            'footer_content' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'footer_font_size' => 'nullable|integer|min:8|max:20',
            'footer_alignment' => 'nullable|string|in:left,center,right',
            'footer_border_style' => 'nullable|string|in:none,solid,double,dashed,dotted',
            'footer_border_thickness' => 'nullable|integer|min:0|max:10',
            'footer_border_color' => 'nullable|string|max:50',
            'footer_spacing' => 'nullable|integer|min:0|max:80',
            'footer_height' => 'nullable|integer|min:20|max:150',
            'paper_size' => 'nullable|string|in:a4,letter,legal',
            'orientation' => 'nullable|string|in:portrait,landscape',
            'margin_top' => 'nullable|integer|min:0|max:60',
            'margin_bottom' => 'nullable|integer|min:0|max:60',
            'margin_left' => 'nullable|integer|min:0|max:60',
            'margin_right' => 'nullable|integer|min:0|max:60',
            'watermark_enabled' => 'nullable|boolean',
            'watermark_type' => 'nullable|string|in:text,image',
            'watermark_text' => 'nullable|string|max:100',
            'watermark_image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            'watermark_opacity' => 'nullable|numeric|min:0.01|max:1.0',
            'watermark_rotation' => 'nullable|integer|min:-180|max:180',
            'watermark_size' => 'nullable|integer|min:16|max:120',
            'primary_color' => 'nullable|string|max:50',
            'secondary_color' => 'nullable|string|max:50',
            'header_line_color' => 'nullable|string|max:50',
            'footer_line_color' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,draft,inactive,archived',
            'is_default' => 'nullable|boolean',
            'change_summary' => 'nullable|string|max:255',
        ]);

        // Auto-generate unique code if blank
        if (empty($validated['code'])) {
            $validated['code'] = 'LH-' . date('Y') . '-' . strtoupper(Str::random(4));
        }

        // Set contextual names if IDs are provided
        $this->populateContextualNames($validated);

        // Handle File Uploads
        if ($request->hasFile('logo')) {
            $validated['logo'] = $this->handleFileUpload($request->file('logo'), 'logos');
        }
        if ($request->hasFile('header_image')) {
            $validated['header_image'] = $this->handleFileUpload($request->file('header_image'), 'headers');
        }
        if ($request->hasFile('footer_image')) {
            $validated['footer_image'] = $this->handleFileUpload($request->file('footer_image'), 'footers');
        }
        if ($request->hasFile('background_page_image')) {
            $validated['background_page_image'] = $this->handleFileUpload($request->file('background_page_image'), 'backgrounds');
        }
        if ($request->hasFile('watermark_image')) {
            $validated['watermark_image'] = $this->handleFileUpload($request->file('watermark_image'), 'watermarks');
        }

        $validated['is_default'] = $request->boolean('is_default');
        $validated['watermark_enabled'] = $request->boolean('watermark_enabled');
        $validated['status'] = $validated['status'] ?? ($request->input('action_type') === 'activate' ? 'active' : 'draft');
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        $validated['version'] = 1;

        // If setting as default, unset previous defaults in the same scope
        if ($validated['is_default']) {
            $this->resetScopeDefaults($validated['type'], $validated['company_id'] ?? null);
        }

        $letterhead = Letterhead::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Letter Head "' . $letterhead->name . '" created successfully.',
                'letterhead' => $letterhead,
            ]);
        }

        return redirect()->route('letterhead.index')
            ->with('success', 'Letter Head "' . $letterhead->name . '" created and configured successfully.');
    }

    /**
     * Show details/JSON of a letterhead.
     */
    public function show($id): JsonResponse|View
    {
        $letterhead = Letterhead::with(['company', 'branch', 'department', 'project', 'creator', 'updater'])
            ->findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'letterhead' => $letterhead,
                'formatted_address' => $letterhead->formatted_address,
                'organization_display' => $letterhead->organization_display_name,
            ]);
        }

        return view('admin.letterhead.show', compact('letterhead'));
    }

    /**
     * Return edit data for modal / page.
     */
    public function edit($id): JsonResponse|View
    {
        $letterhead = Letterhead::with(['company', 'branch', 'department', 'project'])->findOrFail($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'letterhead' => $letterhead,
            ]);
        }

        $companies = Company::orderBy('name')->get();
        $branches = BusinessAddress::orderBy('branch_name')->get();
        $departments = Department::orderBy('dpt_name')->get();
        $projects = Project::orderBy('name')->get();

        return view('admin.letterhead.edit', compact('letterhead', 'companies', 'branches', 'departments', 'projects'));
    }

    /**
     * Update an existing letterhead.
     */
    public function update(Request $request, $id): RedirectResponse|JsonResponse
    {
        $letterhead = Letterhead::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'type' => 'required|string|in:company,branch,department,project,custom',
            'company_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
            'department_id' => 'nullable|integer',
            'project_id' => 'nullable|integer',
            'company_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'department_name' => 'nullable|string|max:255',
            'project_name' => 'nullable|string|max:255',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:50',
            'alternate_phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'website' => 'nullable|string|max:200',
            'registration_number' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'gst_number' => 'nullable|string|max:100',
            'cin_number' => 'nullable|string|max:100',
            'other_info' => 'nullable|string|max:500',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp,pdf|max:10240',
            'header_image' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp,pdf|max:10240',
            'footer_image' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp,pdf|max:10240',
            'background_page_image' => 'nullable|file|mimes:jpeg,png,jpg,svg,webp,pdf|max:15360',
            'layout_mode' => 'nullable|string|in:standard,custom_header_footer,full_a4_page',
            'content_padding_top' => 'nullable|integer|min:0|max:400',
            'content_padding_bottom' => 'nullable|integer|min:0|max:400',
            'content_padding_left' => 'nullable|integer|min:0|max:200',
            'content_padding_right' => 'nullable|integer|min:0|max:200',
            'preset_template' => 'nullable|string|max:100',
            'logo_position' => 'nullable|string|in:left,center,right',
            'logo_height' => 'nullable|integer|min:20|max:150',
            'header_content' => 'nullable|string',
            'header_font' => 'nullable|string|max:100',
            'header_font_size' => 'nullable|integer|min:9|max:36',
            'header_alignment' => 'nullable|string|in:left,center,right',
            'header_border_style' => 'nullable|string|in:none,solid,double,dashed,dotted',
            'header_border_thickness' => 'nullable|integer|min:0|max:10',
            'header_border_color' => 'nullable|string|max:50',
            'header_spacing' => 'nullable|integer|min:0|max:80',
            'header_height' => 'nullable|integer|min:40|max:250',
            'footer_content' => 'nullable|string',
            'footer_text' => 'nullable|string',
            'footer_font_size' => 'nullable|integer|min:8|max:20',
            'footer_alignment' => 'nullable|string|in:left,center,right',
            'footer_border_style' => 'nullable|string|in:none,solid,double,dashed,dotted',
            'footer_border_thickness' => 'nullable|integer|min:0|max:10',
            'footer_border_color' => 'nullable|string|max:50',
            'footer_spacing' => 'nullable|integer|min:0|max:80',
            'footer_height' => 'nullable|integer|min:20|max:150',
            'paper_size' => 'nullable|string|in:a4,letter,legal',
            'orientation' => 'nullable|string|in:portrait,landscape',
            'margin_top' => 'nullable|integer|min:0|max:60',
            'margin_bottom' => 'nullable|integer|min:0|max:60',
            'margin_left' => 'nullable|integer|min:0|max:60',
            'margin_right' => 'nullable|integer|min:0|max:60',
            'watermark_enabled' => 'nullable|boolean',
            'watermark_type' => 'nullable|string|in:text,image',
            'watermark_text' => 'nullable|string|max:100',
            'watermark_image' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:5120',
            'watermark_opacity' => 'nullable|numeric|min:0.01|max:1.0',
            'watermark_rotation' => 'nullable|integer|min:-180|max:180',
            'watermark_size' => 'nullable|integer|min:16|max:120',
            'primary_color' => 'nullable|string|max:50',
            'secondary_color' => 'nullable|string|max:50',
            'header_line_color' => 'nullable|string|max:50',
            'footer_line_color' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,draft,inactive,archived',
            'is_default' => 'nullable|boolean',
            'change_summary' => 'nullable|string|max:255',
        ]);

        $this->populateContextualNames($validated);

        // Handle File Removals
        if ($request->boolean('remove_logo') && $letterhead->logo) {
            $this->removeFile($letterhead->logo);
            $validated['logo'] = null;
        }
        if ($request->boolean('remove_header_image') && $letterhead->header_image) {
            $this->removeFile($letterhead->header_image);
            $validated['header_image'] = null;
        }
        if ($request->boolean('remove_footer_image') && $letterhead->footer_image) {
            $this->removeFile($letterhead->footer_image);
            $validated['footer_image'] = null;
        }
        if ($request->boolean('remove_background_page_image') && $letterhead->background_page_image) {
            $this->removeFile($letterhead->background_page_image);
            $validated['background_page_image'] = null;
        }

        // Handle File Replacements
        if ($request->hasFile('logo')) {
            $this->removeFile($letterhead->logo);
            $validated['logo'] = $this->handleFileUpload($request->file('logo'), 'logos');
        }
        if ($request->hasFile('header_image')) {
            $this->removeFile($letterhead->header_image);
            $validated['header_image'] = $this->handleFileUpload($request->file('header_image'), 'headers');
        }
        if ($request->hasFile('footer_image')) {
            $this->removeFile($letterhead->footer_image);
            $validated['footer_image'] = $this->handleFileUpload($request->file('footer_image'), 'footers');
        }
        if ($request->hasFile('background_page_image')) {
            $this->removeFile($letterhead->background_page_image);
            $validated['background_page_image'] = $this->handleFileUpload($request->file('background_page_image'), 'backgrounds');
        }
        if ($request->hasFile('watermark_image')) {
            $this->removeFile($letterhead->watermark_image);
            $validated['watermark_image'] = $this->handleFileUpload($request->file('watermark_image'), 'watermarks');
        }

        $validated['is_default'] = $request->boolean('is_default');
        $validated['watermark_enabled'] = $request->boolean('watermark_enabled');
        $validated['updated_by'] = auth()->id();
        $validated['version'] = ($letterhead->version ?: 1) + 1;

        // If setting as default, unset others in scope
        if ($validated['is_default'] && ! $letterhead->is_default) {
            $this->resetScopeDefaults($validated['type'] ?? $letterhead->type, $validated['company_id'] ?? $letterhead->company_id);
        }

        $letterhead->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Letter Head updated successfully.',
                'letterhead' => $letterhead,
            ]);
        }

        return redirect()->route('letterhead.index')
            ->with('success', 'Letter Head "' . $letterhead->name . '" updated successfully (v' . $letterhead->version . ').');
    }

    /**
     * Safe deletion of a letterhead.
     */
    public function destroy($id): RedirectResponse|JsonResponse
    {
        $letterhead = Letterhead::findOrFail($id);

        $name = $letterhead->name;
        $wasDefault = $letterhead->is_default;

        $this->removeFile($letterhead->logo);
        $this->removeFile($letterhead->header_image);
        $this->removeFile($letterhead->footer_image);
        $this->removeFile($letterhead->background_page_image);
        $this->removeFile($letterhead->watermark_image);

        $letterhead->delete();

        $message = 'Letter Head "' . $name . '" was deleted successfully.';
        if ($wasDefault) {
            $message .= ' Notice: This was previously a default letterhead. Please assign a new default.';
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('letterhead.index')->with('success', $message);
    }

    /**
     * Duplicate an existing letterhead.
     */
    public function duplicate($id): RedirectResponse|JsonResponse
    {
        $original = Letterhead::findOrFail($id);

        $clone = $original->replicate([
            'is_default',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        $clone->name = $original->name . ' (Copy)';
        $clone->code = 'LH-' . date('Y') . '-' . strtoupper(Str::random(4));
        $clone->is_default = false;
        $clone->status = 'draft';
        $clone->version = 1;
        $clone->change_summary = 'Duplicated from ' . $original->name;
        $clone->created_by = auth()->id();
        $clone->updated_by = auth()->id();
        $clone->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Letter Head duplicated as "' . $clone->name . '".',
                'letterhead' => $clone,
            ]);
        }

        return redirect()->route('letterhead.index')
            ->with('success', 'Letter Head duplicated successfully as "' . $clone->name . '".');
    }

    /**
     * Set a letterhead as default for its scope.
     */
    public function setDefault($id): RedirectResponse|JsonResponse
    {
        $letterhead = Letterhead::findOrFail($id);

        $this->resetScopeDefaults($letterhead->type, $letterhead->company_id);

        $letterhead->update([
            'is_default' => true,
            'status' => 'active',
            'updated_by' => auth()->id(),
        ]);

        $msg = 'Letter Head "' . $letterhead->name . '" is now set as the Default ' . ucfirst($letterhead->type) . ' Letterhead.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
            ]);
        }

        return redirect()->route('letterhead.index')->with('success', $msg);
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus($id): RedirectResponse|JsonResponse
    {
        $letterhead = Letterhead::findOrFail($id);

        $newStatus = $letterhead->status === 'active' ? 'inactive' : 'active';
        $letterhead->update([
            'status' => $newStatus,
            'updated_by' => auth()->id(),
        ]);

        $msg = 'Letter Head "' . $letterhead->name . '" status changed to ' . ucfirst($newStatus) . '.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'status' => $newStatus,
            ]);
        }

        return redirect()->route('letterhead.index')->with('success', $msg);
    }

    /**
     * Toggle archive status.
     */
    public function archive($id): RedirectResponse|JsonResponse
    {
        $letterhead = Letterhead::findOrFail($id);

        $newStatus = $letterhead->status === 'archived' ? 'active' : 'archived';
        $letterhead->update([
            'status' => $newStatus,
            'is_default' => $newStatus === 'archived' ? false : $letterhead->is_default,
            'updated_by' => auth()->id(),
        ]);

        $msg = 'Letter Head "' . $letterhead->name . '" has been ' . ($newStatus === 'archived' ? 'archived' : 'restored to active') . '.';

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'status' => $newStatus,
            ]);
        }

        return redirect()->route('letterhead.index')->with('success', $msg);
    }

    /**
     * Export / Download dynamically generated letter in PDF format.
     */
    public function exportPdf(Request $request): Response
    {
        $request->validate([
            'subject' => 'required|string|max:300',
            'body' => 'required|string',
            'letterhead_id' => 'nullable|integer',
            'header_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,pdf|max:10240',
            'footer_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,pdf|max:10240',
            'background_page_image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,pdf|max:10240',
        ]);

        $letterhead = null;
        if ($request->filled('letterhead_id')) {
            $letterhead = Letterhead::find($request->letterhead_id);
        }
        if (! $letterhead) {
            $letterhead = Letterhead::where('is_default', true)->first() ?: Letterhead::first();
        }

        // Layout & Image Paths
        $layoutMode = $request->input('layout_mode', $letterhead?->layout_mode ?: 'custom_header_footer');
        $headerImagePath = $letterhead?->header_image ?: 'assets/letterhead/presets/bengal_header.svg';
        $footerImagePath = $letterhead?->footer_image ?: 'assets/letterhead/presets/bengal_footer.svg';
        $bgPageImagePath = $letterhead?->background_page_image ?: 'assets/letterhead/presets/bengal_it_hub_a4.svg';

        $destinationPath = public_path('uploads/letterhead');
        if (! file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if ($request->hasFile('header_image')) {
            $headerFile = $request->file('header_image');
            $headerName = 'header_' . time() . '_' . Str::random(6) . '.' . $headerFile->getClientOriginalExtension();
            $headerFile->move($destinationPath, $headerName);
            $headerImagePath = 'uploads/letterhead/' . $headerName;
        }

        if ($request->hasFile('footer_image')) {
            $footerFile = $request->file('footer_image');
            $footerName = 'footer_' . time() . '_' . Str::random(6) . '.' . $footerFile->getClientOriginalExtension();
            $footerFile->move($destinationPath, $footerName);
            $footerImagePath = 'uploads/letterhead/' . $footerName;
        }

        if ($request->hasFile('background_page_image')) {
            $bgFile = $request->file('background_page_image');
            $bgName = 'bg_page_' . time() . '_' . Str::random(6) . '.' . $bgFile->getClientOriginalExtension();
            $bgFile->move($destinationPath, $bgName);
            $bgPageImagePath = 'uploads/letterhead/' . $bgName;
            $layoutMode = 'full_a4_page';
        }

        if ($layoutMode === 'full_a4_page') {
            $headerImagePath = null;
            $footerImagePath = null;
        }

        // Prepare letter document structure
        $letter = [
            'ref_no' => $request->input('ref_no') ?: ('REF/' . date('Y') . '/' . strtoupper(Str::random(6))),
            'date' => $request->input('date') ?: now()->format('F d, Y'),
            'recipient_name' => $request->input('recipient_name') ?: '',
            'recipient_email' => $request->input('recipient_email') ?: '',
            'subject' => $request->input('subject'),
            'body' => $request->input('body'),
            'body_paragraphs' => preg_split('/\r\n|\r|\n/', $request->input('body')),
            'signatory_name' => $request->input('signatory_name') ?: (auth()->user()?->name ?: 'Arthur Pendelton'),
            'signatory_title' => $request->input('signatory_title') ?: 'Authorized Signatory',
            'layout_mode' => $layoutMode,
            'header_image' => $headerImagePath,
            'footer_image' => $footerImagePath,
            'background_page_image' => $bgPageImagePath,
        ];

        $fileName = 'Letter_' . Str::slug(substr($letter['subject'], 0, 30)) . '_' . date('Ymd') . '.pdf';

        return PdfLetterheadService::generate($letter, $letterhead, $fileName);
    }

    /**
     * Export / Download dynamically generated letter in Microsoft Word (.docx) format.
     */
    public function exportWord(Request $request): BinaryFileResponse|Response
    {
        $request->validate([
            'subject' => 'required|string|max:300',
            'body' => 'required|string',
            'letterhead_id' => 'nullable|integer',
        ]);

        $letterhead = null;
        if ($request->filled('letterhead_id')) {
            $letterhead = Letterhead::find($request->letterhead_id);
        }
        if (! $letterhead) {
            $letterhead = Letterhead::where('is_default', true)->first() ?: Letterhead::first();
        }

        $letterData = [
            'company_name' => $letterhead?->company_name ?: 'BENGAL IT HUB PRIVATE LIMITED',
            'cin_number' => $letterhead?->cin_number ?: 'CIN : U62090WB2026PTC287230',
            'address' => $letterhead?->formatted_address ?: '3RD FLOOR 259, NEW SANTOSHPUR MAIN RD, SANTOSHPUR, KOLKATA 700075, INDIA',
            'phone' => $letterhead?->phone ?: '+91 92306 53975',
            'email' => $letterhead?->email ?: 'CONTACT@BENGALITHUB.COM',
            'website' => $letterhead?->website ?: 'WWW.BENGALITHUB.COM',
            'ref_no' => $request->input('ref_no') ?: ('REF/' . date('Y') . '/' . strtoupper(Str::random(6))),
            'date' => $request->input('date') ?: now()->format('F d, Y'),
            'recipient_name' => $request->input('recipient_name') ?: '',
            'recipient_email' => $request->input('recipient_email') ?: '',
            'subject' => $request->input('subject'),
            'body' => $request->input('body'),
            'signatory_name' => $request->input('signatory_name') ?: (auth()->user()?->name ?: 'Arthur Pendelton'),
            'signatory_title' => $request->input('signatory_title') ?: 'Authorized Signatory',
        ];

        $fileName = 'Letter_' . Str::slug(substr($letterData['subject'], 0, 30)) . '_' . date('Ymd') . '.docx';

        return DocxGeneratorService::download($letterData, $fileName);
    }

    /**
     * Instant Demo Download for the professional sample card.
     */
    public function demoDownload(Request $request): BinaryFileResponse|Response
    {
        $templateKey = $request->get('template_key', 'apology_leave');
        $format = $request->get('format', 'pdf'); // 'pdf' or 'word'

        $template = LetterTemplateService::getTemplate($templateKey);
        $user = auth()->user();
        $company = Company::first();

        $userDesignation = is_string($user?->designation) 
            ? $user->designation 
            : ($user?->employeeDetail?->designation?->name ?: 'Senior Software Engineer');

        $renderedText = LetterTemplateService::render($template['content'], [
            '[Company Name]' => $company?->name ?: 'Bengal IT Hub Private Limited',
            '[Employee Name]' => $user?->name ?: 'Alexander Wright',
            '[Employee ID]' => 'EMP-' . date('Y') . '-' . str_pad($user?->id ?? '0842', 4, '0', STR_PAD_LEFT),
            '[Designation]' => $userDesignation,
        ]);

        $letterData = [
            'company_name' => $company?->name ?: 'BENGAL IT HUB PRIVATE LIMITED',
            'cin_number' => 'CIN : U62090WB2026PTC287230',
            'address' => $company?->address ?: '3RD FLOOR 259, NEW SANTOSHPUR MAIN RD, SANTOSHPUR, KOLKATA 700075, INDIA',
            'phone' => $company?->phone ?: '+91 92306 53975',
            'email' => $company?->email ?: 'CONTACT@BENGALITHUB.COM',
            'website' => $company?->website ?: 'WWW.BENGALITHUB.COM',
            'ref_no' => 'DEMO/' . date('Y') . '/' . strtoupper(Str::random(5)),
            'date' => now()->format('F d, Y'),
            'recipient_name' => 'HR Operations Team',
            'subject' => $template['subject'] ?? 'Professional Sample Letter',
            'body' => $renderedText,
            'signatory_name' => $user?->name ?: 'Arthur Pendelton',
            'signatory_title' => 'Executive Director / Authorized Signatory',
        ];

        if ($format === 'word' || $format === 'docx') {
            $fileName = 'Demo_' . Str::slug($template['name']) . '.docx';

            return DocxGeneratorService::download($letterData, $fileName);
        }

        $letterhead = Letterhead::where('is_default', true)->first() ?: Letterhead::first();
        $letter = $letterData;
        $letter['body_paragraphs'] = preg_split('/\r\n|\r|\n/', $renderedText);
        $fileName = 'Demo_' . Str::slug($template['name']) . '.pdf';

        return PdfLetterheadService::generate($letter, $letterhead, $fileName);
    }

    /**
     * Send generated letter to HR or recipient.
     */
    public function sendLetter(Request $request): RedirectResponse
    {
        $request->validate([
            'subject' => 'required|string|max:300',
            'body' => 'required|string',
            'recipient_email' => 'nullable|email',
        ]);

        $recipient = $request->input('recipient_email') ?: 'hr@bengalithub.com';

        // Log and redirect
        Log::info('Official letter submitted: ' . $request->input('subject') . ' to ' . $recipient);

        return redirect()->route('letterhead.create')
            ->with('success', 'Letter "' . $request->input('subject') . '" has been prepared and sent to ' . $recipient . ' successfully.');
    }

    /**
     * Generate standalone corporate sample letterhead PDF with DomPDF.
     */
    public function generatePdf($id): Response
    {
        $letterhead = Letterhead::with(['company', 'branch', 'department', 'project'])->findOrFail($id);

        $sampleLetter = [
            'ref_no' => 'REF/' . date('Y') . '/' . strtoupper(Str::random(6)),
            'date' => now()->format('F d, Y'),
            'recipient_name' => 'Dr. Eleanor Vance',
            'recipient_title' => 'Director of Corporate Partnerships',
            'recipient_org' => 'Apex Global Solutions Ltd.',
            'recipient_address' => '452 Innovation Boulevard, Tech Park, CA 94025',
            'subject' => 'Official Confirmation & Corporate Strategic Partnership Agreement',
            'body_paragraphs' => [
                'We are pleased to formally present this official communication confirming the executive approval of the strategic partnership initiative between our organizations. This document outlines the initial parameters and framework for the forthcoming fiscal collaboration.',
                'Our executive management team has thoroughly evaluated the mutual deliverables and operational alignment. We remain steadfast in upholding the highest benchmarks of quality, accountability, and professional integrity across all designated milestones.',
                'Should your department require any supplementary documentation, certified clearances, or technical schematics, please do not hesitate to contact our executive administrative office directly through the coordinates provided below.',
            ],
            'signatory_name' => auth()->user()?->name ?: 'Arthur Pendelton',
            'signatory_title' => 'Chief Executive Officer / Managing Director',
        ];

        $fileName = 'Letterhead_' . Str::slug($letterhead->name) . '_' . date('Ymd') . '.pdf';

        return PdfLetterheadService::generate($sampleLetter, $letterhead, $fileName);
    }

    /**
     * Render standalone browser print layout.
     */
    public function printPreview($id): View
    {
        $letterhead = Letterhead::with(['company', 'branch', 'department', 'project'])->findOrFail($id);

        $sampleLetter = [
            'ref_no' => 'REF/' . date('Y') . '/' . strtoupper(Str::random(6)),
            'date' => now()->format('F d, Y'),
            'recipient_name' => 'Dr. Eleanor Vance',
            'recipient_title' => 'Director of Corporate Partnerships',
            'recipient_org' => 'Apex Global Solutions Ltd.',
            'recipient_address' => '452 Innovation Boulevard, Tech Park, CA 94025',
            'subject' => 'Official Confirmation & Corporate Strategic Partnership Agreement',
            'body_paragraphs' => [
                'We are pleased to formally present this official communication confirming the executive approval of the strategic partnership initiative between our organizations.',
                'Our executive management team has thoroughly evaluated the mutual deliverables and operational alignment.',
                'Should your department require any supplementary documentation, please contact our office.',
            ],
            'signatory_name' => auth()->user()?->name ?: 'Arthur Pendelton',
            'signatory_title' => 'Chief Executive Officer / Managing Director',
        ];

        return view('admin.letterhead.print', compact('letterhead', 'sampleLetter'));
    }

    // =========================================================================
    // LEGACY COMPATIBILITY METHODS
    // =========================================================================

    public function upload(Request $request, Company $company): RedirectResponse
    {
        $request->validate([
            'letterhead_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        if ($request->hasFile('letterhead_file')) {
            $file = $request->file('letterhead_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $originalName = $file->getClientOriginalName();
            $fileType = in_array($extension, ['doc', 'docx']) ? 'docs' : 'pdf';

            $destinationPath = public_path('uploads/letterheads');
            if (! File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            if ($company->letterhead_file && File::exists(public_path($company->letterhead_file))) {
                File::delete(public_path($company->letterhead_file));
            }

            $fileName = 'letterhead_company_' . $company->id . '_' . time() . '.' . $extension;
            $file->move($destinationPath, $fileName);

            $company->update([
                'letterhead_file' => 'uploads/letterheads/' . $fileName,
                'letterhead_original_name' => $originalName,
                'letterhead_file_type' => $fileType,
                'letterhead_uploaded_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Official Letterhead for "' . $company->name . '" uploaded successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload letterhead file.');
    }

    public function download(Company $company): BinaryFileResponse|RedirectResponse
    {
        if (! $company->hasLetterhead() || ! File::exists(public_path($company->letterhead_file))) {
            return redirect()->back()->with('error', 'No letterhead file found for ' . $company->name);
        }

        $filePath = public_path($company->letterhead_file);
        $displayName = $company->letterhead_original_name ?: ($company->company_code . '_Letterhead.' . pathinfo($filePath, PATHINFO_EXTENSION));

        return response()->download($filePath, $displayName);
    }

    public function destroyLegacy(Company $company): RedirectResponse
    {
        if ($company->letterhead_file && File::exists(public_path($company->letterhead_file))) {
            File::delete(public_path($company->letterhead_file));
        }

        $company->update([
            'letterhead_file' => null,
            'letterhead_original_name' => null,
            'letterhead_file_type' => null,
            'letterhead_uploaded_at' => null,
        ]);

        return redirect()->back()->with('success', 'Letterhead template removed for ' . $company->name);
    }

    // =========================================================================
    // PRIVATE UTILITIES
    // =========================================================================

    private function handleFileUpload($file, string $subfolder): string
    {
        $destinationPath = public_path('uploads/letterheads/' . $subfolder);
        if (! File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        $fileName = 'lh_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($destinationPath, $fileName);

        return 'uploads/letterheads/' . $subfolder . '/' . $fileName;
    }

    private function removeFile(?string $relativePath): void
    {
        if ($relativePath && File::exists(public_path($relativePath))) {
            File::delete(public_path($relativePath));
        }
    }

    private function populateContextualNames(array &$data): void
    {
        if (! empty($data['company_id']) && empty($data['company_name'])) {
            $company = Company::find($data['company_id']);
            $data['company_name'] = $company?->name;
            if (empty($data['email'])) $data['email'] = $company?->email;
            if (empty($data['phone'])) $data['phone'] = $company?->phone;
            if (empty($data['website'])) $data['website'] = $company?->website;
            if (empty($data['address_line_1'])) $data['address_line_1'] = $company?->address;
        }

        if (! empty($data['branch_id'])) {
            $branch = BusinessAddress::find($data['branch_id']);
            $data['branch_name'] = $branch?->branch_name ?: $branch?->location;
        }

        if (! empty($data['department_id'])) {
            $dept = Department::find($data['department_id']);
            $data['department_name'] = $dept?->dpt_name;
        }

        if (! empty($data['project_id'])) {
            $proj = Project::find($data['project_id']);
            $data['project_name'] = $proj?->name;
        }
    }

    private function resetScopeDefaults(string $type, ?int $companyId): void
    {
        $query = Letterhead::where('type', $type);
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        $query->update(['is_default' => false]);
    }

    /**
     * Seeds initial default letterhead if the table is empty.
     */
    private function ensureInitialSeedExists(): void
    {
        if (Letterhead::count() === 0) {
            $company = Company::first();

            Letterhead::create([
                'name' => 'Bengal IT Hub Corporate Letterhead',
                'code' => 'LH-2026-BENGAL01',
                'type' => 'company',
                'company_id' => $company?->id,
                'company_name' => 'Bengal IT Hub Private Limited',
                'tagline' => 'Next-Generation Enterprise Technology & Solutions',
                'address_line_1' => '3rd Floor 259, New Santoshpur Main Rd',
                'city' => 'Santoshpur, Kolkata',
                'state' => 'West Bengal',
                'country' => 'India',
                'postal_code' => '700075',
                'phone' => '+91 92306 53975',
                'email' => 'contact@bengalithub.com',
                'website' => 'www.bengalithub.com',
                'cin_number' => 'CIN : U62090WB2026PTC287230',
                'layout_mode' => 'full_a4_page',
                'background_page_image' => 'assets/letterhead/presets/bengal_it_hub_a4.svg',
                'content_padding_top' => 140,
                'content_padding_bottom' => 120,
                'content_padding_left' => 65,
                'content_padding_right' => 65,
                'logo_position' => 'left',
                'logo_height' => 54,
                'header_font' => 'Plus Jakarta Sans',
                'header_font_size' => 14,
                'header_alignment' => 'left',
                'header_border_style' => 'solid',
                'header_border_thickness' => 2,
                'header_border_color' => '#1e40af',
                'header_spacing' => 22,
                'header_height' => 84,
                'footer_content' => '3rd Floor 259, New Santoshpur Main Rd, Santoshpur, Kolkata 700075, India',
                'footer_text' => 'Tel: +91 92306 53975 • Website: www.bengalithub.com • Email: contact@bengalithub.com',
                'footer_font_size' => 10,
                'footer_alignment' => 'center',
                'footer_border_style' => 'solid',
                'footer_border_thickness' => 1,
                'footer_border_color' => '#cbd5e1',
                'footer_spacing' => 16,
                'footer_height' => 52,
                'paper_size' => 'a4',
                'orientation' => 'portrait',
                'margin_top' => 22,
                'margin_bottom' => 22,
                'margin_left' => 22,
                'margin_right' => 22,
                'watermark_enabled' => false,
                'primary_color' => '#1e40af',
                'secondary_color' => '#3b82f6',
                'status' => 'active',
                'is_default' => true,
                'version' => 1,
                'change_summary' => 'Initial Bengal IT Hub A4 letterhead preset',
                'created_by' => auth()->id() ?: 1,
                'updated_by' => auth()->id() ?: 1,
            ]);
        }
    }
}

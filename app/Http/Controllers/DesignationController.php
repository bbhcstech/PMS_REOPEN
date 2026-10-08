<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class DesignationController extends Controller
{
    private static bool $schemaChecked = false;

    public function updateLevelSettings(Request $request)
    {
        abort_unless(in_array(strtolower((string) auth()->user()?->role), ['admin', 'administrator'], true), 403);
        $data = $request->validate(['maximum_level' => ['required', 'integer', 'min:6', 'max:100']]);
        $maximum = (int) $data['maximum_level'];
        if (Designation::where('level', '>', $maximum)->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'maximum_level' => 'Correct existing designations above this limit before lowering it.',
            ]);
        }
        DB::connection('central')->transaction(function () use ($maximum) {
            $company = \App\Models\Company::whereKey(\App\Services\DesignationLevels::companyId())->lockForUpdate()->firstOrFail();
            $settings = $company->settings ?? [];
            $settings['designation_max_level'] = $maximum;
            $company->settings = $settings;
            $company->save();
        });
        return back()->with('success', 'Company designation level limit updated.');
    }

    public static function ensureDesignationColumnsExist(): void
    {
        if (self::$schemaChecked) {
            return;
        }
        self::$schemaChecked = true;

        try {
            if (! Schema::hasTable('designations')) {
                return;
            }

            $missing = [];
            foreach (['company_id', 'parent_id', 'unique_code', 'level', 'order', 'status', 'archived_at'] as $col) {
                if (! Schema::hasColumn('designations', $col)) {
                    $missing[] = $col;
                }
            }

            if (! empty($missing)) {
                Schema::table('designations', function (\Illuminate\Database\Schema\Blueprint $table) use ($missing) {
                    if (in_array('company_id', $missing, true)) {
                        $table->unsignedBigInteger('company_id')->nullable()->index();
                    }
                    if (in_array('parent_id', $missing, true)) {
                        $table->unsignedBigInteger('parent_id')->nullable()->index();
                    }
                    if (in_array('unique_code', $missing, true)) {
                        $table->string('unique_code')->nullable();
                    }
                    if (in_array('level', $missing, true)) {
                        $table->integer('level')->default(0);
                    }
                    if (in_array('order', $missing, true)) {
                        $table->integer('order')->default(0);
                    }
                    if (in_array('status', $missing, true)) {
                        $table->string('status', 50)->default('active');
                    }
                    if (in_array('archived_at', $missing, true)) {
                        $table->timestamp('archived_at')->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {}
    }

    public function index(Request $request)
    {
        self::ensureDesignationColumnsExist();
        $perPage = $request->get('per_page', 10);

        $hasParentId = Schema::hasColumn('designations', 'parent_id');
        $hasLevel = Schema::hasColumn('designations', 'level');
        $hasArchivedAt = Schema::hasColumn('designations', 'archived_at');

        // Order by level and name to ensure proper display
        $withRelations = ['addedBy', 'updatedBy'];
        if ($hasParentId) {
            $withRelations[] = 'parent';
        }

        $query = Designation::with($withRelations);
        if ($hasArchivedAt) {
            $query->whereNull('archived_at');
        }
        if ($hasLevel) {
            $query->orderBy('level', 'asc');
        }
        $designations = $query->orderBy('name', 'asc')->paginate($perPage);

        // Get accurate global counts safely
        $levelsCount = $hasLevel
            ? Designation::when($hasArchivedAt, fn ($q) => $q->whereNull('archived_at'))->distinct('level')->count('level')
            : 0;

        $topLevelQuery = Designation::query();
        if ($hasArchivedAt) {
            $topLevelQuery->whereNull('archived_at');
        }
        if ($hasParentId && $hasLevel) {
            $topLevelQuery->where(function ($q) {
                $q->whereNull('parent_id')->orWhere('level', '<=', 2);
            });
        } elseif ($hasParentId) {
            $topLevelQuery->whereNull('parent_id');
        } elseif ($hasLevel) {
            $topLevelQuery->where('level', '<=', 2);
        }
        $topLevelCount = $topLevelQuery->count();

        $recentCount = Designation::when($hasArchivedAt, fn ($q) => $q->whereNull('archived_at'))
            ->where('updated_at', '>=', now()->subDays(7))->count();

        $archivedCount = $hasArchivedAt ? Designation::whereNotNull('archived_at')->count() : 0;

        return view('admin.designations.index', compact('designations', 'levelsCount', 'topLevelCount', 'recentCount', 'archivedCount'));
    }

    public function show(Designation $designation)
    {
        self::ensureDesignationColumnsExist();
        $relations = ['addedBy', 'updatedBy', 'employeeDetails'];
        if (Schema::hasColumn('designations', 'parent_id')) {
            $relations[] = 'parent';
        }
        $designation->load($relations);
        $hierarchy = $this->getHierarchyTree($designation);
        return view('admin.designations.show', compact('designation', 'hierarchy'));
    }

    private function getHierarchyTree(Designation $designation)
    {
        $hasParentId = Schema::hasColumn('designations', 'parent_id');
        $ancestors = collect();
        $current = $designation;

        if ($hasParentId) {
            while ($current->parent) {
                $ancestors->prepend($current->parent);
                $current = $current->parent;
            }
            $children = $designation->children;
            $descendants = method_exists($designation, 'descendants') ? $designation->descendants : collect();
        } else {
            $children = collect();
            $descendants = collect();
        }

        return [
            'ancestors' => $ancestors,
            'children' => $children,
            'descendants' => $descendants,
        ];
    }

    public function create()
    {
        self::ensureDesignationColumnsExist();
        $hasArchivedAt = Schema::hasColumn('designations', 'archived_at');
        $hasLevel = Schema::hasColumn('designations', 'level');

        $query = Designation::query();
        if ($hasArchivedAt) {
            $query->whereNull('archived_at');
        }
        if ($hasLevel) {
            $query->orderBy('level', 'asc');
        }
        $designations = $query->orderBy('name', 'asc')->get();
        $nextCode = $this->generateNextCodePreview();
        return view('admin.designations.create', compact('designations', 'nextCode'));
    }

    public function nextCode()
    {
        return response()->json(['next_code' => $this->generateNextCodePreview()]);
    }

    private function generateNextCodePreview()
    {
        $nextId = ((int) Designation::max('id')) + 1;

        return 'DGN-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Helper: get all descendant IDs recursively for a given designation ID.
     */
    public function getAllDescendantIds(int $id): array
    {
        $descendants = [];
        $children = Designation::where('parent_id', $id)->pluck('id')->all();
        foreach ($children as $childId) {
            $childId = (int) $childId;
            $descendants[] = $childId;
            $descendants = array_merge($descendants, $this->getAllDescendantIds($childId));
        }
        return array_values(array_unique($descendants));
    }

    /**
     * Helper: get the maximum subtree depth below a designation.
     * Returns 0 if leaf node, 1 if direct children only, etc.
     */
    public function getMaxSubtreeDepth(int $id, int $currentDepth = 0): int
    {
        $children = Designation::where('parent_id', $id)->pluck('id')->all();
        if (empty($children)) {
            return $currentDepth;
        }
        $max = $currentDepth;
        foreach ($children as $childId) {
            $subDepth = $this->getMaxSubtreeDepth((int) $childId, $currentDepth + 1);
            if ($subDepth > $max) {
                $max = $subDepth;
            }
        }
        return $max;
    }

    public function store(Request $request)
    {
        $request->merge([
            'code_generation_mode' => $request->input('code_generation_mode', 'auto'),
            'unique_code'          => trim((string) $request->input('unique_code', '')),
            'parent_id'            => $request->filled('parent_id') ? $request->input('parent_id') : null,
        ]);

        $request->validate([
            'name'                 => ['required', 'string', 'max:255', Rule::unique('designations', 'name')],
            'parent_id'            => ['nullable', Rule::exists('tenant.designations', 'id')->where(fn ($q) => $q->whereIn('id', Designation::pluck('id')))],
            'level'                => ['required', 'integer', 'min:0', 'max:' . \App\Services\DesignationLevels::maximum()],
            'code_generation_mode' => ['required', Rule::in(['auto', 'custom'])],
            'unique_code'          => [
                'required_if:code_generation_mode,custom',
                'nullable',
                'string',
                'max:255',
                Rule::unique('designations', 'unique_code'),
            ],
        ]);

        $level = (int) $request->level;

        // Strict level range validation (0 - 6 only)
        if ($level < 0 || $level > \App\Services\DesignationLevels::maximum()) {
            $errorMsg = 'Designation level must be within the configured company limit.';
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['level' => [$errorMsg]]], 422);
            }
            return back()->withErrors(['level' => $errorMsg])->withInput();
        }

        // Parent designation hierarchy validation
        if ($request->filled('parent_id')) {
            $parent = Designation::find($request->parent_id);
            if ($parent) {
                $parentLevel = (int) ($parent->level ?? 0);
                if ($parentLevel >= \App\Services\DesignationLevels::maximum()) {
                    $errorMsg = 'A designation at the company maximum level cannot have subordinate designations.';
                    if ($request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['parent_id' => [$errorMsg]]], 422);
                    }
                    return back()->withErrors(['parent_id' => $errorMsg])->withInput();
                }

                if ($level <= $parentLevel) {
                    $errorMsg = 'Subordinate designation level (' . $level . ') must be greater than parent designation level (Level ' . $parentLevel . '). Minimum allowed level is ' . ($parentLevel + 1) . '.';
                    if ($request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['level' => [$errorMsg]]], 422);
                    }
                    return back()->withErrors(['level' => $errorMsg])->withInput();
                }
            }
        }

        try {
            $designationData = [
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
                'level'           => $level,
                'added_by'        => Auth::id(),
                'last_updated_by' => Auth::id(),
            ];

            if ($request->code_generation_mode === 'custom') {
                $designationData['unique_code'] = $request->unique_code;
            }

            $designation = Designation::create($designationData);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'designation' => [
                        'id'          => $designation->id,
                        'name'        => $designation->name,
                        'level'       => $designation->level,
                        'unique_code' => $designation->unique_code,
                        'parent_id'   => $designation->parent_id,
                    ]
                ]);
            }

        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This designation name already exists.'
                    ], 422);
                }
                return back()->withErrors(['name' => 'This designation name already exists.'])->withInput();
            }
            throw $e;
        }

        return redirect()->route('designations.index', ['t' => time()])
            ->with('success', 'Designation added successfully.');
    }

    public function edit(Designation $designation)
    {
        self::ensureDesignationColumnsExist();
        $hasArchivedAt = Schema::hasColumn('designations', 'archived_at');
        $hasLevel = Schema::hasColumn('designations', 'level');

        // Exclude the designation itself and all of its descendants to prevent circular hierarchy
        $excludeIds = array_merge([$designation->id], $this->getAllDescendantIds($designation->id));

        $query = Designation::whereNotIn('id', $excludeIds);
        if ($hasArchivedAt) {
            $query->whereNull('archived_at');
        }
        if ($hasLevel) {
            $query->orderBy('level', 'asc');
        }
        $designations = $query->orderBy('name', 'asc')->get();
        return view('admin.designations.create', compact('designation', 'designations'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->merge([
            'parent_id' => $request->filled('parent_id') ? $request->input('parent_id') : null,
        ]);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('designations', 'name')->ignore($designation->id)
            ],
            'parent_id' => [
                'nullable',
                Rule::exists('tenant.designations', 'id')->where(fn ($q) => $q->whereIn('id', Designation::pluck('id'))),
                Rule::notIn([$designation->id]),
            ],
            'level'     => ['required', 'integer', 'min:0', 'max:' . \App\Services\DesignationLevels::maximum()]
        ]);

        $level = (int) $request->level;

        // Strict level range validation (0 - 6 only)
        if ($level < 0 || $level > \App\Services\DesignationLevels::maximum()) {
            $errorMsg = 'Designation level must be within the configured company limit.';
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['level' => [$errorMsg]]], 422);
            }
            return back()->withErrors(['level' => $errorMsg])->withInput();
        }

        // Circular hierarchy loop check
        if ($request->filled('parent_id')) {
            $descendantIds = $this->getAllDescendantIds($designation->id);
            if (in_array((int) $request->parent_id, $descendantIds, true)) {
                $errorMsg = 'Cannot select a subordinate designation as the parent (circular hierarchy detected).';
                if ($request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['parent_id' => [$errorMsg]]], 422);
                }
                return back()->withErrors(['parent_id' => $errorMsg])->withInput();
            }

            $parent = Designation::find($request->parent_id);
            if ($parent) {
                $parentLevel = (int) ($parent->level ?? 0);
                if ($parentLevel >= \App\Services\DesignationLevels::maximum()) {
                    $errorMsg = 'A designation at the company maximum level cannot have subordinate designations.';
                    if ($request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['parent_id' => [$errorMsg]]], 422);
                    }
                    return back()->withErrors(['parent_id' => $errorMsg])->withInput();
                }

                if ($level <= $parentLevel) {
                    $errorMsg = 'Subordinate designation level (' . $level . ') must be greater than parent designation level (Level ' . $parentLevel . '). Minimum allowed level is ' . ($parentLevel + 1) . '.';
                    if ($request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['level' => [$errorMsg]]], 422);
                    }
                    return back()->withErrors(['level' => $errorMsg])->withInput();
                }
            }
        }

        // Subtree depth limit check: if this designation has descendants, new level + max descendant depth cannot exceed 6
        $maxSubtreeDepth = $this->getMaxSubtreeDepth($designation->id);
        if ($level + $maxSubtreeDepth > \App\Services\DesignationLevels::maximum()) {
            $errorMsg = 'Updating this designation to Level ' . $level . ' would cause subordinate designations to exceed the configured company level limit (subtree depth reaches Level ' . ($level + $maxSubtreeDepth) . ').';
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['level' => [$errorMsg]]], 422);
            }
            return back()->withErrors(['level' => $errorMsg])->withInput();
        }

        try {
            $designation->update([
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
                'level'           => $level,
                'last_updated_by' => Auth::id(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'designation' => [
                        'id'          => $designation->id,
                        'name'        => $designation->name,
                        'level'       => $designation->level,
                        'unique_code' => $designation->unique_code,
                        'parent_id'   => $designation->parent_id,
                    ]
                ]);
            }

        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                if ($request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'This designation name already exists.'
                    ], 422);
                }
                return back()->withErrors(['name' => 'This designation name already exists.'])
                    ->withInput();
            }
            throw $e;
        }

        return redirect()->route('designations.index', ['t' => time()])
            ->with('success', 'Designation updated successfully.')
            ->with('updated_id', $designation->id);
    }

    public function ajaxStore(Request $request)
    {
        $request->merge([
            'parent_id' => $request->filled('parent_id') ? $request->input('parent_id') : null,
        ]);

        $request->validate([
            'name'      => ['required', 'string', 'max:255', Rule::unique('designations', 'name')],
            'parent_id' => ['nullable', Rule::exists('tenant.designations', 'id')->where(fn ($q) => $q->whereIn('id', Designation::pluck('id')))],
            'level'     => ['required', 'integer', 'min:0', 'max:' . \App\Services\DesignationLevels::maximum()]
        ]);

        $level = (int) $request->level;

        // Strict level range validation (0 - 6 only)
        if ($level < 0 || $level > \App\Services\DesignationLevels::maximum()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Designation level must be within the configured company limit.'
            ], 422);
        }

        if ($request->filled('parent_id')) {
            $parent = Designation::find($request->parent_id);
            if ($parent) {
                $parentLevel = (int) ($parent->level ?? 0);
                if ($parentLevel >= \App\Services\DesignationLevels::maximum()) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'A designation at the company maximum level cannot have subordinate designations.'
                    ], 422);
                }

                if ($level <= $parentLevel) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Subordinate designation level (' . $level . ') must be greater than parent designation level (Level ' . $parentLevel . '). Minimum allowed level is ' . ($parentLevel + 1) . '.'
                    ], 422);
                }
            }
        }

        try {
            $designation = Designation::create([
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
                'level'           => $level,
                'added_by'        => Auth::id(),
                'last_updated_by' => Auth::id(),
            ]);

            $designation->unique_code = 'DGN-' . str_pad($designation->id, 4, '0', STR_PAD_LEFT);
            $designation->saveQuietly();

            return response()->json([
                'status' => 'success',
                'designation' => [
                    'id'          => $designation->id,
                    'name'        => $designation->name,
                    'level'       => $designation->level,
                    'unique_code' => $designation->unique_code,
                    'parent_id'   => $designation->parent_id,
                ]
            ]);

        } catch (QueryException $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This designation name already exists.'
                ], 422);
            }
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the designation.'
            ], 500);
        }
    }

    public function destroy(Designation $designation)
    {
        return $this->archiveDesignation($designation);
    }

    public function archiveDesignation(Designation $designation)
    {
        self::ensureDesignationColumnsExist();
        $hasArchivedAt = Schema::hasColumn('designations', 'archived_at');
        $hasParentId = Schema::hasColumn('designations', 'parent_id');

        if ($hasArchivedAt && $designation->archived_at) {
            return redirect()->route('designations.index')
                ->with('error', 'Designation is already archived.');
        }

        DB::transaction(function () use ($designation, $hasParentId, $hasArchivedAt) {
            if ($hasParentId) {
                Designation::where('parent_id', $designation->id)->update([
                    'parent_id' => $designation->parent_id,
                    'last_updated_by' => Auth::id(),
                ]);
            }

            $updateData = ['last_updated_by' => Auth::id()];
            if ($hasArchivedAt) {
                $updateData['archived_at'] = now();
            }
            $designation->forceFill($updateData)->save();
        });

        return redirect()->route('designations.index')
            ->with('success', 'Designation archived successfully.');
    }

    public function archive(Request $request)
    {
        self::ensureDesignationColumnsExist();
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 30, 40, 50, 100], true) ? $perPage : 10;

        $hasParentId = Schema::hasColumn('designations', 'parent_id');
        $hasUniqueCode = Schema::hasColumn('designations', 'unique_code');
        $hasArchivedAt = Schema::hasColumn('designations', 'archived_at');

        $with = ['addedBy', 'updatedBy'];
        if ($hasParentId) {
            $with[] = 'parent';
        }

        $query = Designation::with($with);
        if ($hasArchivedAt) {
            $query->whereNotNull('archived_at')->orderByDesc('archived_at');
        } else {
            $query->orderByDesc('id');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $hasUniqueCode, $hasParentId) {
                $q->where('name', 'like', '%' . $search . '%');
                if ($hasUniqueCode) {
                    $q->orWhere('unique_code', 'like', '%' . $search . '%');
                }
                if ($hasParentId) {
                    $q->orWhereHas('parent', function ($parentQuery) use ($search) {
                        $parentQuery->where('name', 'like', '%' . $search . '%');
                    });
                }
            });
        }

        $designations = $query->paginate($perPage)->withQueryString();

        return view('admin.designations.archive', compact('designations'));
    }

    public function restore($id)
    {
        $designation = Designation::whereNotNull('archived_at')->findOrFail($id);

        $designation->forceFill([
            'archived_at' => null,
            'last_updated_by' => Auth::id(),
        ])->save();

        return redirect()->route('designations.archive')
            ->with('success', 'Designation restored successfully.');
    }

        public function bulkDelete(Request $request)
        {
            $ids = $request->input('ids', []);

            if (empty($ids) || !is_array($ids)) {
                return $request->ajax()
                    ? response()->json(['status' => false, 'message' => 'No designations selected.'], 422)
                    : back()->with('error', 'No designations selected.');
            }

            $deleted = 0;
            $blocked = 0;
            $blockedNames = [];

            foreach ($ids as $id) {
                $designation = Designation::find($id);

                if (!$designation) {
                    continue;
                }

                if ($designation->employeeDetails()->count() > 0) {
                    $blocked++;
                    $blockedNames[] = $designation->name;
                } else {
                    $designation->delete();
                    $deleted++;
                }
            }

            // Prepare success and error messages
            $successMessage = '';
            $errorMessage = '';

            if ($deleted > 0) {
                $successMessage = $deleted . ' designation(s) deleted successfully.';
            }

            if ($blocked > 0) {
                $errorMessage = $blocked . ' designation(s) cannot be deleted because employees are tagged under them.';
                if (!empty($blockedNames)) {
                    $errorMessage .= ' (' . implode(', ', $blockedNames) . ')';
                }
            }

            // For AJAX requests
            if ($request->ajax()) {
                if ($blocked > 0 && $deleted == 0) {
                    return response()->json([
                        'status' => false,
                        'message' => $errorMessage
                    ], 422);
                } else {
                    return response()->json([
                        'status' => true,
                        'message' => $successMessage,
                        'error_message' => $blocked > 0 ? $errorMessage : null
                    ]);
                }
            }

            // For regular requests
            if ($deleted > 0) {
                session()->flash('success', $successMessage);
            }

            if ($blocked > 0) {
                session()->flash('error', $errorMessage);
            }

            return back();
        }
    public function bulkArchive(Request $request)
    {
        self::ensureDesignationColumnsExist();
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'No designations selected.');
        }

        $hasArchivedAt = Schema::hasColumn('designations', 'archived_at');
        $hasParentId = Schema::hasColumn('designations', 'parent_id');

        $query = Designation::whereIn('id', $ids);
        if ($hasArchivedAt) {
            $query->whereNull('archived_at');
        }
        $designations = $query->get();

        if ($designations->isEmpty()) {
            return back()->with('error', 'No active designations found for archive.');
        }

        DB::transaction(function () use ($designations, $hasParentId, $hasArchivedAt) {
            foreach ($designations as $designation) {
                if ($hasParentId) {
                    Designation::where('parent_id', $designation->id)->update([
                        'parent_id' => $designation->parent_id,
                        'last_updated_by' => Auth::id(),
                    ]);
                }

                $updateData = ['last_updated_by' => Auth::id()];
                if ($hasArchivedAt) {
                    $updateData['archived_at'] = now();
                }
                $designation->forceFill($updateData)->save();
            }
        });

        return redirect()->route('designations.index')
            ->with('success', $designations->count() . ' designation(s) archived successfully.');
    }

    public function hierarchy()
    {
        self::ensureDesignationColumnsExist();
        $hasParentId = Schema::hasColumn('designations', 'parent_id');
        $hasArchivedAt = Schema::hasColumn('designations', 'archived_at');
        $hasOrder = Schema::hasColumn('designations', 'order');

        $query = Designation::query();
        if ($hasParentId) {
            $query->with('children');
        }
        if ($hasArchivedAt) {
            $query->whereNull('archived_at');
        }
        if ($hasOrder) {
            $query->orderBy('order', 'asc');
        }
        $designations = $query->orderBy('name', 'asc')->get();
        $chartPoints = $this->employeeHierarchyPoints();

        return view('admin.designations.hierarchy', compact('designations', 'chartPoints'));
    }

    public function chartData()
    {
        return response()->json(['points' => $this->employeeHierarchyPoints()]);
    }

    private function employeeHierarchyPoints()
    {
        $employees = User::with(['employeeDetail.designation'])
            ->where('company_id', \App\Services\DesignationLevels::companyId())
            ->where('role', 'employee')
            ->whereNull('archived_at')
            ->whereDoesntHave('employeeDetail', function ($query) {
                $query->where(function ($sub) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('employee_details', 'status')) {
                        $sub->whereIn('status', ['notice', 'probation']);
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('employee_details', 'notice_end_date')) {
                        $sub->orWhereNotNull('notice_end_date');
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn('employee_details', 'probation_end_date')) {
                        $sub->orWhereNotNull('probation_end_date');
                    }
                });
            })
            ->orderBy('name')
            ->get();

        $employeeIds = $employees->pluck('id')->map(fn ($id) => (int) $id)->all();

        return $employees->map(function (User $employee) use ($employeeIds) {
            $reportingTo = (int) ($employee->employeeDetail?->reporting_to ?? 0);
            $designation = $employee->employeeDetail?->designation?->name ?? 'No designation';

            return [
                'id' => 'employee-' . $employee->id,
                'parent' => in_array($reportingTo, $employeeIds, true) ? 'employee-' . $reportingTo : null,
                'name' => $employee->name,
                'level' => $designation,
            ];
        })->values();
    }

    public function saveHierarchy(Request $request)
    {
        $validated = $request->validate([
            'hierarchy' => ['required', 'array'],
            'hierarchy.*.id' => ['required', 'integer', Rule::exists('tenant.designations', 'id')->where(fn ($q) => $q->whereIn('id', Designation::pluck('id')))],
            'hierarchy.*.parent_id' => ['nullable', 'integer', Rule::exists('tenant.designations', 'id')->where(fn ($q) => $q->whereIn('id', Designation::pluck('id')))],
            'hierarchy.*.order' => ['required', 'integer', 'min:0'],
        ]);

        // Build parent map to validate depth & detect cycles
        $parentMap = Designation::pluck('parent_id', 'id')->all();
        $ids = [];
        foreach ($validated['hierarchy'] as $item) {
            $itemId = (int) $item['id'];
            $parentId = $item['parent_id'] !== null ? (int) $item['parent_id'] : null;
            $parentMap[$itemId] = $parentId;
            $ids[] = $itemId;
        }

        // Validate depths & cycles
        $calculatedLevels = [];
        foreach ($ids as $id) {
            $current = $id;
            $depth = 0;
            $visited = [];

            while (isset($parentMap[$current]) && $parentMap[$current] !== null) {
                if (in_array($current, $visited, true)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Circular hierarchy loop detected in organization structure.'
                    ], 422);
                }
                $visited[] = $current;
                $current = $parentMap[$current];
                $depth++;

                if ($depth > \App\Services\DesignationLevels::maximum()) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Hierarchy depth exceeds the configured company designation level limit.'
                    ], 422);
                }
            }
            $calculatedLevels[$id] = $depth;
        }

        // Save hierarchy and synchronize level
        DB::transaction(function () use ($validated, $calculatedLevels) {
            foreach ($validated['hierarchy'] as $item) {
                $itemId = (int) $item['id'];
                $parentId = $item['parent_id'] !== null ? (int) $item['parent_id'] : null;
                $level = $calculatedLevels[$itemId] ?? 0;

                Designation::whereKey($itemId)->update([
                    'parent_id'       => $parentId,
                    'order'           => (int) $item['order'],
                    'level'           => $level,
                    'last_updated_by' => Auth::id(),
                ]);
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Hierarchy saved successfully!'
        ]);
    }
}

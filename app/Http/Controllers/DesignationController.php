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

    public function store(Request $request)
    {
        $request->merge([
            'code_generation_mode' => $request->input('code_generation_mode', 'auto'),
            'unique_code'          => trim((string) $request->input('unique_code', '')),
            'parent_id'            => $request->filled('parent_id') ? $request->input('parent_id') : null,
        ]);

        $request->validate([
            'name'                 => ['required', 'string', 'max:255', Rule::unique('designations', 'name')],
            'parent_id'            => ['nullable', 'exists:designations,id'],
            'level'                => ['required', 'integer', 'min:0', 'max:6'],
            'code_generation_mode' => ['required', Rule::in(['auto', 'custom'])],
            'unique_code'          => [
                'required_if:code_generation_mode,custom',
                'nullable',
                'string',
                'max:255',
                Rule::unique('designations', 'unique_code'),
            ],
        ]);

        try {
            $designationData = [
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
                'level'           => $request->level,
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

        $query = Designation::whereKeyNot($designation->id);
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
                'exists:designations,id',
                Rule::notIn([$designation->id]),
            ],
            'level'     => ['required', 'integer', 'min:0', 'max:6']
        ]);

        try {
            $designation->update([
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
                'level'           => $request->level,
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
            'parent_id' => ['nullable', 'exists:designations,id'],
            'level'     => ['required', 'integer', 'min:0', 'max:6']
        ]);

        try {
            $designation = Designation::create([
                'name'            => $request->name,
                'parent_id'       => $request->parent_id ?: null,
                'level'           => $request->level,
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
            'hierarchy.*.id' => ['required', 'integer', 'exists:designations,id'],
            'hierarchy.*.parent_id' => ['nullable', 'integer', 'exists:designations,id'],
            'hierarchy.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['hierarchy'] as $item) {
            Designation::whereKey($item['id'])->update([
                'parent_id' => $item['parent_id'],
                'order' => $item['order'],
                'last_updated_by' => Auth::id(),
            ]);
        }

        return response()->json(['message' => 'Hierarchy saved successfully!']);
    }
}

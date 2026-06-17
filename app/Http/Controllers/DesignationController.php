<?php

namespace App\Http\Controllers;

use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class DesignationController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        // FIX 1: Order by level to ensure proper display
        $designations = Designation::with(['addedBy', 'updatedBy'])
            ->whereNull('archived_at')
            ->orderBy('level', 'asc')  // ADD THIS LINE
            ->orderBy('name', 'asc')   // ADD THIS LINE
            ->paginate($perPage);

        // Get unique levels count
        $levelsCount = Designation::whereNull('archived_at')->distinct('level')->count('level');
        $archivedCount = Designation::whereNotNull('archived_at')->count();

        return view('admin.designations.index', compact('designations', 'levelsCount', 'archivedCount'));
    }

    public function show(Designation $designation)
    {
        $designation->load(['addedBy', 'updatedBy', 'parent', 'employeeDetails']);
        $hierarchy = $this->getHierarchyTree($designation);
        return view('admin.designations.show', compact('designation', 'hierarchy'));
    }

    private function getHierarchyTree(Designation $designation)
    {
        $ancestors = collect();
        $current = $designation;

        while ($current->parent) {
            $ancestors->prepend($current->parent);
            $current = $current->parent;
        }

        $children = $designation->children;
        $descendants = $designation->descendants;

        return [
            'ancestors' => $ancestors,
            'children' => $children,
            'descendants' => $descendants,
        ];
    }

    public function create()
    {
        $designations = Designation::whereNull('archived_at')->get();
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
            'unique_code' => trim((string) $request->input('unique_code', '')),
        ]);

        $request->validate([
            'name'      => ['required','string','max:255', Rule::unique('designations','name')],
            'parent_id' => ['nullable','exists:designations,id'],
            'level'     => ['required','integer','min:0','max:6'],
            'code_generation_mode' => ['required', Rule::in(['auto', 'custom'])],
            'unique_code' => [
                'required_if:code_generation_mode,custom',
                'nullable',
                'string',
                'max:255',
                Rule::unique('designations', 'unique_code'),
            ],
        ]);

        try {
            $designationData = [
                'name'        => $request->name,
                'parent_id'   => $request->parent_id ?: null,
                'level'       => $request->level,
                'added_by'    => Auth::id(),
                'updated_by'  => Auth::id(),  // FIX 2: Changed from 'last_updated_by' to 'updated_by'
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
                        'unique_code' => $designation->unique_code
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

        // FIX 3: Add timestamp to prevent caching
        return redirect()->route('designations.index', ['t' => time()])
            ->with('success', 'Designation added successfully.');
    }

    public function edit(Designation $designation)
    {
        $designations = Designation::whereNull('archived_at')
            ->whereKeyNot($designation->id)
            ->get();
        return view('admin.designations.create', compact('designation', 'designations'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'name' => [
                'required','string','max:255',
                Rule::unique('designations', 'name')->ignore($designation->id)
            ],
            'parent_id' => ['nullable','exists:designations,id'],
            'level'     => ['required','integer','min:0','max:6']
        ]);

        try {
            $designation->update([
                'name'        => $request->name,
                'parent_id'   => $request->parent_id ?: null,
                'level'       => $request->level,
                'updated_by'  => Auth::id(),  // FIX 4: Changed from 'last_updated_by' to 'updated_by'
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'designation' => [
                        'id'          => $designation->id,
                        'name'        => $designation->name,
                        'level'       => $designation->level,
                        'unique_code' => $designation->unique_code
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

        // FIX 5: Add timestamp and updated_id to prevent caching
        return redirect()->route('designations.index', ['t' => time()])
            ->with('success', 'Designation updated successfully.')
            ->with('updated_id', $designation->id);  // Send back ID for potential JS update
    }

    public function ajaxStore(Request $request)
    {
        $request->validate([
            'name'  => ['required','string','max:255', Rule::unique('designations','name')],
            'level' => ['required','integer','min:0','max:6']
        ]);

        try {
            $designation = Designation::create([
                'name'       => $request->name,
                'level'      => $request->level,
                'added_by'   => Auth::id(),
                'updated_by' => Auth::id(),  // FIX 6: Consistency
            ]);

            $designation->unique_code = 'DGN-' . str_pad($designation->id, 4, '0', STR_PAD_LEFT);
            $designation->saveQuietly();

            return response()->json([
                'status' => 'success',
                'designation' => [
                    'id'          => $designation->id,
                    'name'        => $designation->name,
                    'level'       => $designation->level,
                    'unique_code' => $designation->unique_code
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
        if ($designation->archived_at) {
            return redirect()->route('designations.index')
                ->with('error', 'Designation is already archived.');
        }

        DB::transaction(function () use ($designation) {
            Designation::where('parent_id', $designation->id)->update([
                'parent_id' => $designation->parent_id,
                'last_updated_by' => Auth::id(),
            ]);

            $designation->forceFill([
                'archived_at' => now(),
                'last_updated_by' => Auth::id(),
            ])->save();
        });

        return redirect()->route('designations.index')
            ->with('success', 'Designation archived successfully.');
    }

    public function archive(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 30, 40, 50, 100], true) ? $perPage : 10;

        $query = Designation::with(['addedBy', 'updatedBy', 'parent'])
            ->whereNotNull('archived_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('unique_code', 'like', '%' . $search . '%')
                    ->orWhereHas('parent', function ($parentQuery) use ($search) {
                        $parentQuery->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $designations = $query->orderByDesc('archived_at')->paginate($perPage)->withQueryString();

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
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'No designations selected.');
        }

        $designations = Designation::whereNull('archived_at')
            ->whereIn('id', $ids)
            ->get();

        if ($designations->isEmpty()) {
            return back()->with('error', 'No active designations found for archive.');
        }

        DB::transaction(function () use ($designations) {
            foreach ($designations as $designation) {
                Designation::where('parent_id', $designation->id)->update([
                    'parent_id' => $designation->parent_id,
                    'last_updated_by' => Auth::id(),
                ]);

                $designation->forceFill([
                    'archived_at' => now(),
                    'last_updated_by' => Auth::id(),
                ])->save();
            }
        });

        return redirect()->route('designations.index')
            ->with('success', $designations->count() . ' designation(s) archived successfully.');
    }

    public function hierarchy()
    {
        $designations = Designation::with('children')
            ->whereNull('archived_at')
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
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
                $query->whereIn('status', ['notice', 'probation'])
                    ->orWhereNotNull('notice_end_date')
                    ->orWhereNotNull('probation_end_date');
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

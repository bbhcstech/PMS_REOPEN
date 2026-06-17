<?php

namespace App\Http\Controllers;

use App\Models\ParentDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ParentDepartmentController extends Controller
{
    /**
     * Display a listing of parent departments.
     */
    public function index(Request $request)
    {
        $departments = ParentDepartment::withCount(['departments', 'employees'])
            ->whereNull('archived_at')
            ->orderBy('id', 'desc')
            ->get();
        $archivedCount = ParentDepartment::whereNotNull('archived_at')->count();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['data' => $departments]);
        }

        return view('admin.parent_departments.index', compact('departments', 'archivedCount'));
    }

    /**
     * Show the form for creating a new parent department.
     * Pre-computes next code for preview.
     */
    public function create()
    {
        $nextCode = $this->computeNextCode();
        return view('admin.parent_departments.create', compact('nextCode'));
    }

    /**
     * Display the specified parent department.
     */
    public function show(ParentDepartment $parentDepartment)
    {
        $parentDepartment->load(['addedBy', 'updatedBy'])
            ->loadCount(['departments', 'employees']);

        return view('admin.parent_departments.show', compact('parentDepartment'));
    }

    /**
     * Return next available code as JSON for AJAX usage.
     */
    public function nextCode(Request $request): JsonResponse
    {
        $nextCode = $this->computeNextCode();
        return response()->json(['next_code' => $nextCode]);
    }

    /**
     * Store a newly created parent department in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'code_generation_mode' => $request->input('code_generation_mode', 'auto'),
            'dpt_code' => trim((string) $request->input('dpt_code', '')),
        ]);

        $request->validate([
            'dpt_name' => 'required|string|max:255',
            'code_generation_mode' => ['required', Rule::in(['auto', 'custom'])],
            'dpt_code' => [
                'required_if:code_generation_mode,custom',
                'nullable',
                'string',
                'max:50',
                Rule::unique('parent_departments', 'dpt_code'),
            ],
        ]);

        $prefix = 'DEP-';
        $pad = 4;

        DB::beginTransaction();
        try {
            if ($request->code_generation_mode === 'custom') {
                $generatedCode = trim((string) $request->dpt_code);
            } else {
                // compute numeric max safely within transaction to avoid race conditions
                $max = ParentDepartment::where('dpt_code', 'like', $prefix . '%')
                    ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(dpt_code, ?) AS UNSIGNED)), 0) as mx', [strlen($prefix) + 1])
                    ->lockForUpdate()
                    ->value('mx');

                $nextNumber = ((int) $max) + 1;
                $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);
            }

            // create in parent_departments table via model
            $dpt = ParentDepartment::create([
                'dpt_name' => $request->dpt_name,
                'dpt_code' => $generatedCode,
                'added_by' => Auth::id(),
            ]);

            DB::commit();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success', 'dpt' => $dpt]);
            }

            return redirect()->route('parent-departments.index')->with('success', 'Parent department created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('ParentDepartment store error: ' . $e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Failed to create department.', 'error' => $e->getMessage()], 500);
            }

            return back()->withErrors('Failed to create department: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for editing the specified parent department.
     */
    public function edit(ParentDepartment $parentDepartment)
    {
        // pass nextCode too if your form expects it; not required for edits
        $nextCode = $this->computeNextCode();
        return view('admin.parent_departments.create', compact('parentDepartment', 'nextCode'));
    }

    /**
     * Update specified parent department.
     */
    public function update(Request $request, ParentDepartment $parentDepartment)
    {
        $request->merge([
            'dpt_code' => trim((string) $request->input('dpt_code', $parentDepartment->dpt_code)),
        ]);

        $request->validate([
            'dpt_name' => 'required|string|max:255',
            'dpt_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('parent_departments', 'dpt_code')->ignore($parentDepartment->id),
            ],
        ]);

        try {
            $parentDepartment->update([
                'dpt_code' => $request->dpt_code,
                'dpt_name' => $request->dpt_name,
                'last_updated_by' => Auth::id(),
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success', 'dpt' => $parentDepartment->fresh()]);
            }

            return redirect()->route('parent-departments.index')->with('success', 'Updated successfully.');
        } catch (\Throwable $e) {
            logger()->error('ParentDepartment update error: ' . $e->getMessage(), ['exception' => $e]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Update failed.', 'error' => $e->getMessage()], 500);
            }

            return back()->withErrors('Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified parent department.
     * Prevent deletion if sub-departments OR employees exist.
     */
    // public function destroy(Request $request, ParentDepartment $parentDepartment)
    // {
    //     try {
    //         // Check if department has sub-departments
    //         if ($parentDepartment->departments()->exists()) {
    //             $message = 'This department cannot be deleted because it has sub-departments linked to it.';

    //             if ($request->wantsJson() || $request->ajax()) {
    //                 return response()->json(['status' => 'error', 'message' => $message], 422);
    //             }

    //             return back()->with('error', $message);
    //         }

    //         // Check if department has employees
    //         if ($parentDepartment->employees()->exists()) {
    //             $message = 'This department cannot be deleted because it is tagged with employees.';

    //             if ($request->wantsJson() || $request->ajax()) {
    //                 return response()->json(['status' => 'error', 'message' => $message], 422);
    //             }

    //             return back()->with('error', $message);
    //         }

    //         $parentDepartment->delete();

    //         if ($request->wantsJson() || $request->ajax()) {
    //             return response()->json(['status' => 'success', 'message' => 'Department deleted successfully.']);
    //         }

    //         return redirect()->route('parent-departments.index')->with('success', 'Department deleted successfully.');
    //     } catch (\Throwable $e) {
    //         logger()->error('ParentDepartment delete error: ' . $e->getMessage(), ['exception' => $e]);

    //         if ($request->wantsJson() || $request->ajax()) {
    //             return response()->json(['status' => 'error', 'message' => 'Delete failed.', 'error' => $e->getMessage()], 500);
    //         }

    //         return back()->with('error', 'Delete failed: ' . $e->getMessage());
    //     }
    // }




    public function destroy(Request $request, ParentDepartment $parentDepartment)
{
    try {
        $parentDepartment->forceFill([
            'archived_at' => now(),
            'last_updated_by' => Auth::id(),
        ])->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Department archived successfully.',
                'deleted_id' => $parentDepartment->id
            ]);
        }

        return redirect()->route('parent-departments.index')
            ->with('success', 'Department archived successfully.');
    } catch (\Throwable $e) {
        logger()->error('ParentDepartment archive error: ' . $e->getMessage());

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Archive failed: ' . $e->getMessage()
            ], 500);
        }

        return back()->with('error', 'Archive failed: ' . $e->getMessage());
    }
}

    public function archive(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 30, 40, 50, 100], true) ? $perPage : 10;

        $query = ParentDepartment::withCount(['departments', 'employees'])
            ->whereNotNull('archived_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('dpt_name', 'like', '%' . $search . '%')
                    ->orWhere('dpt_code', 'like', '%' . $search . '%');
            });
        }

        $departments = $query->orderByDesc('archived_at')->paginate($perPage)->withQueryString();

        return view('admin.parent_departments.archive', compact('departments'));
    }

    public function restore($id)
    {
        $department = ParentDepartment::whereNotNull('archived_at')->findOrFail($id);

        $department->forceFill([
            'archived_at' => null,
            'last_updated_by' => Auth::id(),
        ])->save();

        return redirect()->route('parent-departments.archive')
            ->with('success', 'Parent department restored successfully.');
    }

    /**
     * Bulk delete multiple ParentDepartment records.
     * Input expects bulk_ids => array or comma separated string.
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('bulk_ids', []);

        if (!is_array($ids)) {
            if (is_string($ids)) {
                $ids = array_filter(array_map('trim', explode(',', $ids)));
            } else {
                $ids = [$ids];
            }
        }

        $ids = array_values(array_filter(array_map(function ($v) {
            return is_numeric($v) ? (int) $v : null;
        }, $ids)));

        if (empty($ids)) {
            $msg = 'No departments selected for archive.';
            return $request->wantsJson() || $request->ajax()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->with('error', $msg);
        }

        DB::beginTransaction();
        try {
            $parents = ParentDepartment::withCount(['departments', 'employees'])
                ->whereNull('archived_at')
                ->whereIn('id', $ids)
                ->get();

            $foundIds = $parents->pluck('id')->map(fn($v) => (int) $v)->toArray();
            $missing = array_values(array_diff($ids, $foundIds));

            if (!empty($missing)) {
                $msg = 'Some selected items do not exist: ' . implode(', ', $missing);
                DB::rollBack();
                return $request->wantsJson() || $request->ajax()
                    ? response()->json(['status' => 'error', 'message' => $msg], 422)
                    : back()->with('error', $msg);
            }

            $deletableIds = $parents->pluck('id')->all();
            $deletedCount = 0;
            if (!empty($deletableIds)) {
                $deletedCount = ParentDepartment::whereIn('id', $deletableIds)->update([
                    'archived_at' => now(),
                    'last_updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            $blockedBySubDepartments = collect();
            $blockedByEmployees = collect();
            $blockedSubCount = 0;
            $blockedEmpCount = 0;
            $totalBlocked = $blockedSubCount + $blockedEmpCount;

            $messageParts = [];

            if ($deletedCount > 0) {
                $messageParts[] = "{$deletedCount} department(s) archived successfully.";
            }

            if ($blockedSubCount > 0) {
                $blockedSubList = $blockedBySubDepartments->pluck('dpt_name')->implode(', ');
                $messageParts[] = "{$blockedSubCount} department(s) cannot be deleted because they have sub-departments: {$blockedSubList}";
            }

            if ($blockedEmpCount > 0) {
                $blockedEmpList = $blockedByEmployees->pluck('dpt_name')->implode(', ');
                $messageParts[] = "{$blockedEmpCount} department(s) cannot be deleted because they are tagged with employees: {$blockedEmpList}";
            }

            $finalMessage = implode(' ', $messageParts);

            // If nothing was deleted but items were blocked
            if ($deletedCount === 0 && $totalBlocked > 0) {
                $status = 'warning';
            } else {
                $status = 'success';
            }

            return $request->wantsJson() || $request->ajax()
                ? response()->json([
                    'status' => $status,
                    'deleted' => $deletedCount,
                    'blocked_sub_departments' => $blockedSubCount,
                    'blocked_employees' => $blockedEmpCount,
                    'blocked_sub_names' => $blockedBySubDepartments->pluck('dpt_name')->values(),
                    'blocked_emp_names' => $blockedByEmployees->pluck('dpt_name')->values(),
                    'deleted_ids' => $deletableIds,
                    'message' => $finalMessage,
                ])
                : redirect()->route('parent-departments.index')->with($status, $finalMessage);
        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('ParentDepartment bulk archive error: ' . $e->getMessage(), ['exception' => $e]);

            return $request->wantsJson() || $request->ajax()
                ? response()->json(['status' => 'error', 'message' => 'Bulk archive failed.', 'error' => $e->getMessage()], 500)
                : back()->with('error', 'Bulk archive failed: ' . $e->getMessage());
        }
    }

    /**
     * Compute next department code using numeric max of existing suffixes.
     * Returns string like DEP-0001
     */
    protected function computeNextCode(string $prefix = 'DEP-', int $pad = 4): string
    {
        $max = ParentDepartment::where('dpt_code', 'like', $prefix . '%')
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(dpt_code, ?) AS UNSIGNED)), 0) as mx', [strlen($prefix) + 1])
            ->value('mx');

        $next = ((int) $max) + 1;
        return $prefix . str_pad($next, $pad, '0', STR_PAD_LEFT);
    }
}

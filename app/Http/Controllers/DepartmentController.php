<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ParentDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    /**
     * Show all departments
     */
    public function index()
    {
       $departments = Department::with('parent')
           ->withCount('employeeDetails')
           ->whereNull('archived_at')
           ->latest()
           ->get();
       $archivedCount = Department::whereNotNull('archived_at')->count();

        return view('admin.departments.index', compact('departments', 'archivedCount'));
    }

    /**
     * Show create form with next preview code
     */
    public function create()
    {
        $parentDepartments = ParentDepartment::all();
        $nextCode = $this->generateNextCodePreview();

        return view('admin.departments.create', compact('parentDepartments', 'nextCode'));
    }

    /**
     * Display the specified sub department.
     */
    public function show(Department $department)
    {
        $department->load(['parent', 'addedBy', 'updatedBy'])
            ->loadCount('employeeDetails');

        return view('admin.departments.show', compact('department'));
    }

    /**
     * Generate next automatic preview code (no DB lock)
     */
    private function generateNextCodePreview()
    {
        $prefix = 'SUB-';

        $last = Department::where('dpt_code', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) {
            $num = (int)$m[1] + 1;
        } else {
            $num = 1;
        }

        $pad = $num > 99 ? 3 : 2;

        return $prefix . str_pad($num, $pad, '0', STR_PAD_LEFT);
    }

    /**
     * Store new Department
     */
    public function store(Request $request)
    {
        $request->merge([
            'code_generation_mode' => $request->input('code_generation_mode', 'auto'),
            'dpt_code' => trim((string) $request->input('dpt_code', '')),
        ]);

        $request->validate([
            'dpt_name' => 'required|string|max:255',
            'parent_dpt_id' => 'nullable|exists:parent_departments,id',
            'code_generation_mode' => ['required', Rule::in(['auto', 'custom'])],
            'dpt_code' => [
                'required_if:code_generation_mode,custom',
                'nullable',
                'string',
                'max:50',
                Rule::unique('departments', 'dpt_code'),
            ],
        ]);

        $prefix = 'SUB-';  // SAME prefix for preview + save

        DB::beginTransaction();

        try {

            if ($request->code_generation_mode === 'custom') {
                $generatedCode = trim((string) $request->dpt_code);
            } else {
                // Lock row for concurrency safety
                $last = Department::where('dpt_code', 'like', $prefix . '%')
                    ->orderBy('id', 'desc')
                    ->lockForUpdate()
                    ->first();

                if ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) {
                    $nextNumber = (int)$m[1] + 1;
                } else {
                    $nextNumber = 1;
                }

                $pad = $nextNumber > 99 ? 3 : 2;

                $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);

                // EXTRA CHECK If duplicate somehow exists
                while (Department::where('dpt_code', $generatedCode)->exists()) {
                    $nextNumber++;
                    $pad = $nextNumber > 99 ? 3 : $pad;
                    $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);
                }
            }

            Department::create([
                'dpt_name'       => $request->dpt_name,
                'dpt_code'       => $generatedCode,
                'parent_dpt_id'  => $request->parent_dpt_id,
                'added_by'       => Auth::id(),
            ]);

            DB::commit();

            return redirect()
                ->route('departments.index')
                ->with('success', 'Department created successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();
            return redirect()
                ->back()
                ->with('error', 'Failed to create department. Please try again.');
        }
    }

    /**
     * Edit Department
     */
    public function edit(Department $department)
    {
        $parentDepartments = ParentDepartment::all();
        return view('admin.departments.edit', compact('department', 'parentDepartments'));
    }

    /**
     * Update Department
     */
    public function update(Request $request, Department $department)
    {
        $request->merge([
            'dpt_code' => trim((string) $request->input('dpt_code', $department->dpt_code)),
        ]);

        $request->validate([
            'dpt_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments', 'dpt_code')->ignore($department->id),
            ],
            'dpt_name' => 'required|string|max:255',
            'parent_dpt_id' => 'required|exists:parent_departments,id',
        ]);

        $department->update([
            'dpt_code'        => $request->dpt_code,
            'dpt_name'        => $request->dpt_name,
            'parent_dpt_id'   => $request->parent_dpt_id,
            'last_updated_by' => Auth::id(),
        ]);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated.');
    }

    /**
     * Delete single department (must respect employee rule)
     */
    public function destroy(Request $request, Department $department)
    {
        try {
            // RULE: cannot delete if any employees exist under this department
            if ($department->employeeDetails()->exists()) {
                $message = 'Sub Department cannot be archived because Employees are tagged under it.';

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => $message,
                    ], 422);
                }

                return back()->withErrors($message);
            }

            $department->forceFill([
                'archived_at' => now(),
                'last_updated_by' => Auth::id(),
            ])->save();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => 'Sub Department archived successfully',
                ]);
            }

            return redirect()
                ->route('departments.index')
                ->with('success', 'Sub Department archived successfully');

        } catch (\Throwable $e) {

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Archive failed.',
                    'error'   => $e->getMessage(),
                ], 500);
            }

            return back()->withErrors('Archive failed: ' . $e->getMessage());
        }
    }

    /**
     * Show archived sub departments.
     */
    public function archive(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 30, 40, 50, 100], true) ? $perPage : 10;

        $query = Department::with('parent')
            ->withCount('employeeDetails')
            ->whereNotNull('archived_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('dpt_name', 'like', '%' . $search . '%')
                    ->orWhere('dpt_code', 'like', '%' . $search . '%')
                    ->orWhereHas('parent', function ($parentQuery) use ($search) {
                        $parentQuery->where('dpt_name', 'like', '%' . $search . '%')
                            ->orWhere('dpt_code', 'like', '%' . $search . '%');
                    });
            });
        }

        $departments = $query->orderByDesc('archived_at')->paginate($perPage)->withQueryString();

        return view('admin.departments.archive', compact('departments'));
    }

    /**
     * Restore an archived sub department.
     */
    public function restore($id)
    {
        $department = Department::whereNotNull('archived_at')->findOrFail($id);

        $department->forceFill([
            'archived_at' => null,
            'last_updated_by' => Auth::id(),
        ])->save();

        return redirect()->route('departments.archive')
            ->with('success', 'Sub Department restored successfully.');
    }


    /**
     * Bulk archive departments.
     * Must respect: cannot archive departments that have employees.
     */
    public function bulkDestroy(Request $request)
    {
        \Log::debug('bulkDestroy payload', $request->all());

        $ids = $request->input('bulk_ids', []);

        if (!is_array($ids)) {
            $ids = is_string($ids)
                ? array_filter(array_map('trim', explode(',', $ids)))
                : [$ids];
        }

        // Normalize integers
        $ids = array_values(array_filter(array_map(fn($v) => is_numeric($v) ? (int)$v : null, $ids)));

        if (empty($ids)) {
            $msg = 'No IDs provided.';

            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors('No departments selected for archive.');
        }

        $foundIds = Department::whereNull('archived_at')
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn($v) => (int)$v)
            ->toArray();

        $missing = array_values(array_diff($ids, $foundIds));
        if (!empty($missing)) {
            $msg = 'Some selected items do not exist: ' . implode(', ', $missing);

            return $request->wantsJson()
                ? response()->json(['status' => 'error', 'message' => $msg], 422)
                : back()->withErrors($msg);
        }

        DB::beginTransaction();

        try {
            // Load with count of employees
            $departments = Department::withCount('employeeDetails')
                ->whereNull('archived_at')
                ->whereIn('id', $foundIds)
                ->get();

            // Blocked: have employees
            $blocked   = $departments->where('employee_details_count', '>', 0);
            // Archivable: no employees
            $deletable = $departments->where('employee_details_count', 0);

            $deletableIds = $deletable->pluck('id')->all();

            $deleted = 0;
            if (!empty($deletableIds)) {
                $deleted = Department::whereIn('id', $deletableIds)->update([
                    'archived_at' => now(),
                    'last_updated_by' => Auth::id(),
                ]);
            }

            DB::commit();

            $blockedCount = $blocked->count();

            $messageParts   = [];
            $messageParts[] = "$deleted item(s) archived.";
            if ($blockedCount > 0) {
                $blockedList   = $blocked->pluck('dpt_name')->implode(', ');
                $messageParts[] = "$blockedCount item(s) were not archived because Employees are tagged under them: $blockedList";
            }
            $finalMessage = implode(' ', $messageParts);

            return $request->wantsJson()
                ? response()->json([
                    'status'        => 'success',
                    'deleted'       => $deleted,
                    'blocked'       => $blockedCount,
                    'blocked_names' => $blocked->pluck('dpt_name')->values(),
                    'deleted_ids'   => $deletableIds,
                    'message'       => $finalMessage,
                ])
                : redirect()->route('departments.index')->with('success', $finalMessage);

        } catch (\Throwable $e) {

            DB::rollBack();
            \Log::error('bulkDestroy error', ['error' => $e->getMessage()]);

            return $request->wantsJson()
                ? response()->json([
                    'status'  => 'error',
                    'message' => 'Bulk archive failed.',
                    'error'   => $e->getMessage()
                ], 500)
                : back()->withErrors('Bulk archive failed: ' . $e->getMessage());
        }
    }
}

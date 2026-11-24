<?php

namespace App\Http\Controllers\Concerns\Employee;

use App\Http\Controllers\DepartmentController;
use App\Models\Country;
use App\Models\Department;
use App\Models\Designation;
use App\Models\ParentDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait EmployeeOrganizationFields
{
    /**
     * Store a new Department (called from the Add Department modal)
     */
    public function storeDepartment(Request $request)
    {
        // basic validation for dpt_name first
        $request->validate([
            'dpt_name' => 'required|string|max:191',
            'parent_dpt_id' => 'nullable', // we'll validate existence with custom logic below
        ]);

        // custom existence check: allow parent_dpt_id to exist in either parent_departments.id OR departments.id
        $parentId = $request->input('parent_dpt_id', null);
        if (!is_null($parentId) && $parentId !== '') {
            $existsInParent = \DB::table('parent_departments')->where('id', $parentId)->exists();
            $existsInDepartments = \DB::table('departments')->where('id', $parentId)->exists();

            if (! $existsInParent && ! $existsInDepartments) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The selected parent department does not exist.'
                ], 422);
            }
        } else {
            $parentId = null;
        }

        DB::beginTransaction();
        try {
            // Generate next sequential sub-department code (matching DepartmentController logic)
            $departmentCode = $this->generateNextSubDepartmentCode();

            $dpt = Department::create([
                'dpt_name' => $request->input('dpt_name'),
                'parent_dpt_id' => $parentId,
                'dpt_code' => $departmentCode,
                'added_by' => auth()->id() ?? null,
                'last_updated_by' => auth()->id() ?? null,
                'status' => 'Active' // Added status field
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'dpt' => [
                    'id' => $dpt->id,
                    'dpt_name' => $dpt->dpt_name,
                    'parent_dpt_id' => $dpt->parent_dpt_id,
                    // parent_name: try to fetch from parent_departments first, then from departments as fallback
                    'parent_name' => optional(\DB::table('parent_departments')->where('id', $dpt->parent_dpt_id)->first())->dpt_name
                                     ?? optional(\DB::table('departments')->where('id', $dpt->parent_dpt_id)->first())->dpt_name
                                     ?? null,
                    'dpt_code' => $dpt->dpt_code,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('storeDepartment error', ['error' => $e->getMessage(), 'payload' => $request->all()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Server error while creating department: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Return sub-departments for a parent department (AJAX)
     */
    public function getSubDepartments($parentId)
    {
        // ensure numeric parent id
        if (!is_numeric($parentId)) {
            return response()->json([], 400);
        }

        $subs = Department::where('parent_dpt_id', $parentId)
                ->when(Schema::hasColumn('departments', 'dpt_name'), fn ($q) => $q->orderBy('dpt_name'), fn ($q) => $q->orderBy('id'))
                ->get(Schema::hasColumn('departments', 'dpt_name') ? ['id', 'dpt_name', 'dpt_code'] : ['id']);

        return response()->json($subs);
    }

    /**
     * AJAX: create a designation (used by the employee create page "Add" button).
     * Returns JSON { designation: { id, name, unique_code, parent_id, ... } } on success.
     */
    public function storeDesignation(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->only(['name', 'parent_id', 'level', 'status']);

        $validator = \Validator::make($data, [
            'name'      => 'required|string|max:191|unique:designations,name',
            'parent_id' => ['nullable', \Illuminate\Validation\Rule::exists('tenant.designations', 'id')->where(fn ($query) => $query->whereIn('id', Designation::pluck('id')))],
            'level'     => 'required|integer|min:0|max:' . \App\Services\DesignationLevels::maximum(),
            'status'    => 'nullable|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        $level = (int) $data['level'];
        if ($level < 0 || $level > \App\Services\DesignationLevels::maximum()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => ['level' => ['Designation level must be within the configured company limit.']]
            ], 422);
        }

        if (!empty($data['parent_id'])) {
            $parent = \App\Models\Designation::find($data['parent_id']);
            if ($parent) {
                $parentLevel = (int) ($parent->level ?? 0);
                if ($parentLevel >= \App\Services\DesignationLevels::maximum()) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors'  => ['parent_id' => ['A designation at the company maximum level cannot have subordinate designations.']]
                    ], 422);
                }

                if ($level <= $parentLevel) {
                    return response()->json([
                        'message' => 'Validation failed',
                        'errors'  => ['level' => ['Subordinate designation level (' . $level . ') must be greater than parent designation level (Level ' . $parentLevel . '). Minimum allowed level is ' . ($parentLevel + 1) . '.']]
                    ], 422);
                }
            }
        }

        try {

            $designation = \App\Models\Designation::create([
                'name' => $data['name'],
                'parent_id' => $data['parent_id'] ?? null,
                'level' => $level,
                'status' => $data['status'] ?? 'Active',
                'added_by' => auth()->id()
            ]);

            $designation->refresh();

            return response()->json([
                'status' => 'success',
                'designation' => $designation
            ], 201);

        } catch (\Exception $e) {

            \Log::error('Designation AJAX failed', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Could not create designation'
            ], 500);
        }
    }

    /**
     * AJAX: create a country (used by the employee create page "Add" button).
     * Returns JSON { country: { id, name, ... } } on success.
     */
    public function storeCountry(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->only(['name', 'status']);

        $validator = \Validator::make($data, [
            'name'   => 'required|string|max:191|unique:countries,name',
            'status' => 'nullable|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            $country = Country::create([
                'name' => $data['name'],
                'status' => $data['status'] ?? 'Active',
                'added_by' => auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'country' => $country
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to store country via AJAX', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create country'
            ], 500);
        }
    }

    /**
     * AJAX: create a parent department (used by the employee create page "Add" button).
     * Returns JSON { department: { id, dpt_name, dpt_code, ... } } on success.
     */
    public function storeParentDepartment(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->only(['dpt_name', 'status']);

        $validator = \Validator::make($data, [
            'dpt_name' => 'required|string|max:191|unique:parent_departments,dpt_name',
            'status'   => 'nullable|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $departmentCode = $this->generateNextParentDepartmentCode();

            $department = ParentDepartment::create([
                'dpt_name' => $data['dpt_name'],
                'dpt_code' => $departmentCode,
                'status' => $data['status'] ?? 'Active',
                'added_by' => auth()->id() ?? null
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'department' => $department
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to store parent department via AJAX', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create department'
            ], 500);
        }
    }

    /**
     * AJAX: create a sub-department (used by the employee create page "Add" button).
     * Returns JSON { department: { id, dpt_name, dpt_code, parent_dpt_id, ... } } on success.
     */
    public function storeSubDepartment(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->only(['dpt_name', 'parent_dpt_id', 'status']);

        $validator = \Validator::make($data, [
            'dpt_name'      => 'required|string|max:191',
            'parent_dpt_id' => 'required|exists:parent_departments,id',
            'status'        => 'nullable|in:Active,Inactive'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $departmentCode = $this->generateNextSubDepartmentCode();

            $department = Department::create([
                'dpt_name' => $data['dpt_name'],
                'parent_dpt_id' => $data['parent_dpt_id'],
                'dpt_code' => $departmentCode,
                'status' => $data['status'] ?? 'Active',
                'added_by' => auth()->id() ?? null
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'department' => $department
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to store sub-department via AJAX', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create sub-department'
            ], 500);
        }
    }

    /**
     * Generate next sequential parent department code (DEP-0001, DEP-0002, etc.)
     */
    private function generateNextParentDepartmentCode()
    {
        $prefix = 'DEP-';  // Prefix for parent departments

        return DB::transaction(function () use ($prefix) {
            // Lock for update to prevent concurrent insertions
            $last = ParentDepartment::where('dpt_code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) {
                $nextNumber = (int)$m[1] + 1;
            } else {
                $nextNumber = 1;
            }

            // Determine padding (4 digits for 0001 format)
            $pad = 4;

            $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);

            // EXTRA CHECK: If duplicate somehow exists
            while (ParentDepartment::where('dpt_code', $generatedCode)->exists()) {
                $nextNumber++;
                $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);
            }

            return $generatedCode;
        }, 5); // retry up to 5 times on deadlock
    }

    /**
     * Generate next sequential sub-department code (SUB-01, SUB-02, etc.)
     */
    private function generateNextSubDepartmentCode()
    {
        $prefix = 'SUB-';  // SAME prefix as in DepartmentController

        return DB::transaction(function () use ($prefix) {
            // Lock row for concurrency safety - same logic as DepartmentController
            $last = Department::where('dpt_code', 'like', $prefix . '%')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($last && preg_match('/(\d+)$/', $last->dpt_code, $m)) {
                $nextNumber = (int)$m[1] + 1;
            } else {
                $nextNumber = 1;
            }

            // Determine padding (2 digits for 01, 02... 3 digits for 100+)
            $pad = $nextNumber > 99 ? 3 : 2;

            $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);

            // EXTRA CHECK: If duplicate somehow exists
            while (Department::where('dpt_code', $generatedCode)->exists()) {
                $nextNumber++;
                $pad = $nextNumber > 99 ? 3 : $pad;
                $generatedCode = $prefix . str_pad($nextNumber, $pad, '0', STR_PAD_LEFT);
            }

            return $generatedCode;
        }, 5); // retry up to 5 times on deadlock
    }
}

<?php

namespace App\Http\Controllers\Concerns\Payroll;

use App\Models\SalaryStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait SalaryStructures
{
    /**
     * 2. Salary Structures
     */
    public function salaryStructures(Request $request)
    {
        $this->authorizePayroll('payroll');
        $companyId = $this->selectedCompanyId($request);

        $query = SalaryStructure::with(['designation', 'assignments'])
            ->when($companyId, function ($q) use ($companyId) {
                if (Schema::hasColumn('salary_structures', 'company_id')) {
                    $q->where('company_id', $companyId);
                }
            });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('grade', 'LIKE', "%{$search}%")
                  ->orWhereHas('designation', fn ($d) => $d->where('name', 'LIKE', "%{$search}%"));
            });
        }

        if ($request->filled('designation_id') && $request->input('designation_id') !== 'all') {
            $query->where('designation_id', $request->input('designation_id'));
        }

        if ($request->filled('grade') && $request->input('grade') !== 'all') {
            $level = (string) $request->input('grade');
            if (preg_match('/^L([0-9]+)$/', $level, $matches)) {
                $query->whereHas('designation', fn ($designation) => $designation->where('level', (int) $matches[1]));
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $structures = $query->orderBy('id', 'desc')->paginate(15);
        $designations = app(\App\Services\PayrollDesignations::class)->available();
        $grades = $designations->pluck('level')->unique()->sort()->map(fn ($level) => 'L'.$level)->values();

        return view('admin.payroll.salary-structures', compact('structures', 'designations', 'grades'));
    }

    public function storeSalaryStructure(Request $request)
    {
        $this->authorizePayroll('payroll', 'create');

        $data = $request->validate([
            'designation_id' => ['required', 'exists:designations,id'],
            'grade' => ['required', 'string', 'max:50'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'hra_type' => ['required', 'in:percentage,fixed'],
            'hra_value' => ['required', 'numeric', 'min:0'],
            'special_allowance_type' => ['required', 'in:percentage,fixed'],
            'special_allowance_value' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $designation = app(\App\Services\PayrollDesignations::class)->resolve((int) $data['designation_id'], $data['grade']);
        $data['grade'] = 'L'.$designation->level;
        $name = $designation->name . ' - ' . $data['grade'];

        $payload = array_merge($data, [
            'name' => $name,
            'code' => Str::slug($name) . '-' . Str::upper(Str::random(4)),
            'created_by' => auth()->id(),
            'version' => 1,
            'is_active' => ($data['status'] === 'active'),
        ]);

        if (Schema::hasColumn('salary_structures', 'company_id')) {
            $payload['company_id'] = auth()->user()?->company_id;
        }

        $structure = SalaryStructure::create($payload);

        $this->audit('created_salary_structure', $structure, null, $structure->toArray(), $request);

        return back()->with('success', 'Salary Structure created successfully.');
    }

    public function updateSalaryStructure(Request $request, SalaryStructure $structure)
    {
        $this->authorizePayroll('payroll', 'edit');

        $data = $request->validate([
            'designation_id' => ['required', 'exists:designations,id'],
            'grade' => ['required', 'string', 'max:50'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'hra_type' => ['required', 'in:percentage,fixed'],
            'hra_value' => ['required', 'numeric', 'min:0'],
            'special_allowance_type' => ['required', 'in:percentage,fixed'],
            'special_allowance_value' => ['required', 'numeric', 'min:0'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'in:active,inactive'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $designation = app(\App\Services\PayrollDesignations::class)->resolve((int) $data['designation_id'], $data['grade']);
        $data['grade'] = 'L'.$designation->level;
        $name = $designation->name . ' - ' . $data['grade'];

        $old = $structure->toArray();
        $structure->update(array_merge($data, [
            'name' => $name,
            'is_active' => ($data['status'] === 'active'),
        ]));

        $this->audit('updated_salary_structure', $structure, $old, $structure->fresh()->toArray(), $request);

        return back()->with('success', 'Salary Structure updated successfully.');
    }

    public function destroySalaryStructure(Request $request, SalaryStructure $structure)
    {
        $this->authorizePayroll('payroll', 'delete');

        $old = $structure->toArray();
        $structure->delete();

        $this->audit('deleted_salary_structure', $structure, $old, null, $request);

        return back()->with('success', 'Salary Structure deleted successfully.');
    }

    /**
     * AJAX endpoint: Return structure defaults for designation or structure selection
     */
    public function structureDefaults(Request $request)
    {
        $this->authorizePayroll('payroll');

        if ($request->filled('structure_id')) {
            $structure = SalaryStructure::find($request->input('structure_id'));
            if ($structure) {
                return response()->json([
                    'success' => true,
                    'designation_id' => $structure->designation_id,
                    'grade' => $structure->grade,
                    'basic_salary' => (float) $structure->basic_salary,
                    'hra_type' => $structure->hra_type ?: 'percentage',
                    'hra_value' => (float) $structure->hra_value,
                    'special_allowance_type' => $structure->special_allowance_type ?: 'percentage',
                    'special_allowance_value' => (float) $structure->special_allowance_value,
                ]);
            }
        }

        if ($request->filled('designation_id')) {
            $structure = SalaryStructure::where('designation_id', $request->input('designation_id'))
                ->where('status', 'active')
                ->latest()
                ->first();

            if ($structure) {
                return response()->json([
                    'success' => true,
                    'structure_id' => $structure->id,
                    'grade' => $structure->grade,
                    'basic_salary' => (float) $structure->basic_salary,
                    'hra_type' => $structure->hra_type ?: 'percentage',
                    'hra_value' => (float) $structure->hra_value,
                    'special_allowance_type' => $structure->special_allowance_type ?: 'percentage',
                    'special_allowance_value' => (float) $structure->special_allowance_value,
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'basic_salary' => 0.0,
            'hra_type' => 'percentage',
            'hra_value' => 0.0,
            'special_allowance_type' => 'percentage',
            'special_allowance_value' => 0.0,
        ]);
    }
}

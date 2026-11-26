<?php

namespace App\Http\Controllers;

use App\Models\Designation;
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
        $data = $request->validate(['maximum_level' => ['required', 'integer', 'min:7', 'max:2147483647']]);
        $maximum = (int) $data['maximum_level'];
        if (Designation::where('level', '>=', $maximum)->exists()) {
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

        $hasLevel = Schema::hasColumn('designations', 'level');
        $hasArchivedAt = Schema::hasColumn('designations', 'archived_at');

        // Order by level and name to ensure proper display
        $query = Designation::with(['addedBy', 'updatedBy']);
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
        if ($hasLevel) {
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
        $designation->load(['addedBy', 'updatedBy', 'employeeDetails']);
        return view('admin.designations.show', compact('designation'));
    }

    public function create()
    {
        self::ensureDesignationColumnsExist();
        $nextCode = $this->generateNextCodePreview();
        return view('admin.designations.create', compact('nextCode'));
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
        ]);

        $request->validate([
            'name'                 => ['required', 'string', 'max:255', Rule::unique('designations', 'name')],
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

        // Strict level range validation within configured company limit
        if ($level < 0 || $level > \App\Services\DesignationLevels::maximum()) {
            $errorMsg = 'Designation level must be within the configured company limit.';
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['level' => [$errorMsg]]], 422);
            }
            return back()->withErrors(['level' => $errorMsg])->withInput();
        }

        try {
            $designationData = [
                'name'            => $request->name,
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

        return redirect()->route(\App\Support\WorkforceSettings::name('designations.index'), ['t' => time()])
            ->with('success', 'Designation added successfully.');
    }

    public function edit(Designation $designation)
    {
        self::ensureDesignationColumnsExist();
        return view('admin.designations.create', compact('designation'));
    }

    public function update(Request $request, Designation $designation)
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('designations', 'name')->ignore($designation->id)
            ],
            'level'     => ['required', 'integer', 'min:0', 'max:' . \App\Services\DesignationLevels::maximum()]
        ]);

        $level = (int) $request->level;

        // Strict level range validation within configured company limit
        if ($level < 0 || $level > \App\Services\DesignationLevels::maximum()) {
            $errorMsg = 'Designation level must be within the configured company limit.';
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $errorMsg, 'errors' => ['level' => [$errorMsg]]], 422);
            }
            return back()->withErrors(['level' => $errorMsg])->withInput();
        }

        try {
            $designation->update([
                'name'            => $request->name,
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

        return redirect()->route(\App\Support\WorkforceSettings::name('designations.index'), ['t' => time()])
            ->with('success', 'Designation updated successfully.')
            ->with('updated_id', $designation->id);
    }

    public function ajaxStore(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255', Rule::unique('designations', 'name')],
            'level'     => ['required', 'integer', 'min:0', 'max:' . \App\Services\DesignationLevels::maximum()]
        ]);

        $level = (int) $request->level;

        // Strict level range validation within configured company limit
        if ($level < 0 || $level > \App\Services\DesignationLevels::maximum()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Designation level must be within the configured company limit.'
            ], 422);
        }

        try {
            $designation = Designation::create([
                'name'            => $request->name,
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
            return redirect()->route(\App\Support\WorkforceSettings::name('designations.index'))
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

        return redirect()->route(\App\Support\WorkforceSettings::name('designations.index'))
            ->with('success', 'Designation archived successfully.');
    }

    public function archive(Request $request)
    {
        self::ensureDesignationColumnsExist();
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 30, 40, 50, 100], true) ? $perPage : 10;

        $hasUniqueCode = Schema::hasColumn('designations', 'unique_code');
        $hasArchivedAt = Schema::hasColumn('designations', 'archived_at');

        $query = Designation::with(['addedBy', 'updatedBy']);
        if ($hasArchivedAt) {
            $query->whereNotNull('archived_at')->orderByDesc('archived_at');
        } else {
            $query->orderByDesc('id');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $hasUniqueCode) {
                $q->where('name', 'like', '%' . $search . '%');
                if ($hasUniqueCode) {
                    $q->orWhere('unique_code', 'like', '%' . $search . '%');
                }
            });
        }

        $designations = $query->paginate($perPage)->withQueryString();

        return view('admin.designations.archive', compact('designations'));
    }

    public function deleteArchived($id)
    {
        abort_unless(auth()->user()?->hasModulePermission('designations', 'delete'), 403);
        $connection = (new Designation())->getConnection();
        try {
            return $connection->transaction(function () use ($id) {
                $designation = Designation::whereNotNull('archived_at')->lockForUpdate()->findOrFail($id);
                if ($designation->employeeDetails()->exists()
                    || Designation::where('parent_id', $designation->id)->exists()) {
                    return back()->with('error', 'This designation cannot be deleted while employees or subordinate designations are linked to it.');
                }
                $designation->delete();
                return redirect()->route(\App\Support\WorkforceSettings::name('designations.archive'))->with('success', 'Archived designation permanently deleted.');
            });
        } catch (QueryException $e) {
            if (($e->errorInfo[0] ?? null) !== '23000') {
                throw $e;
            }
            return back()->with('error', 'This designation cannot be deleted because other records reference it.');
        }
    }

    public function restore($id)
    {
        $designation = Designation::whereNotNull('archived_at')->findOrFail($id);

        $designation->forceFill([
            'archived_at' => null,
            'last_updated_by' => Auth::id(),
        ])->save();

        return redirect()->route(\App\Support\WorkforceSettings::name('designations.archive'))
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

                if ($designation->employeeDetails()->exists()) {
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

        return redirect()->route(\App\Support\WorkforceSettings::name('designations.index'))
            ->with('success', $designations->count() . ' designation(s) archived successfully.');
    }
}

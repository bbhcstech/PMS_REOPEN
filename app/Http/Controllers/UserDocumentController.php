<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\EmployeeDocument;
use App\Models\HrDocument;
use App\Models\ManagerDocument;
use App\Models\AdminDocument;
use App\Models\DocumentView;
use App\Models\User;
use App\Services\SystemNotificationService;
use Illuminate\Support\Facades\Auth;

class UserDocumentController extends Controller
{
    public static function getStructuredDocumentTypes(): array
    {
        $json = AppSetting::valueFor('doc_types_structured');
        if ($json) {
            $types = json_decode($json, true);
            if (is_array($types) && count($types) > 0) {
                return $types;
            }
        }

        // Fallback default types
        $rawTypesStr = AppSetting::valueFor('doc_types', 'National ID (NID), Passport, Academic Certificate, Tax Certificate, Driving License, Experience Letter');
        $rawTypes = array_map('trim', explode(',', $rawTypesStr));

        $types = [];
        $requireNid = AppSetting::valueFor('doc_require_nid', '1') === '1';
        $requireTax = AppSetting::valueFor('doc_require_tax_id', '0') === '1';

        foreach ($rawTypes as $name) {
            if (empty($name)) continue;
            $isRequired = false;
            if (str_contains(strtolower($name), 'nid') || str_contains(strtolower($name), 'national id')) {
                $isRequired = $requireNid;
            } elseif (str_contains(strtolower($name), 'tax')) {
                $isRequired = $requireTax;
            } elseif (str_contains(strtolower($name), 'academic') || str_contains(strtolower($name), 'certificate')) {
                $isRequired = true;
            }

            $types[] = [
                'name' => $name,
                'required' => $isRequired,
                'description' => 'Upload clear digital copy or PDF',
            ];
        }

        return $types;
    }

    public static function getModelAndTableForType(string $type): array
    {
        $normalized = strtolower(trim($type));
        return match ($normalized) {
            'hr', 'hr_documents' => [HrDocument::class, 'hr_documents'],
            'manager', 'manager_documents' => [ManagerDocument::class, 'manager_documents'],
            'admin', 'admin_documents', 'superadmin', 'super_admin', 'super-admin' => [AdminDocument::class, 'admin_documents'],
            default => [EmployeeDocument::class, 'employee_documents'],
        };
    }

    public static function getModelForRole(string $role): string
    {
        return match (strtolower(trim($role))) {
            'hr' => HrDocument::class,
            'manager' => ManagerDocument::class,
            'admin', 'superadmin', 'super_admin', 'super-admin' => AdminDocument::class,
            default => EmployeeDocument::class,
        };
    }

    public static function getTableForRole(string $role): string
    {
        return match (strtolower(trim($role))) {
            'hr' => 'hr_documents',
            'manager' => 'manager_documents',
            'admin', 'superadmin', 'super_admin', 'super-admin' => 'admin_documents',
            default => 'employee_documents',
        };
    }

    public function index()
    {
        $user = Auth::user();
        $userRole = strtolower($user->role ?? 'employee');

        $docTypes = static::getStructuredDocumentTypes();
        $maxSizeMb = (int) AppSetting::valueFor('doc_max_file_size', '10');
        $allowedExtensions = AppSetting::valueFor('doc_allowed_extensions', 'pdf,png,jpg,jpeg,doc,docx');

        // My documents from role-specific table
        $myModel = static::getModelForRole($userRole);
        $myDocs = $myModel::where('user_id', $user->id)
            ->with('views.viewer')
            ->orderBy('created_at', 'desc')
            ->get();

        // For HR, Manager, and Admin: Load Role Storage Repository documents for Tab 2
        $repositoryDocs = collect();
        if (in_array($userRole, ['admin', 'superadmin', 'super_admin', 'super-admin', 'hr', 'manager'])) {
            $empDocs = EmployeeDocument::with(['user', 'views.viewer'])->get()->each(fn($d) => $d->table_type = 'employee');
            $hrDocs = HrDocument::with(['user', 'views.viewer'])->get()->each(fn($d) => $d->table_type = 'hr');
            $mgrDocs = ManagerDocument::with(['user', 'views.viewer'])->get()->each(fn($d) => $d->table_type = 'manager');
            $admDocs = collect();

            if (in_array($userRole, ['admin', 'superadmin', 'super_admin', 'super-admin'])) {
                $admDocs = AdminDocument::with(['user', 'views.viewer'])->get()->each(fn($d) => $d->table_type = 'admin');
            }

            $allDocs = collect()
                ->concat($empDocs)
                ->concat($hrDocs)
                ->concat($mgrDocs)
                ->concat($admDocs)
                ->sortByDesc('created_at');

            // Apply search & role filters if present
            if (request()->filled('role_type')) {
                $rFilter = strtolower(trim(request('role_type')));
                $allDocs = $allDocs->filter(fn($d) => $d->table_type === $rFilter);
            }

            if (request()->filled('search')) {
                $search = strtolower(trim(request('search')));
                $allDocs = $allDocs->filter(function ($d) use ($search) {
                    $uName = strtolower($d->user->name ?? '');
                    $uEmail = strtolower($d->user->email ?? '');
                    $docType = strtolower($d->document_type ?? '');
                    $fName = strtolower($d->file_name ?? '');

                    return str_contains($uName, $search) ||
                           str_contains($uEmail, $search) ||
                           str_contains($docType, $search) ||
                           str_contains($fName, $search);
                });
            }

            $repositoryDocs = $allDocs->values();
        }

        return view('user-documents.index', compact(
            'docTypes',
            'myDocs',
            'repositoryDocs',
            'userRole',
            'maxSizeMb',
            'allowedExtensions'
        ));
    }

    public function upload(Request $request)
    {
        $maxSizeMb = (int) AppSetting::valueFor('doc_max_file_size', '10');
        $maxSizeKb = $maxSizeMb * 1024;
        $allowedExtensions = AppSetting::valueFor('doc_allowed_extensions', 'pdf,png,jpg,jpeg,doc,docx');
        $allowedExtArray = array_map('trim', explode(',', strtolower($allowedExtensions)));

        $request->validate([
            'document_type' => 'required|string|max:255',
            'document_file' => "required|file|max:{$maxSizeKb}",
        ]);

        $file = $request->file('document_file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        if (!in_array($extension, $allowedExtArray)) {
            return back()->with('error', "Invalid file format. Allowed formats: {$allowedExtensions}");
        }

        $user = Auth::user();
        $userRole = strtolower($user->role ?? 'employee');
        $docType = trim($request->document_type);

        $folderName = match ($userRole) {
            'hr' => 'hr-documents',
            'manager' => 'manager-documents',
            'admin', 'superadmin', 'super_admin', 'super-admin' => 'admin-documents',
            default => 'employee-documents',
        };

        $uploadDir = public_path("uploads/{$folderName}/" . $user->id);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = \Illuminate\Support\Str::slug($docType) . '-' . time() . '.' . $extension;
        $file->move($uploadDir, $safeName);
        $relativePath = "uploads/{$folderName}/" . $user->id . '/' . $safeName;

        $modelClass = static::getModelForRole($userRole);

        $doc = $modelClass::updateOrCreate(
            [
                'user_id' => $user->id,
                'document_type' => $docType,
            ],
            [
                'file_path' => $relativePath,
                'file_name' => $originalName,
                'file_size' => $fileSize,
                'file_type' => $extension,
                'uploaded_at' => now(),
            ]
        );

        // Send Notifications based on role rules
        try {
            $uploaderUrl = match ($userRole) {
                'admin', 'superadmin', 'super_admin', 'super-admin' => route('my-documents.index'),
                default => route('my-documents.index', ['tab' => 'my-docs']),
            };

            // Notify the uploader
            SystemNotificationService::notifyUser(
                $user,
                "Document Uploaded: {$docType}",
                "Your {$docType} ({$originalName}) has been successfully uploaded.",
                $uploaderUrl,
                ['document_type' => $docType]
            );

            // If an Employee uploaded, notify HR and Managers
            if ($userRole === 'employee') {
                $hrAndManagers = User::whereIn('role', ['hr', 'manager'])
                    ->where('id', '!=', $user->id)
                    ->where(function ($q) {
                        $q->where('is_active', true)->orWhereNull('is_active');
                    })
                    ->get();

                if ($hrAndManagers->count() > 0) {
                    SystemNotificationService::notifyUser(
                        $hrAndManagers,
                        "Employee Document Uploaded: {$user->name}",
                        "{$user->name} has uploaded a document: {$docType}.",
                        route('my-documents.index', ['tab' => 'employee']),
                        [
                            'user_id' => $user->id,
                            'document_type' => $docType,
                            'file_path' => $relativePath
                        ]
                    );
                }
            }

            // If HR uploaded, notify Managers
            if ($userRole === 'hr') {
                $managers = User::where('role', 'manager')
                    ->where('id', '!=', $user->id)
                    ->where(function ($q) {
                        $q->where('is_active', true)->orWhereNull('is_active');
                    })
                    ->get();

                if ($managers->count() > 0) {
                    SystemNotificationService::notifyUser(
                        $managers,
                        "HR Document Uploaded: {$user->name}",
                        "{$user->name} (HR) has uploaded a document: {$docType}.",
                        route('my-documents.index', ['tab' => 'employee', 'role_type' => 'hr']),
                        [
                            'user_id' => $user->id,
                            'document_type' => $docType,
                            'file_path' => $relativePath
                        ]
                    );
                }
            }

            // Notify Admins
            $admins = User::whereIn('role', ['admin', 'superadmin', 'super_admin'])
                ->where('id', '!=', $user->id)
                ->where(function ($q) {
                    $q->where('is_active', true)->orWhereNull('is_active');
                })
                ->get();

            if ($admins->count() > 0) {
                SystemNotificationService::notifyUser(
                    $admins,
                    "Document Uploaded: {$user->name}",
                    "{$user->name} ({$user->role}) has uploaded {$docType}.",
                    route('my-documents.index', ['tab' => 'employee']),
                    [
                        'user_id' => $user->id,
                        'document_type' => $docType,
                        'file_path' => $relativePath
                    ]
                );
            }
        } catch (\Throwable $e) {
            \Log::error('Document upload notification error: ' . $e->getMessage());
        }

        return back()->with('success', "{$docType} uploaded successfully!");
    }

    public function destroy(Request $request, $type, $id)
    {
        $user = Auth::user();
        [$modelClass] = static::getModelAndTableForType($type);

        $doc = $modelClass::where('user_id', $user->id)->where('id', $id)->first();
        if (!$doc) {
            $doc = EmployeeDocument::where('user_id', $user->id)->where('id', $id)->first()
                ?? HrDocument::where('user_id', $user->id)->where('id', $id)->first()
                ?? ManagerDocument::where('user_id', $user->id)->where('id', $id)->first()
                ?? AdminDocument::where('user_id', $user->id)->where('id', $id)->first();
        }

        if (!$doc) {
            return back()->with('error', 'Document not found or unauthorized deletion.');
        }

        $fullPath = public_path($doc->file_path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        $doc->delete();

        return back()->with('success', 'Document removed successfully.');
    }

    protected function resolveAccessibleDocument($type, $id): array
    {
        $user = Auth::user();
        $userRole = strtolower($user->role ?? 'employee');

        [$modelClass, $tableName] = static::getModelAndTableForType($type);
        $doc = $modelClass::find($id);

        if (!$doc) {
            // Search across all document tables as fallback if type mismatch occurs
            $doc = EmployeeDocument::find($id)
                ?? HrDocument::find($id)
                ?? ManagerDocument::find($id)
                ?? AdminDocument::find($id);

            if ($doc) {
                if ($doc instanceof HrDocument) $tableName = 'hr_documents';
                elseif ($doc instanceof ManagerDocument) $tableName = 'manager_documents';
                elseif ($doc instanceof AdminDocument) $tableName = 'admin_documents';
                else $tableName = 'employee_documents';
            }
        }

        if (!$doc) {
            return [null, $tableName];
        }

        // Authorization check:
        $isAuthorized = false;

        // 1. Owner can always download their own document
        if ((int) $doc->user_id === (int) $user->id) {
            $isAuthorized = true;
        }
        // 2. Admins and Superadmins can download any document
        elseif (in_array($userRole, ['admin', 'superadmin', 'super_admin', 'super-admin', 'administrator', 'company_admin'])) {
            $isAuthorized = true;
        }
        // 3. HR and Managers can download employee, hr, and manager documents
        elseif (in_array($userRole, ['hr', 'manager'])) {
            if (in_array($tableName, ['employee_documents', 'hr_documents', 'manager_documents'])) {
                $isAuthorized = true;
            }
        }
        // 4. Default fallback: allow logged-in authenticated users access to employee documents
        elseif ($tableName === 'employee_documents') {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            abort(403, 'Unauthorized document access.');
        }

        // Cross-company isolation check: ensure the document owner belongs to the same tenant company
        $targetUser = User::find($doc->user_id);
        $myCompanyId = app(\App\Services\CompanyContext::class)->id() ?? $user->company_id;
        if ($targetUser && $myCompanyId && $targetUser->company_id && $targetUser->company_id != $myCompanyId) {
            abort(403, 'Cross-company document access forbidden.');
        }

        return [$doc, $tableName];
    }

    public function history($type, $id)
    {
        [$doc] = $this->resolveAccessibleDocument($type, $id);
        abort_unless($doc, 404, 'Document record not found.');

        return response()->json($doc->views()->with('viewer')->get())
            ->header('Cache-Control', 'no-store, private');
    }

    public function download(Request $request, $type, $id)
    {
        [$doc, $tableName] = $this->resolveAccessibleDocument($type, $id);
        if (!$doc) {
            return back()->with('error', 'Document record not found.');
        }
        $user = Auth::user();

        $fullPath = public_path($doc->file_path);
        if (!file_exists($fullPath)) {
            $altPath = storage_path('app/public/' . ltrim($doc->file_path, '/'));
            if (file_exists($altPath)) {
                $fullPath = $altPath;
            } else {
                return back()->with('error', 'Document file not found on server.');
            }
        }

        // Record view audit event
        try {
            DocumentView::create([
                'document_table' => $tableName,
                'document_id' => $doc->id,
                'viewed_by_user_id' => $user->id,
                'viewed_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to log document view: ' . $e->getMessage());
        }

        return response()->download($fullPath, $doc->file_name);
    }
}

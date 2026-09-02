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
            'admin', 'admin_documents' => [AdminDocument::class, 'admin_documents'],
            default => [EmployeeDocument::class, 'employee_documents'],
        };
    }

    public static function getModelForRole(string $role): string
    {
        return match (strtolower(trim($role))) {
            'hr' => HrDocument::class,
            'manager' => ManagerDocument::class,
            'admin' => AdminDocument::class,
            default => EmployeeDocument::class,
        };
    }

    public static function getTableForRole(string $role): string
    {
        return match (strtolower(trim($role))) {
            'hr' => 'hr_documents',
            'manager' => 'manager_documents',
            'admin' => 'admin_documents',
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
        if (in_array($userRole, ['admin', 'hr', 'manager'])) {
            $empDocs = EmployeeDocument::with(['user', 'views.viewer'])->get()->each(fn($d) => $d->table_type = 'employee');
            $hrDocs = HrDocument::with(['user', 'views.viewer'])->get()->each(fn($d) => $d->table_type = 'hr');
            $mgrDocs = ManagerDocument::with(['user', 'views.viewer'])->get()->each(fn($d) => $d->table_type = 'manager');
            $admDocs = collect();

            if ($userRole === 'admin') {
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
            'admin' => 'admin-documents',
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

        // Send Notifications based on role rules:
        // 1. Uploader notification:
        //    - Admin: redirects to my-documents.index
        //    - HR, Manager, Employee: redirects to my-documents.index?tab=my-docs (yellow portion)
        // 2. Employee upload:
        //    - HR & Managers receive notification redirecting to my-documents.index?tab=employee (red portion)
        // 3. Document uploaded by HR, Manager, Employee or another Admin:
        //    - Admins receive notification redirecting to admin.settings.document (document settings page)
        try {
            $uploaderUrl = match ($userRole) {
                'admin' => route('my-documents.index'),
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

            // If an Employee uploaded, notify HR and Managers (redirecting to Role Storage Repository)
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

            // If HR uploaded, notify Managers (redirecting to HR uploaded document section under My Documents)
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

            // Notify Admins (redirecting to Role Storage Databases Repository section on My Documents page)
            $admins = User::where('role', 'admin')
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

        $doc = $modelClass::where('user_id', $user->id)->where('id', $id)->firstOrFail();

        $fullPath = public_path($doc->file_path);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        $doc->delete();

        return back()->with('success', 'Document removed successfully.');
    }

    public function download(Request $request, $type, $id)
    {
        $user = Auth::user();
        $userRole = strtolower($user->role ?? 'employee');

        [$modelClass, $tableName] = static::getModelAndTableForType($type);
        $doc = $modelClass::findOrFail($id);

        // Download Permission Matrix:
        // Admin: can download hr, manager, employee, admin documents
        // HR: can download employee, hr, and manager documents
        // Manager: can download employee, hr, and manager documents
        // Employee: can download ONLY own employee documents
        $isAuthorized = false;

        if ($userRole === 'admin') {
            $isAuthorized = true;
        } elseif (in_array($userRole, ['hr', 'manager'])) {
            if (in_array($tableName, ['employee_documents', 'hr_documents', 'manager_documents'])) {
                $isAuthorized = true;
            }
        } elseif ($userRole === 'employee') {
            if ($tableName === 'employee_documents' && $doc->user_id === $user->id) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            abort(403, 'Unauthorized document access.');
        }

        $fullPath = public_path($doc->file_path);
        if (!file_exists($fullPath)) {
            return back()->with('error', 'Document file not found on server.');
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

<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppSetting;
use App\Models\EmployeeDocument;
use App\Models\HrDocument;
use App\Models\ManagerDocument;
use App\Models\AdminDocument;
use App\Models\User;
use App\Http\Controllers\UserDocumentController;

class DocumentSettingsController extends Controller
{
    public function index(Request $request)
    {
        $settings = [
            'document_types' => AppSetting::valueFor('doc_types', 'National ID (NID), Passport, Academic Certificate, Tax Certificate, Driving License, Experience Letter'),
            'max_file_size_mb' => AppSetting::valueFor('doc_max_file_size', '10'),
            'allowed_extensions' => AppSetting::valueFor('doc_allowed_extensions', 'pdf,png,jpg,jpeg,doc,docx'),
            'require_nid' => AppSetting::valueFor('doc_require_nid', '1'),
            'require_tax_id' => AppSetting::valueFor('doc_require_tax_id', '0'),
        ];

        return view('admin.settings.document', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'max_file_size_mb' => 'required|numeric|min:1|max:100',
            'allowed_extensions' => 'required|string',
        ]);

        AppSetting::updateOrCreate(
            ['key' => 'doc_max_file_size'],
            ['label' => 'Max File Size MB', 'value' => $request->max_file_size_mb, 'page' => 'document-settings', 'section' => 'Document', 'type' => 'number']
        );

        AppSetting::updateOrCreate(
            ['key' => 'doc_allowed_extensions'],
            ['label' => 'Allowed Extensions', 'value' => strtolower(trim($request->allowed_extensions)), 'page' => 'document-settings', 'section' => 'Document', 'type' => 'text']
        );

        return back()->with('success', 'Document system file rules updated successfully!');
    }

    public function addDocumentType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $types = UserDocumentController::getStructuredDocumentTypes();
        $name = trim($request->name);
        $isRequired = $request->has('required') ? true : false;

        foreach ($types as $t) {
            if (strtolower($t['name']) === strtolower($name)) {
                return back()->with('error', "Document type '{$name}' already exists!");
            }
        }

        $types[] = [
            'name' => $name,
            'required' => $isRequired,
            'description' => $request->input('description', 'Upload clear file copy'),
        ];

        AppSetting::updateOrCreate(
            ['key' => 'doc_types_structured'],
            ['label' => 'Structured Document Types', 'value' => json_encode(array_values($types)), 'page' => 'document-settings', 'section' => 'Document', 'type' => 'json']
        );

        $names = array_column($types, 'name');
        AppSetting::updateOrCreate(
            ['key' => 'doc_types'],
            ['label' => 'Document Types List', 'value' => implode(', ', $names), 'page' => 'document-settings', 'section' => 'Document', 'type' => 'text']
        );

        return back()->with('success', "New Document Type '{$name}' added successfully!");
    }

    public function deleteDocumentType(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $targetName = trim($request->name);
        $types = UserDocumentController::getStructuredDocumentTypes();

        $filtered = array_values(array_filter($types, function ($t) use ($targetName) {
            return strtolower($t['name']) !== strtolower($targetName);
        }));

        AppSetting::updateOrCreate(
            ['key' => 'doc_types_structured'],
            ['label' => 'Structured Document Types', 'value' => json_encode($filtered), 'page' => 'document-settings', 'section' => 'Document', 'type' => 'json']
        );

        $names = array_column($filtered, 'name');
        AppSetting::updateOrCreate(
            ['key' => 'doc_types'],
            ['label' => 'Document Types List', 'value' => implode(', ', $names), 'page' => 'document-settings', 'section' => 'Document', 'type' => 'text']
        );

        return back()->with('success', "Document Type '{$targetName}' removed.");
    }
}

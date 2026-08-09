<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LetterheadController extends Controller
{
    public function index(): View
    {
        $companies = Company::orderBy('name')->get();

        $stats = [
            'total_companies' => $companies->count(),
            'uploaded_count' => $companies->filter(fn($c) => $c->hasLetterhead())->count(),
            'pending_count' => $companies->filter(fn($c) => !$c->hasLetterhead())->count(),
        ];

        return view('admin.letterhead.index', compact('companies', 'stats'));
    }

    public function upload(Request $request, Company $company): RedirectResponse
    {
        $request->validate([
            'letterhead_file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ], [
            'letterhead_file.required' => 'Please select or drag & drop a PDF or DOCS file.',
            'letterhead_file.mimes' => 'Only PDF (.pdf) and Word Documents (.doc, .docx) are allowed.',
            'letterhead_file.max' => 'The file size must not exceed 10MB.',
        ]);

        if ($request->hasFile('letterhead_file')) {
            $file = $request->file('letterhead_file');
            $extension = strtolower($file->getClientOriginalExtension());
            $originalName = $file->getClientOriginalName();
            $fileType = in_array($extension, ['doc', 'docx']) ? 'docs' : 'pdf';

            // Destination directory inside public/uploads/letterheads
            $destinationPath = public_path('uploads/letterheads');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            // Remove old letterhead file if present
            if ($company->letterhead_file && File::exists(public_path($company->letterhead_file))) {
                File::delete(public_path($company->letterhead_file));
            }

            $fileName = 'letterhead_company_' . $company->id . '_' . time() . '.' . $extension;
            $file->move($destinationPath, $fileName);

            $relativePath = 'uploads/letterheads/' . $fileName;

            $company->update([
                'letterhead_file' => $relativePath,
                'letterhead_original_name' => $originalName,
                'letterhead_file_type' => $fileType,
                'letterhead_uploaded_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Official Letterhead for "' . $company->name . '" uploaded and fixed successfully.');
        }

        return redirect()->back()->with('error', 'Failed to upload letterhead file.');
    }

    public function download(Company $company): BinaryFileResponse|RedirectResponse
    {
        if (!$company->hasLetterhead() || !File::exists(public_path($company->letterhead_file))) {
            return redirect()->back()->with('error', 'No letterhead file found for ' . $company->name);
        }

        $filePath = public_path($company->letterhead_file);
        $displayName = $company->letterhead_original_name ?: ($company->company_code . '_Letterhead.' . pathinfo($filePath, PATHINFO_EXTENSION));

        return response()->download($filePath, $displayName);
    }

    public function destroy(Company $company): RedirectResponse
    {
        if ($company->letterhead_file && File::exists(public_path($company->letterhead_file))) {
            File::delete(public_path($company->letterhead_file));
        }

        $company->update([
            'letterhead_file' => null,
            'letterhead_original_name' => null,
            'letterhead_file_type' => null,
            'letterhead_uploaded_at' => null,
        ]);

        return redirect()->back()->with('success', 'Letterhead template removed for ' . $company->name);
    }
}

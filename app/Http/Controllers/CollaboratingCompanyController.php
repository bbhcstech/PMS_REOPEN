<?php

namespace App\Http\Controllers;

use App\Models\CollaboratingCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CollaboratingCompanyController extends Controller
{
    public function index(Request $request)
    {
        $isAdmin = $this->isAdmin();
        $query = CollaboratingCompany::query();

        if (! $isAdmin) {
            $query->active();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('industry', 'like', '%' . $search . '%')
                    ->orWhere('collaboration_type', 'like', '%' . $search . '%')
                    ->orWhere('services', 'like', '%' . $search . '%');
            });
        }

        if ($isAdmin && $request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $companies = $query->latest()->paginate(12)->withQueryString();
        $stats = [
            'total' => CollaboratingCompany::count(),
            'active' => CollaboratingCompany::active()->count(),
            'inactive' => CollaboratingCompany::where('status', 'inactive')->count(),
        ];

        return view('admin.collaborating-companies.index', compact('companies', 'stats', 'isAdmin'));
    }

    public function create()
    {
        $this->ensureAdmin();

        return view('admin.collaborating-companies.form', [
            'company' => new CollaboratingCompany(),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $this->validatedData($request);
        $data['image_path'] = $this->storeCompanyImage($request);

        CollaboratingCompany::create($data);

        return redirect()->route('collaborating-companies.index')
            ->with('success', 'Collaborating company added successfully.');
    }

    public function show(CollaboratingCompany $collaboratingCompany)
    {
        if (! $this->isAdmin() && $collaboratingCompany->status !== 'active') {
            abort(404);
        }

        return view('admin.collaborating-companies.show', [
            'company' => $collaboratingCompany,
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    public function edit(CollaboratingCompany $collaboratingCompany)
    {
        $this->ensureAdmin();

        return view('admin.collaborating-companies.form', [
            'company' => $collaboratingCompany,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, CollaboratingCompany $collaboratingCompany)
    {
        $this->ensureAdmin();
        $data = $this->validatedData($request);
        $imagePath = $this->storeCompanyImage($request, $collaboratingCompany->image_path);

        if ($imagePath) {
            $data['image_path'] = $imagePath;
        }

        $collaboratingCompany->update($data);

        return redirect()->route('collaborating-companies.index')
            ->with('success', 'Collaborating company updated successfully.');
    }

    public function destroy(CollaboratingCompany $collaboratingCompany)
    {
        $this->ensureAdmin();
        $this->deleteCompanyImage($collaboratingCompany->image_path);
        $collaboratingCompany->delete();

        return redirect()->route('collaborating-companies.index')
            ->with('success', 'Collaborating company removed successfully.');
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'industry' => ['nullable', 'string', 'max:255'],
            'collaboration_type' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'services' => ['nullable', 'string', 'max:3000'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'website' => ['nullable', 'url', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
            'started_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'social_links.linkedin' => ['nullable', 'url', 'max:255'],
            'social_links.facebook' => ['nullable', 'url', 'max:255'],
            'social_links.instagram' => ['nullable', 'url', 'max:255'],
            'social_links.x' => ['nullable', 'url', 'max:255'],
            'social_links.youtube' => ['nullable', 'url', 'max:255'],
        ]);

        unset($data['company_image']);

        $socialLinks = array_filter($data['social_links'] ?? [], fn ($value) => filled($value));
        $data['social_links'] = $socialLinks ?: null;

        return $data;
    }

    private function storeCompanyImage(Request $request, ?string $oldPath = null): ?string
    {
        if (! $request->hasFile('company_image')) {
            return null;
        }

        $file = $request->file('company_image');
        $directory = public_path('uploads/collaborating-companies');

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $filename = uniqid('company_', true) . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        $this->deleteCompanyImage($oldPath);

        return 'uploads/collaborating-companies/' . $filename;
    }

    private function deleteCompanyImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        $fullPath = public_path($path);

        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }

    private function isAdmin(): bool
    {
        return in_array(strtolower((string) auth()->user()?->role), ['admin', 'hr'], true);
    }

    private function ensureAdmin(): void
    {
        abort_unless($this->isAdmin(), 403);
    }
}

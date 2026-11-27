<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LetterheadSignature;
use App\Services\LetterheadSignatureService;
use App\Services\TenantScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LetterheadSignatureController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        LetterheadSignature::ensureTable();
        $user = auth()->user();
        $companyId = TenantScope::companyId() ?: ($user?->company_id ? (int) $user->company_id : null);

        $signatures = LetterheadSignature::query()
            ->visibleTo($user, $companyId)
            ->orderByDesc('is_default')
            ->orderByDesc('is_active')
            ->latest('id')
            ->get()
            ->map(fn (LetterheadSignature $sig) => $this->serialize($sig));

        return response()->json([
            'success' => true,
            'signatures' => $signatures,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        LetterheadSignature::ensureTable();
        $user = auth()->user();
        $companyId = TenantScope::companyId() ?: ($user?->company_id ? (int) $user->company_id : null);

        $validated = $request->validate([
            'signatory_name' => 'required|string|max:160',
            'signatory_title' => 'nullable|string|max:160',
            'method' => 'required|in:draw,upload',
            'signature_data' => 'required_if:method,draw|nullable|string',
            'signature_file' => 'required_if:method,upload|nullable|file|mimes:png,jpg,jpeg|max:' . LetterheadSignatureService::maxUploadKb(),
            'is_default' => 'nullable|boolean',
        ]);

        $stored = $validated['method'] === 'draw'
            ? LetterheadSignatureService::storeFromDataUrl((string) $validated['signature_data'], $companyId)
            : LetterheadSignatureService::storeFromUploadedFile($request->file('signature_file'), $companyId);

        $setAsDefault = $request->boolean('is_default');
        if ($setAsDefault) {
            LetterheadSignature::where('company_id', $companyId)
                ->where('user_id', $user->id)
                ->update(['is_default' => false]);
        }

        $signature = LetterheadSignature::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'signatory_name' => $validated['signatory_name'],
            'signatory_title' => $validated['signatory_title'] ?? null,
            'image_path' => $stored['path'],
            'mime_type' => $stored['mime_type'],
            'width_px' => $stored['width_px'],
            'height_px' => $stored['height_px'],
            'method' => $validated['method'],
            'is_default' => $setAsDefault,
            'is_active' => true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        LetterheadSignatureService::recordAudit('letterhead_signature_created', [
            'company_id' => $companyId,
            'signature_id' => $signature->id,
            'signatory_name' => $signature->signatory_name,
            'signatory_title' => $signature->signatory_title,
            'method' => $signature->method,
            'is_default' => $signature->is_default,
        ], $signature->id);

        return response()->json([
            'success' => true,
            'message' => 'Authorized signature saved.',
            'signature' => $this->serialize($signature),
        ], 201);
    }

    public function update(Request $request, int $signatureId): JsonResponse
    {
        LetterheadSignature::ensureTable();
        $signature = LetterheadSignature::findOrFail($signatureId);
        $user = auth()->user();
        LetterheadSignatureService::authorizeSignature($user, $signature, true);

        $validated = $request->validate([
            'signatory_name' => 'required|string|max:160',
            'signatory_title' => 'nullable|string|max:160',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
        ]);

        $setDefault = $request->has('is_default') ? $request->boolean('is_default') : $signature->is_default;
        if ($setDefault) {
            LetterheadSignature::where('company_id', $signature->company_id)
                ->where('user_id', $signature->user_id)
                ->where('id', '!=', $signature->id)
                ->update(['is_default' => false]);
        }

        $signature->update([
            'signatory_name' => $validated['signatory_name'],
            'signatory_title' => $validated['signatory_title'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $signature->is_active,
            'is_default' => $setDefault,
            'updated_by' => $user->id,
        ]);

        LetterheadSignatureService::recordAudit('letterhead_signature_updated', [
            'company_id' => $signature->company_id,
            'signature_id' => $signature->id,
            'signatory_name' => $signature->signatory_name,
            'signatory_title' => $signature->signatory_title,
            'is_active' => $signature->is_active,
            'is_default' => $signature->is_default,
        ], $signature->id);

        return response()->json([
            'success' => true,
            'message' => 'Signature details updated.',
            'signature' => $this->serialize($signature->fresh()),
        ]);
    }

    public function setDefault(int $signatureId): JsonResponse
    {
        LetterheadSignature::ensureTable();
        $signature = LetterheadSignature::findOrFail($signatureId);
        $user = auth()->user();
        LetterheadSignatureService::authorizeSignature($user, $signature, true);

        LetterheadSignature::where('company_id', $signature->company_id)
            ->where('user_id', $signature->user_id)
            ->update(['is_default' => false]);

        $signature->update([
            'is_default' => true,
            'is_active' => true,
            'updated_by' => $user->id,
        ]);

        LetterheadSignatureService::recordAudit('letterhead_signature_default_set', [
            'company_id' => $signature->company_id,
            'signature_id' => $signature->id,
            'signatory_name' => $signature->signatory_name,
        ], $signature->id);

        return response()->json([
            'success' => true,
            'message' => 'Default signature updated.',
            'signature' => $this->serialize($signature->fresh()),
        ]);
    }

    public function toggleActive(int $signatureId): JsonResponse
    {
        LetterheadSignature::ensureTable();
        $signature = LetterheadSignature::findOrFail($signatureId);
        $user = auth()->user();
        LetterheadSignatureService::authorizeSignature($user, $signature, true);

        $nextActive = ! $signature->is_active;
        $signature->update([
            'is_active' => $nextActive,
            'is_default' => $nextActive ? $signature->is_default : false,
            'updated_by' => $user->id,
        ]);

        LetterheadSignatureService::recordAudit('letterhead_signature_toggled', [
            'company_id' => $signature->company_id,
            'signature_id' => $signature->id,
            'is_active' => $nextActive,
        ], $signature->id);

        return response()->json([
            'success' => true,
            'message' => $nextActive ? 'Signature activated.' : 'Signature deactivated.',
            'signature' => $this->serialize($signature->fresh()),
        ]);
    }

    public function destroy(int $signatureId): JsonResponse
    {
        LetterheadSignature::ensureTable();
        $signature = LetterheadSignature::findOrFail($signatureId);
        $user = auth()->user();
        LetterheadSignatureService::authorizeSignature($user, $signature, true);

        $companyId = $signature->company_id;
        $sigName = $signature->signatory_name;
        $imagePath = $signature->image_path;

        $signature->delete();
        LetterheadSignatureService::deletePrivateFile($imagePath);

        LetterheadSignatureService::recordAudit('letterhead_signature_deleted', [
            'company_id' => $companyId,
            'signature_id' => $signatureId,
            'signatory_name' => $sigName,
        ], $signatureId);

        return response()->json([
            'success' => true,
            'message' => 'Signature deleted.',
        ]);
    }

    public function image(int $signatureId): BinaryFileResponse
    {
        LetterheadSignature::ensureTable();
        $signature = LetterheadSignature::findOrFail($signatureId);
        LetterheadSignatureService::authorizeSignature(auth()->user(), $signature, false);

        $absolute = LetterheadSignatureService::absolutePath($signature->image_path);
        abort_unless($absolute && is_file($absolute), 404, 'Signature image not found.');

        return response()->file($absolute, [
            'Content-Type' => $signature->mime_type ?: 'image/png',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
        ]);
    }

    private function serialize(LetterheadSignature $sig): array
    {
        return [
            'id' => $sig->id,
            'user_id' => $sig->user_id,
            'company_id' => $sig->company_id,
            'signatory_name' => $sig->signatory_name,
            'signatory_title' => $sig->signatory_title,
            'method' => $sig->method,
            'width_px' => $sig->width_px,
            'height_px' => $sig->height_px,
            'is_default' => (bool) $sig->is_default,
            'is_active' => (bool) $sig->is_active,
            'image_url' => route('letterhead.signatures.image', $sig->id),
            'created_at' => optional($sig->created_at)->toIso8601String(),
        ];
    }
}

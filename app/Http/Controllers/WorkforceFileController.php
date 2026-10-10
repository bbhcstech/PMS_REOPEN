<?php

namespace App\Http\Controllers;

use App\Models\{Attendance, Leave};
use App\Services\WorkforceAccess;
use Illuminate\Support\Facades\Storage;

class WorkforceFileController extends Controller
{
    public function photo(Attendance $attendance)
    {
        $actor = WorkforceAccess::authorizeActor();
        \App\Services\TenantScope::authorizeCompany($attendance->user?->company_id);
        abort_unless(WorkforceAccess::isAdmin($actor) || (int) $attendance->user_id === (int) $actor->id, 403);
        return $this->file($attendance->getRawOriginal('clock_in_photo'), 'authority-attendance', (int) $actor->company_id);
    }

    public function attachment(Leave $leave)
    {
        $actor = WorkforceAccess::authorizeActor();
        \App\Services\TenantScope::authorizeCompany($leave->user?->company_id);
        abort_unless(WorkforceAccess::isAdmin($actor) || (int) $leave->user_id === (int) $actor->id, 403);
        return $this->file($leave->getRawOriginal('attachment'), 'authority-leave', (int) $actor->company_id);
    }

    private function file(?string $path, string $folder, int $companyId)
    {
        abort_unless($path && preg_match('#^' . $folder . '/' . $companyId . '/[a-zA-Z0-9_-]+\.[a-zA-Z0-9]+$#', $path), 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);
        return response()->file($disk->path($path), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'])->setPrivate();
    }
}

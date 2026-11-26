<?php

namespace App\Http\Controllers\Concerns\Dashboard;

use App\Models\StickyNote;
use Illuminate\Http\Request;

trait DashboardStickyNotes
{
 // StickyNoteController.php
public function notestore(Request $request)
{
    $request->validate([
        'note_text' => 'required|string|max:1000',
        'colour' => 'required|in:blue,yellow,red,gray,purple,green',
    ]);

    StickyNote::create([
        'company_id' => auth()->user()->company_id ?? null,
        'user_id' => auth()->id(),
        'note_text' => $request->note_text,
        'colour' => $request->colour,
    ]);

    return redirect()->back()->with('success', 'Note added successfully!');
}

public function stickyNoteComplete(StickyNote $stickyNote)
{
    abort_unless($this->canManageStickyNote($stickyNote), 403);

    $stickyNote->forceFill(['completed_at' => now()])->save();

    return redirect()->back()->with('success', 'Sticky note completed.');
}

public function stickyNoteDestroy(StickyNote $stickyNote)
{
    abort_unless($this->canManageStickyNote($stickyNote), 403);

    $stickyNote->delete();

    return redirect()->back()->with('success', 'Sticky note deleted.');
}

private function canManageStickyNote(StickyNote $stickyNote): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    if ((int) $stickyNote->user_id === (int) $user->id) {
        return true;
    }

    if (! in_array(strtolower((string) $user->role), ['admin', 'hr', 'superadmin', 'super_admin', 'super-admin'], true)) {
        return false;
    }

    if (! $stickyNote->company_id || ! $user->company_id) {
        return true;
    }

    return (int) $stickyNote->company_id === (int) $user->company_id;
}
}

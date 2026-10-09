<?php

namespace App\Services;

use App\Models\Central\CompanyComplaint;
use Illuminate\Http\Request;

class ComplaintConversationFeed
{
    public function response(Request $request, CompanyComplaint $ticket, bool $platform = false)
    {
        $data = $request->validate(['after_id' => 'nullable|integer|min:0']);
        $after = (int) ($data['after_id'] ?? 0);
        $messages = $ticket->conversations()->reorder('id')->where('id', '>', $after)->with('attachments')->limit(100)->get();
        return response()->json([
            'success' => true,
            'html' => view($platform ? 'superadmin.complaints.partials.messages' : 'admin.complaints.messages', compact('ticket', 'messages'))->render(),
            'last_id' => $messages->max('id') ?? $after,
            'count' => $ticket->conversations()->count(),
            'has_more' => $messages->count() === 100,
            'status' => $ticket->status,
        ])->header('Cache-Control', 'private, no-store');
    }
}

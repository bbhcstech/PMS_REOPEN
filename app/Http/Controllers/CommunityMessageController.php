<?php

namespace App\Http\Controllers;

use App\Models\CommunityMessage;
use App\Models\CommunityReaction;
use App\Models\CommunityUserState;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\SystemNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommunityMessageController extends Controller
{
    /**
     * Resolve active tenant company ID
     */
    protected function getCompanyId(): int
    {
        $contextId = app(CompanyContext::class)->id();
        if ($contextId) {
            return (int) $contextId;
        }

        return (int) (Auth::user()?->company_id ?? 1);
    }

    /**
     * Check if user has management/admin rights for moderation and pinning
     */
    protected function canManage(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (method_exists($user, 'canManageEvents')) {
            return $user->canManageEvents();
        }

        $role = strtolower($user->role ?? '');
        return in_array($role, ['admin', 'superadmin', 'hr', 'manager']);
    }

    /**
     * Render main Community page
     */
    public function index(Request $request)
    {
        $companyId = $this->getCompanyId();
        $user = Auth::user();

        // 1. Fetch Company Users Count
        $totalMembers = User::where('company_id', $companyId)->count();
        if ($totalMembers === 0) {
            $totalMembers = 1;
        }

        // 2. Fetch Pinned Messages
        $pinnedMessages = CommunityMessage::forTenant($companyId)
            ->where('is_pinned', true)
            ->with(['user', 'pinnedBy'])
            ->orderBy('pinned_at', 'desc')
            ->get();

        // 3. Fetch Initial Latest Messages (limit 50)
        $messages = CommunityMessage::forTenant($companyId)
            ->with(['user', 'parent.user', 'reactions.user'])
            ->orderBy('id', 'asc')
            ->limit(100)
            ->get();

        // 4. Update User Last Read State
        $lastMessage = $messages->last();
        if ($lastMessage && $user) {
            CommunityUserState::updateOrCreate(
                ['company_id' => $companyId, 'user_id' => $user->id],
                ['last_read_message_id' => $lastMessage->id, 'last_read_at' => now()]
            );
        }

        $canManage = $this->canManage();

        return view('admin.community.index', compact(
            'totalMembers',
            'pinnedMessages',
            'messages',
            'canManage'
        ));
    }

    /**
     * Fetch delta messages for polling (after_id) or pagination (before_id)
     */
    public function fetchMessages(Request $request)
    {
        $companyId = $this->getCompanyId();
        $user = Auth::user();

        $afterId = $request->query('after_id');
        $beforeId = $request->query('before_id');
        $search = trim($request->query('search', ''));

        $query = CommunityMessage::forTenant($companyId)
            ->withTrashed()
            ->with(['user', 'parent.user', 'reactions.user']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        } elseif ($afterId) {
            $query->where('id', '>', (int)$afterId);
        } elseif ($beforeId) {
            $query->where('id', '<', (int)$beforeId)->orderBy('id', 'desc')->limit(30);
        } else {
            $query->orderBy('id', 'asc')->limit(100);
        }

        $messages = $query->get();

        if ($beforeId) {
            $messages = $messages->reverse()->values();
        }

        // Format messages array for frontend rendering
        $formatted = $messages->map(function ($m) use ($user) {
            return $this->formatMessage($m, $user);
        });

        // Mark last read if fetching new messages
        if ($afterId && $messages->isNotEmpty() && $user) {
            $maxId = $messages->max('id');
            CommunityUserState::updateOrCreate(
                ['company_id' => $companyId, 'user_id' => $user->id],
                ['last_read_message_id' => $maxId, 'last_read_at' => now()]
            );
        }

        // Pinned messages list
        $pinnedMessages = CommunityMessage::forTenant($companyId)
            ->where('is_pinned', true)
            ->with(['user', 'pinnedBy'])
            ->orderBy('pinned_at', 'desc')
            ->get()
            ->map(fn($pm) => [
                'id' => $pm->id,
                'message' => Str::limit($pm->message, 80),
                'sender_name' => $pm->user?->name ?? 'User',
            ]);

        return response()->json([
            'success' => true,
            'messages' => $formatted,
            'pinned_messages' => $pinnedMessages,
            'can_manage' => $this->canManage(),
        ]);
    }

    /**
     * Store new Community Message
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:5000',
            'parent_id' => 'nullable|integer',
            'attachment' => 'nullable|file|max:10240|mimes:jpeg,png,jpg,webp,gif,pdf,doc,docx,xls,xlsx,txt,zip',
        ]);

        $messageText = trim($request->input('message', ''));
        $hasAttachment = $request->hasFile('attachment');

        if (empty($messageText) && !$hasAttachment) {
            return response()->json([
                'success' => false,
                'error' => 'Please provide a message or attach a file.'
            ], 422);
        }

        $companyId = $this->getCompanyId();
        $user = Auth::user();

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentType = null;
        $attachmentSize = null;

        if ($hasAttachment) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentSize = $file->getSize();

            $ext = strtolower($file->getClientOriginalExtension());
            $mime = $file->getMimeType();

            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']) || str_contains($mime, 'image')) {
                $attachmentType = 'image';
            } elseif ($ext === 'pdf') {
                $attachmentType = 'pdf';
            } elseif (in_array($ext, ['doc', 'docx'])) {
                $attachmentType = 'document';
            } elseif (in_array($ext, ['xls', 'xlsx'])) {
                $attachmentType = 'spreadsheet';
            } else {
                $attachmentType = 'file';
            }

            $filename = 'community_' . time() . '_' . Str::random(8) . '.' . $ext;
            $uploadDir = "uploads/community/{$companyId}";
            $destinationPath = public_path($uploadDir);

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $file->move($destinationPath, $filename);
            $attachmentPath = "{$uploadDir}/{$filename}";
        }

        $msg = CommunityMessage::create([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'parent_id' => $request->input('parent_id'),
            'message' => $messageText,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_type' => $attachmentType,
            'attachment_size' => $attachmentSize,
        ]);

        $msg->load(['user', 'parent.user', 'reactions.user']);

        // Update sender's last read state
        CommunityUserState::updateOrCreate(
            ['company_id' => $companyId, 'user_id' => $user->id],
            ['last_read_message_id' => $msg->id, 'last_read_at' => now()]
        );

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($msg, $user),
        ]);
    }

    /**
     * Edit own message
     */
    public function update(Request $request, $id)
    {
        $companyId = $this->getCompanyId();
        $user = Auth::user();

        $msg = CommunityMessage::forTenant($companyId)->findOrFail($id);

        if ($msg->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'You can only edit your own messages.'
            ], 403);
        }

        if ($msg->trashed()) {
            return response()->json([
                'success' => false,
                'error' => 'Deleted messages cannot be edited.'
            ], 422);
        }

        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $msg->message = trim($request->input('message'));
        $msg->edited_at = now();
        $msg->save();

        $msg->load(['user', 'parent.user', 'reactions.user']);

        return response()->json([
            'success' => true,
            'message' => $this->formatMessage($msg, $user),
        ]);
    }

    /**
     * Delete message (Own message OR Admin Moderation)
     */
    public function destroy(Request $request, $id)
    {
        $companyId = $this->getCompanyId();
        $user = Auth::user();

        $msg = CommunityMessage::forTenant($companyId)->findOrFail($id);

        $isOwner = ($msg->user_id === $user->id);
        $canManage = $this->canManage();

        if (!$isOwner && !$canManage) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to delete this message.'
            ], 403);
        }

        $msg->deleted_by = $user->id;
        $msg->is_pinned = false;
        $msg->save();
        $msg->delete();

        return response()->json([
            'success' => true,
            'message_id' => $msg->id,
            'is_moderated' => !$isOwner,
        ]);
    }

    /**
     * Toggle Emoji Reaction on Message
     */
    public function react(Request $request, $id)
    {
        $request->validate([
            'emoji' => 'required|string|max:32',
        ]);

        $companyId = $this->getCompanyId();
        $user = Auth::user();
        $emoji = $request->input('emoji');

        $msg = CommunityMessage::forTenant($companyId)->findOrFail($id);

        $existing = CommunityReaction::forTenant($companyId)
            ->where('message_id', $msg->id)
            ->where('user_id', $user->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            CommunityReaction::create([
                'company_id' => $companyId,
                'message_id' => $msg->id,
                'user_id' => $user->id,
                'emoji' => $emoji,
            ]);
        }

        $msg->load('reactions.user');

        return response()->json([
            'success' => true,
            'message_id' => $msg->id,
            'reactions' => $this->formatReactions($msg->reactions, $user->id),
        ]);
    }

    /**
     * Pin or Unpin a message (Admin/Manager/HR only)
     */
    public function togglePin(Request $request, $id)
    {
        if (!$this->canManage()) {
            return response()->json([
                'success' => false,
                'error' => 'Only Admin, HR, or Manager can pin messages.'
            ], 403);
        }

        $companyId = $this->getCompanyId();
        $user = Auth::user();

        $msg = CommunityMessage::forTenant($companyId)->findOrFail($id);

        $msg->is_pinned = !$msg->is_pinned;
        $msg->pinned_by = $msg->is_pinned ? $user->id : null;
        $msg->pinned_at = $msg->is_pinned ? now() : null;
        $msg->save();

        return response()->json([
            'success' => true,
            'is_pinned' => $msg->is_pinned,
            'message' => $msg->is_pinned ? 'Message pinned to Community.' : 'Message unpinned.',
        ]);
    }

    /**
     * Helper: Format single message object for JSON transmission
     */
    protected function formatMessage(CommunityMessage $msg, $currentUser): array
    {
        $isDeleted = $msg->trashed();
        $deletedByAdmin = $isDeleted && ($msg->deleted_by !== $msg->user_id);

        $senderName = $msg->user ? $msg->user->name : 'Deleted User';
        $senderRole = $msg->user ? ucfirst($msg->user->role ?? 'Employee') : '';
        
        $avatarUrl = null;
        if ($msg->user && !empty($msg->user->profile_image)) {
            $avatarUrl = asset($msg->user->profile_image);
        }

        $words = explode(' ', trim($senderName));
        $senderInitials = count($words) >= 2
            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
            : strtoupper(substr($senderName, 0, 2));

        $parentData = null;
        if ($msg->parent) {
            $parentData = [
                'id' => $msg->parent->id,
                'sender_name' => $msg->parent->user?->name ?? 'User',
                'message' => Str::limit($msg->parent->message ?: ($msg->parent->attachment_name ? '📷 Attachment' : ''), 60),
            ];
        }

        return [
            'id' => $msg->id,
            'user_id' => $msg->user_id,
            'sender_name' => $senderName,
            'sender_role' => $senderRole,
            'sender_initials' => $senderInitials,
            'avatar_url' => $avatarUrl,
            'is_self' => ($currentUser && $msg->user_id === $currentUser->id),
            'message' => $msg->message,
            'attachment_url' => $msg->attachment_url,
            'attachment_name' => $msg->attachment_name,
            'attachment_type' => $msg->attachment_type,
            'attachment_size_formatted' => $msg->attachment_size ? number_format($msg->attachment_size / 1024, 1) . ' KB' : null,
            'is_pinned' => (bool) $msg->is_pinned,
            'is_edited' => (bool) $msg->edited_at,
            'is_deleted' => $isDeleted,
            'deleted_text' => $deletedByAdmin ? 'This message was removed by an administrator.' : 'This message was deleted.',
            'parent' => $parentData,
            'reactions' => $this->formatReactions($msg->reactions, $currentUser?->id),
            'time' => $msg->created_at ? $msg->created_at->format('h:i A') : '',
            'date_group' => $msg->formatted_date,
            'created_at_iso' => $msg->created_at ? $msg->created_at->toIso8601String() : '',
            'can_edit' => ($currentUser && $msg->user_id === $currentUser->id && !$isDeleted),
            'can_delete' => ($currentUser && ($msg->user_id === $currentUser->id || $this->canManage()) && !$isDeleted),
        ];
    }

    /**
     * Helper: Group reactions by emoji and indicate if current user reacted
     */
    protected function formatReactions($reactionsCollection, $currentUserId): array
    {
        if (!$reactionsCollection) return [];

        $grouped = [];
        foreach ($reactionsCollection as $r) {
            $emoji = $r->emoji;
            if (!isset($grouped[$emoji])) {
                $grouped[$emoji] = [
                    'emoji' => $emoji,
                    'count' => 0,
                    'user_reacted' => false,
                    'users' => [],
                ];
            }

            $grouped[$emoji]['count']++;
            if ($currentUserId && $r->user_id === $currentUserId) {
                $grouped[$emoji]['user_reacted'] = true;
            }
            if ($r->user) {
                $grouped[$emoji]['users'][] = $r->user->name;
            }
        }

        return array_values($grouped);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\EventRsvp;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\SystemNotificationService;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Helper to get current company ID securely.
     */
    protected function getCompanyId(): ?int
    {
        $context = app(CompanyContext::class);
        return $context->id() ?? Auth::user()?->company_id;
    }

    /**
     * Helper to check if current user can create/manage events.
     */
    protected function canManageEvents(?User $user = null): bool
    {
        $user ??= Auth::user();
        if (! $user) return false;

        $role = $user->normalizedRole();
        if (in_array($role, ['admin', 'superadmin'], true)) {
            return true;
        }

        if (in_array($role, ['hr', 'manager'], true)) {
            return true;
        }

        return $user->hasModulePermission('events', 'create');
    }

    /**
     * Display a listing of company events (List & Calendar Views).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId();
        $canManage = $this->canManageEvents($user);

        $query = Event::with(['organizer', 'creator', 'rsvps.user', 'photos' => function ($q) {
            $q->ordered();
        }])
        ->withCount('photos')
        ->forTenant($companyId);

        // Employee role cannot see drafts created by others
        if (! $canManage) {
            $query->where(function ($q) use ($user) {
                $q->where('status', '!=', 'draft')
                  ->orWhere('created_by', $user->id);
            });
        }

        // --- KPI Metrics ---
        $kpiBaseQuery = Event::forTenant($companyId);
        if (! $canManage) {
            $kpiBaseQuery->where('status', '!=', 'draft');
        }

        $totalEvents = (clone $kpiBaseQuery)->count();
        $upcomingEvents = (clone $kpiBaseQuery)->upcoming()->count();
        $todayEvents = (clone $kpiBaseQuery)->today()->count();
        $pastEvents = (clone $kpiBaseQuery)->past()->count();

        // --- Search & Filters ---
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('organizer', function ($oq) use ($search) {
                      $oq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('event_type') && $request->event_type !== 'all') {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('time_filter')) {
            if ($request->time_filter === 'upcoming') {
                $query->upcoming();
            } elseif ($request->time_filter === 'today') {
                $query->today();
            } elseif ($request->time_filter === 'past') {
                $query->past();
            }
        }

        if ($request->filled('rsvp_filter') && $request->rsvp_filter === 'required') {
            $query->where('rsvp_required', true);
        }

        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('start_date', '<=', $request->end_date);
        }

        // Sorting: Nearest upcoming first by default
        $events = $query->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'asc')
            ->paginate(12)
            ->withQueryString();

        // Get company users for Organizer dropdown (Admins, HRs, Managers)
        $users = User::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->whereIn('role', ['admin', 'hr', 'manager', 'employee'])
            ->orderBy('name')
            ->get();

        // Preset Event Types
        $eventTypes = [
            'Meeting',
            'Holiday',
            'Training',
            'Workshop',
            'Seminar',
            'Conference',
            'Team Building',
            'Company Anniversary',
            'Birthday',
            'Sports Event',
            'Cultural Event',
            'Other'
        ];

        return view('admin.events.index', compact(
            'events',
            'totalEvents',
            'upcomingEvents',
            'todayEvents',
            'pastEvents',
            'canManage',
            'users',
            'eventTypes'
        ));
    }

    /**
     * Return calendar JSON events feed.
     */
    public function calendarData(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId();
        $canManage = $this->canManageEvents($user);

        $query = Event::forTenant($companyId)->with('organizer');

        if (! $canManage) {
            $query->where('status', '!=', 'draft');
        }

        if ($request->filled('start')) {
            $query->where('start_date', '>=', date('Y-m-d', strtotime($request->start)));
        }

        if ($request->filled('end')) {
            $query->where('start_date', '<=', date('Y-m-d', strtotime($request->end)));
        }

        $events = $query->get()->map(function ($e) {
            $color = match ($e->status) {
                'draft' => '#f59e0b',
                'cancelled' => '#ef4444',
                'completed' => '#6b7280',
                default => '#0f744c', // Primary company green
            };

            $start = $e->start_date->format('Y-m-d');
            if ($e->start_time) {
                $start .= 'T' . date('H:i:s', strtotime($e->start_time));
            }

            $end = $e->end_date ? $e->end_date->format('Y-m-d') : $e->start_date->format('Y-m-d');
            if ($e->end_time) {
                $end .= 'T' . date('H:i:s', strtotime($e->end_time));
            }

            return [
                'id' => $e->id,
                'title' => $e->title,
                'start' => $start,
                'end' => $end,
                'backgroundColor' => $color,
                'borderColor' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'event_type' => $e->event_type,
                    'status' => $e->status,
                    'location' => $e->location ?: ($e->meeting_url ?: 'N/A'),
                    'location_type' => $e->location_type,
                    'organizer' => $e->organizer?->name ?? 'Company',
                    'rsvp_required' => $e->rsvp_required,
                ]
            ];
        });

        return response()->json($events);
    }

    /**
     * Store a new event in database.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|string|max:100',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'start_time' => 'nullable',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'end_time' => 'nullable',
            'location_type' => 'required|in:physical,online,hybrid',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url|max:500',
            'organizer_id' => 'nullable|exists:users,id',
            'max_participants' => 'nullable|integer|min:1',
            'reminder' => 'nullable|string',
            'status' => 'required|in:draft,published,cancelled,completed',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $companyId = $this->getCompanyId();

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $filename = 'event_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/events/banners');
            if (! File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $bannerPath = 'uploads/events/banners/' . $filename;
        }

        $event = Event::create([
            'company_id' => $companyId,
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'event_type' => $request->event_type,
            'description' => $request->description,
            'banner' => $bannerPath,
            'start_date' => $request->start_date,
            'start_time' => $request->start_time,
            'end_date' => $request->end_date ?: $request->start_date,
            'end_time' => $request->end_time,
            'location_type' => $request->location_type,
            'location' => $request->location,
            'meeting_url' => $request->meeting_url,
            'organizer_id' => $request->organizer_id ?: $user->id,
            'max_participants' => $request->max_participants,
            'rsvp_required' => $request->has('rsvp_required') ? 1 : 0,
            'reminder' => $request->reminder,
            'status' => $request->status,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        // Audit Log
        try {
            UserActivity::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'activity' => "Created company event '{$event->title}' ({$event->status})",
            ]);
        } catch (\Throwable $e) {}

        // Send Company Notification if Published
        if ($event->status === 'published') {
            try {
                $startFmt = date('d M Y', strtotime($event->start_date));
                $timeFmt = $event->start_time ? ' at ' . date('h:i A', strtotime($event->start_time)) : '';
                SystemNotificationService::notifyAllRoles(
                    '🎉 New Company Event',
                    "{$event->title} scheduled for {$startFmt}{$timeFmt}.",
                    route('events.index')
                );
            } catch (\Throwable $e) {}
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Event created successfully!',
                'event' => $event,
            ]);
        }

        return redirect()->route('events.index')->with('success', 'Event created successfully!');
    }

    /**
     * Return detailed event info (JSON or modal data).
     */
    public function show(Request $request, $id)
    {
        $companyId = $this->getCompanyId();
        $event = Event::with(['organizer', 'creator', 'rsvps.user', 'photos.uploader'])
            ->withCount('photos')
            ->forTenant($companyId)
            ->findOrFail($id);

        $userRsvp = $event->user_rsvp;
        $counts = $event->rsvp_counts;

        $responseData = [
            'success' => true,
            'event' => $event,
            'banner_url' => $event->banner_url,
            'formatted_start_date' => $event->start_date ? $event->start_date->format('d M Y') : '',
            'formatted_start_time' => $event->start_time ? date('h:i A', strtotime($event->start_time)) : '',
            'formatted_end_date' => $event->end_date ? $event->end_date->format('d M Y') : '',
            'formatted_end_time' => $event->end_time ? date('h:i A', strtotime($event->end_time)) : '',
            'user_rsvp' => $userRsvp ? $userRsvp->response : null,
            'rsvp_counts' => $counts,
            'can_manage' => $this->canManageEvents(),
            'photos' => $event->photos,
            'photos_count' => $event->photos_count,
        ];

        return response()->json($responseData);
    }

    /**
     * Update an existing event.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'event_type' => 'required|string|max:100',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'start_time' => 'nullable',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'end_time' => 'nullable',
            'location_type' => 'required|in:physical,online,hybrid',
            'location' => 'nullable|string|max:255',
            'meeting_url' => 'nullable|url|max:500',
            'organizer_id' => 'nullable|exists:users,id',
            'max_participants' => 'nullable|integer|min:1',
            'reminder' => 'nullable|string',
            'status' => 'required|in:draft,published,cancelled,completed',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $oldStatus = $event->status;

        if ($request->hasFile('banner')) {
            if ($event->banner && File::exists(public_path($event->banner))) {
                File::delete(public_path($event->banner));
            }
            $file = $request->file('banner');
            $filename = 'event_' . time() . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
            $destination = public_path('uploads/events/banners');
            if (! File::exists($destination)) {
                File::makeDirectory($destination, 0755, true);
            }
            $file->move($destination, $filename);
            $event->banner = 'uploads/events/banners/' . $filename;
        }

        $event->update([
            'title' => $request->title,
            'event_type' => $request->event_type,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'start_time' => $request->start_time,
            'end_date' => $request->end_date ?: $request->start_date,
            'end_time' => $request->end_time,
            'location_type' => $request->location_type,
            'location' => $request->location,
            'meeting_url' => $request->meeting_url,
            'organizer_id' => $request->organizer_id ?: $event->organizer_id,
            'max_participants' => $request->max_participants,
            'rsvp_required' => $request->has('rsvp_required') ? 1 : 0,
            'reminder' => $request->reminder,
            'status' => $request->status,
            'updated_by' => $user->id,
        ]);

        // Audit Log
        try {
            UserActivity::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'activity' => "Updated event '{$event->title}'",
            ]);
        } catch (\Throwable $e) {}

        // Notify if newly published or updated published event
        if ($event->status === 'published') {
            try {
                $titleNotif = ($oldStatus === 'draft') ? '🎉 New Company Event Published' : '📢 Company Event Updated';
                SystemNotificationService::notifyAllRoles(
                    $titleNotif,
                    "{$event->title} has been updated.",
                    route('events.index')
                );
            } catch (\Throwable $e) {}
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully!',
                'event' => $event,
            ]);
        }

        return redirect()->route('events.index')->with('success', 'Event updated successfully!');
    }

    /**
     * Publish a draft event.
     */
    public function publish(Request $request, $id)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($id);

        $event->update([
            'status' => 'published',
            'updated_by' => $user->id,
        ]);

        try {
            $startFmt = date('d M Y', strtotime($event->start_date));
            SystemNotificationService::notifyAllRoles(
                '🎉 New Company Event Published',
                "{$event->title} scheduled for {$startFmt}.",
                route('events.index')
            );
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Event published successfully!'
        ]);
    }

    /**
     * Cancel an event.
     */
    public function cancel(Request $request, $id)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($id);

        $event->update([
            'status' => 'cancelled',
            'updated_by' => $user->id,
        ]);

        try {
            SystemNotificationService::notifyAllRoles(
                '⚠️ Company Event Cancelled',
                "The event '{$event->title}' scheduled for {$event->start_date->format('d M Y')} has been cancelled.",
                route('events.index')
            );
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Event marked as cancelled.'
        ]);
    }

    /**
     * Delete an event.
     */
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized action.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized action.');
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($id);

        if ($event->banner && File::exists(public_path($event->banner))) {
            File::delete(public_path($event->banner));
        }

        $event->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event deleted successfully!']);
        }

        return redirect()->route('events.index')->with('success', 'Event deleted successfully!');
    }

    /**
     * Handle user RSVP for an event.
     */
    public function rsvp(Request $request, $id)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId();

        $event = Event::forTenant($companyId)->findOrFail($id);

        if ($event->status === 'cancelled') {
            return response()->json(['error' => 'Cannot RSVP to a cancelled event.'], 422);
        }

        $request->validate([
            'response' => 'required|in:going,maybe,not_going',
        ]);

        $rsvp = EventRsvp::updateOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $user->id,
            ],
            [
                'company_id' => $companyId,
                'response' => $request->response,
                'responded_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'RSVP response updated!',
            'response' => $rsvp->response,
            'counts' => $event->fresh()->rsvp_counts,
        ]);
    }

    /* =========================================================================
     | EVENT MEMORIES / GALLERY PHOTO METHODS
     | ========================================================================= */

    /**
     * Upload multiple photographs to an event gallery.
     */
    public function uploadPhotos(Request $request, $id)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($id);

        $request->validate([
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|image|mimes:jpeg,jpg,png,webp|max:10240',
            'caption' => 'nullable|string|max:500',
        ]);

        $uploadedPhotos = [];
        $failedCount = 0;
        $destination = public_path("uploads/events/{$event->id}/gallery");

        if (! File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $maxOrder = EventPhoto::where('event_id', $event->id)->max('display_order') ?? 0;

        foreach ($request->file('photos') as $index => $file) {
            try {
                $filename = 'photo_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($destination, $filename);
                $imagePath = "uploads/events/{$event->id}/gallery/" . $filename;

                $photo = EventPhoto::create([
                    'company_id' => $companyId,
                    'event_id' => $event->id,
                    'uploaded_by' => $user->id,
                    'image_path' => $imagePath,
                    'thumbnail_path' => $imagePath,
                    'caption' => $request->caption,
                    'display_order' => $maxOrder + $index + 1,
                    'is_gallery_cover' => false,
                ]);

                $uploadedPhotos[] = $photo->load('uploader');
            } catch (\Throwable $e) {
                $failedCount++;
            }
        }

        // Activity Log
        try {
            $photoCount = count($uploadedPhotos);
            UserActivity::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'activity' => "Uploaded {$photoCount} photo(s) to event memory '{$event->title}'",
            ]);
        } catch (\Throwable $e) {}

        // Notification to Company Users
        try {
            $photoCount = count($uploadedPhotos);
            if ($photoCount > 0 && $event->status === 'published') {
                SystemNotificationService::notifyAllRoles(
                    '📸 New Event Memories Added',
                    "{$photoCount} new photo(s) added to {$event->title}.",
                    route('events.index')
                );
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => count($uploadedPhotos) . ' photo(s) uploaded successfully.' . ($failedCount > 0 ? " ({$failedCount} failed)" : ''),
            'uploaded_count' => count($uploadedPhotos),
            'failed_count' => $failedCount,
            'photos' => $uploadedPhotos,
            'total_photos_count' => $event->photos()->count(),
        ]);
    }

    /**
     * Get all photographs for an event.
     */
    public function getPhotos($id)
    {
        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($id);
        $photos = $event->photos()->with('uploader')->get();

        return response()->json([
            'success' => true,
            'event_id' => $event->id,
            'event_title' => $event->title,
            'total' => $photos->count(),
            'photos' => $photos,
            'can_manage' => $this->canManageEvents(),
        ]);
    }

    /**
     * Update caption of a photo.
     */
    public function updatePhoto(Request $request, $eventId, $photoId)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($eventId);
        $photo = EventPhoto::forTenant($companyId)->where('event_id', $event->id)->findOrFail($photoId);

        $request->validate([
            'caption' => 'nullable|string|max:500',
        ]);

        $photo->update([
            'caption' => $request->caption,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Photo caption updated successfully.',
            'photo' => $photo->load('uploader'),
        ]);
    }

    /**
     * Set a photo as gallery cover for an event.
     */
    public function setGalleryCover($eventId, $photoId)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($eventId);
        $photo = EventPhoto::forTenant($companyId)->where('event_id', $event->id)->findOrFail($photoId);

        // Reset previous gallery cover
        EventPhoto::where('event_id', $event->id)->update(['is_gallery_cover' => false]);

        // Set selected photo as cover
        $photo->update(['is_gallery_cover' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Gallery cover photo updated.',
            'photo' => $photo,
        ]);
    }

    /**
     * Reorder photos for an event gallery.
     */
    public function reorderPhotos(Request $request, $eventId)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($eventId);

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:event_photos,id',
        ]);

        foreach ($request->order as $index => $photoId) {
            EventPhoto::forTenant($companyId)
                ->where('event_id', $event->id)
                ->where('id', $photoId)
                ->update(['display_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo order saved successfully.',
        ]);
    }

    /**
     * Delete a single photo.
     */
    public function deletePhoto($eventId, $photoId)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($eventId);
        $photo = EventPhoto::forTenant($companyId)->where('event_id', $event->id)->findOrFail($photoId);

        if ($photo->image_path && File::exists(public_path($photo->image_path))) {
            File::delete(public_path($photo->image_path));
        }

        $photo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Photograph removed from event memories.',
            'remaining_count' => $event->photos()->count(),
        ]);
    }

    /**
     * Delete multiple selected photos.
     */
    public function deleteBulkPhotos(Request $request, $eventId)
    {
        $user = Auth::user();
        if (! $this->canManageEvents($user)) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $companyId = $this->getCompanyId();
        $event = Event::forTenant($companyId)->findOrFail($eventId);

        $request->validate([
            'photo_ids' => 'required|array|min:1',
            'photo_ids.*' => 'integer|exists:event_photos,id',
        ]);

        $photos = EventPhoto::forTenant($companyId)
            ->where('event_id', $event->id)
            ->whereIn('id', $request->photo_ids)
            ->get();

        foreach ($photos as $photo) {
            if ($photo->image_path && File::exists(public_path($photo->image_path))) {
                File::delete(public_path($photo->image_path));
            }
            $photo->delete();
        }

        return response()->json([
            'success' => true,
            'message' => count($photos) . ' photo(s) deleted successfully.',
            'remaining_count' => $event->photos()->count(),
        ]);
    }
}

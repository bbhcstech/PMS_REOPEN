<?php

namespace App\Services;

use App\Models\Central\CompanyComplaint;
use App\Models\Central\ComplaintConversation;
use App\Models\Central\ComplaintAttachment;
use App\Models\Central\ComplaintActivity;
use App\Models\Central\CentralNotification;
use App\Models\Central\SuperAdminActivityLog;
use App\Models\Central\Company;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComplaintService
{
    /**
     * Create a new complaint from a Company Admin.
     */
    public function createComplaint(array $data, Company $company, $user): CompanyComplaint
    {
        $ticketId = CompanyComplaint::generateTicketId();

        $complaint = CompanyComplaint::create([
            'ticket_id'         => $ticketId,
            'company_id'        => $company->id,
            'raised_by_id'      => $user?->id ?? 0,
            'raised_by_type'    => 'company_admin',
            'raised_by_name'    => $user?->name ?? $company->name . ' Admin',
            'raised_by_email'   => $user?->email ?? $company->email,
            'subject'           => $data['subject'],
            'category'          => $data['category'] ?? 'Technical Issue',
            'priority'          => strtoupper($data['priority'] ?? 'MEDIUM'),
            'status'            => 'OPEN',
            'related_module'    => $data['related_module'] ?? null,
            'related_record_id' => $data['related_record_id'] ?? null,
            'description'       => $data['description'],
            'last_reply_at'     => now(),
        ]);

        // Add initial conversation entry
        ComplaintConversation::create([
            'complaint_id' => $complaint->id,
            'sender_type'  => 'company_admin',
            'sender_id'    => $user?->id ?? 0,
            'sender_name'  => $user?->name ?? $company->name . ' Admin',
            'sender_email' => $user?->email ?? $company->email,
            'message'      => $data['description'],
        ]);

        // Handle attachment file uploads if present
        if (!empty($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $this->storeAttachment($complaint, null, $data['attachment'], 'company_admin');
        }

        // Log complaint activity
        ComplaintActivity::log(
            $complaint->id,
            'Ticket Created',
            $complaint->raised_by_name,
            'company_admin',
            $user?->id,
            null,
            'OPEN',
            "Complaint ticket {$ticketId} created by {$complaint->raised_by_name}."
        );

        // Generate Central System Notification for Super Admin
        CentralNotification::createNotification([
            'company_id'        => $company->id,
            'type'              => 'NEW_COMPLAINT',
            'title'             => "New Complaint Raised: {$ticketId}",
            'message'           => "{$company->name} raised a {$complaint->priority} priority complaint regarding {$complaint->category}: {$complaint->subject}",
            'severity'          => in_array($complaint->priority, ['HIGH', 'CRITICAL']) ? 'CRITICAL' : 'WARNING',
            'related_module'    => 'Complaints',
            'related_record_id' => $ticketId,
            'action_url'        => route('superadmin.complaints.show', $complaint->id),
            'target_audience'   => 'super_admin',
        ]);

        return $complaint;
    }

    /**
     * Add a response message to a complaint timeline.
     */
    public function addResponse(CompanyComplaint $complaint, string $message, $sender, string $senderType = 'super_admin', array $files = []): ComplaintConversation
    {
        $senderName = $sender?->name ?? ($senderType === 'super_admin' ? 'Super Admin' : 'Company Admin');
        $senderEmail = $sender?->email ?? null;

        $conversation = ComplaintConversation::create([
            'complaint_id' => $complaint->id,
            'sender_type'  => $senderType,
            'sender_id'    => $sender?->id ?? null,
            'sender_name'  => $senderName,
            'sender_email' => $senderEmail,
            'message'      => $message,
        ]);

        // Save attachments if uploaded
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $this->storeAttachment($complaint, $conversation->id, $file, $senderType);
            }
        }

        $complaint->update(['last_reply_at' => now()]);

        // Auto update status to WAITING FOR COMPANY if Super Admin responds, or IN PROGRESS if Company Admin responds
        if ($senderType === 'super_admin' && in_array($complaint->status, ['OPEN', 'IN PROGRESS'])) {
            $complaint->update(['status' => 'WAITING FOR COMPANY']);
        } elseif ($senderType === 'company_admin' && $complaint->status === 'WAITING FOR COMPANY') {
            $complaint->update(['status' => 'IN PROGRESS']);
        }

        // Log activity
        ComplaintActivity::log(
            $complaint->id,
            'Response Added',
            $senderName,
            $senderType,
            $sender?->id,
            null,
            null,
            "{$senderName} added a response to ticket {$complaint->ticket_id}."
        );

        if ($senderType === 'super_admin') {
            SuperAdminActivityLog::record(
                'complaint_response_added',
                $complaint->company_id,
                "Responded to complaint ticket #{$complaint->ticket_id}",
                ['ticket_id' => $complaint->ticket_id, 'complaint_id' => $complaint->id]
            );

            // Generate notification for Company Admin
            CentralNotification::createNotification([
                'company_id'        => $complaint->company_id,
                'type'              => 'COMPLAINT_RESPONSE_RECEIVED',
                'title'             => "Response Received on Ticket {$complaint->ticket_id}",
                'message'           => "Super Admin replied to your ticket: {$complaint->subject}",
                'severity'          => 'INFO',
                'related_module'    => 'Complaints',
                'related_record_id' => $complaint->ticket_id,
                'action_url'        => route('admin.company-complaints.show', $complaint->id),
                'target_audience'   => 'company_admin',
            ]);
        } else {
            // Notification for Super Admin
            CentralNotification::createNotification([
                'company_id'        => $complaint->company_id,
                'type'              => 'COMPLAINT_RESPONSE_RECEIVED',
                'title'             => "Company Reply on Ticket {$complaint->ticket_id}",
                'message'           => "{$complaint->company?->name} replied to ticket: {$complaint->subject}",
                'severity'          => 'INFO',
                'related_module'    => 'Complaints',
                'related_record_id' => $complaint->ticket_id,
                'action_url'        => route('superadmin.complaints.show', $complaint->id),
                'target_audience'   => 'super_admin',
            ]);
        }

        return $conversation;
    }

    /**
     * Change ticket status.
     */
    public function updateStatus(CompanyComplaint $complaint, string $newStatus, $actor, string $actorType = 'super_admin', ?string $note = null): bool
    {
        $newStatus = strtoupper($newStatus);
        $oldStatus = $complaint->status;

        if ($oldStatus === $newStatus) {
            return false;
        }

        $actorName = $actor?->name ?? ($actorType === 'super_admin' ? 'Super Admin' : 'Company Admin');

        $complaint->update([
            'status'        => $newStatus,
            'last_reply_at' => now(),
        ]);

        ComplaintActivity::log(
            $complaint->id,
            'Status Changed',
            $actorName,
            $actorType,
            $actor?->id,
            $oldStatus,
            $newStatus,
            "Ticket status changed from {$oldStatus} to {$newStatus}." . ($note ? " Note: {$note}" : '')
        );

        if ($actorType === 'super_admin') {
            SuperAdminActivityLog::record(
                'complaint_status_updated',
                $complaint->company_id,
                "Updated ticket #{$complaint->ticket_id} status from {$oldStatus} to {$newStatus}",
                ['ticket_id' => $complaint->ticket_id, 'old_status' => $oldStatus, 'new_status' => $newStatus]
            );

            // Generate Notification for Company Admin
            CentralNotification::createNotification([
                'company_id'        => $complaint->company_id,
                'type'              => 'COMPLAINT_STATUS_CHANGED',
                'title'             => "Ticket {$complaint->ticket_id} Status Updated",
                'message'           => "Your complaint ticket status has been changed to {$newStatus}.",
                'severity'          => in_array($newStatus, ['RESOLVED', 'CLOSED']) ? 'SUCCESS' : 'INFO',
                'related_module'    => 'Complaints',
                'related_record_id' => $complaint->ticket_id,
                'action_url'        => route('admin.company-complaints.show', $complaint->id),
                'target_audience'   => 'company_admin',
            ]);
        }

        return true;
    }

    /**
     * Assign complaint to Super Admin or support staff.
     */
    public function assignTicket(CompanyComplaint $complaint, ?int $superAdminId, string $assigneeName, $actor): void
    {
        $actorName = $actor?->name ?? 'Super Admin';
        $prevAssignee = $complaint->assigned_to_name ?? 'Unassigned';

        $complaint->update([
            'assigned_super_admin_id' => $superAdminId,
            'assigned_to_name'        => $assigneeName,
            'assigned_at'             => now(),
        ]);

        ComplaintActivity::log(
            $complaint->id,
            'Ticket Assigned',
            $actorName,
            'super_admin',
            $actor?->id,
            $prevAssignee,
            $assigneeName,
            "Ticket #{$complaint->ticket_id} assigned to {$assigneeName}."
        );

        SuperAdminActivityLog::record(
            'complaint_assigned',
            $complaint->company_id,
            "Assigned ticket #{$complaint->ticket_id} to {$assigneeName}",
            ['ticket_id' => $complaint->ticket_id, 'assigned_to' => $assigneeName]
        );
    }

    /**
     * Safely store file attachments (no executable files allowed).
     */
    protected function storeAttachment(CompanyComplaint $complaint, ?int $conversationId, UploadedFile $file, string $uploadedByType): ComplaintAttachment
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, ['php', 'exe', 'sh', 'bat', 'cmd', 'js', 'vbs', 'phtml', 'phar', 'cgi', 'pl'])) {
            throw new \InvalidArgumentException("Security Alert: Executable file upload disallowed.");
        }

        $folder = 'admin/uploads/complaints/' . date('Y/m');
        $destinationPath = public_path($folder);
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $filename = time() . '_' . Str::random(8) . '.' . $extension;
        $file->move($destinationPath, $filename);
        $filePath = $folder . '/' . $filename;

        return ComplaintAttachment::create([
            'complaint_id'     => $complaint->id,
            'conversation_id'   => $conversationId,
            'original_name'    => $file->getClientOriginalName(),
            'file_path'        => $filePath,
            'file_size'        => filesize(public_path($filePath)) ?: $file->getSize(),
            'mime_type'        => $file->getClientMimeType() ?: $extension,
            'uploaded_by_type' => $uploadedByType,
        ]);
    }
}

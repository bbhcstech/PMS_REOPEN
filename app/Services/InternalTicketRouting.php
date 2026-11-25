<?php

namespace App\Services;

use App\Models\Central\{Company, CompanyComplaint};
use App\Models\User;
use App\Support\PlatformSupportAccess;
use Illuminate\Support\Facades\{DB, Schema};

/** Repair legacy staff requests that were incorrectly stored as platform complaints. */
class InternalTicketRouting
{
    public function reconcile(Company $company): int
    {
        // Never switch workspaces during a page read or infer a tenant from a numeric user ID.
        if (!$company->db_name || DB::connection('tenant')->getDatabaseName() !== $company->db_name) return 0;
        if (!Schema::connection('central')->hasTable('company_complaints')) return 0;
        foreach (['tickets', 'ticket_activities', 'replies'] as $table) {
            if (!Schema::connection('tenant')->hasTable($table)) return 0;
        }

        $moved = 0;
        foreach (CompanyComplaint::where('company_id', $company->id)->orderBy('id')->cursor() as $candidate) {
            $moved += DB::connection('central')->transaction(function () use ($candidate, $company) {
                $source = CompanyComplaint::whereKey($candidate->id)->lockForUpdate()->first();
                if (!$source) return 0;
                $query = DB::connection('tenant')->table('users')->where('company_id', $company->id);
                if ($source->raised_by_email) $query->whereRaw('LOWER(email) = ?', [strtolower($source->raised_by_email)]);
                else $query->where('id', $source->raised_by_id);
                $owners = $query->get();
                // Unknown/ambiguous owners must be retained for an administrator to review.
                if ($owners->count() !== 1) return 0;
                $owner = (new User)->forceFill((array) $owners->first());
                if (PlatformSupportAccess::companyAdmin($owner)) return 0;

                $tenant = DB::connection('tenant');
                $marker = 'platform-complaint:'.$company->id.':'.$source->id;
                $ticketId = $tenant->transaction(function () use ($tenant, $source, $owner, $company, $marker) {
                    // The central row lock serializes imports; the durable activity marker also
                    // handles retries if the tenant committed but the central commit failed.
                    $import = $tenant->table('ticket_activities')->where('type', 'Platform Ticket Import')->where('content', $marker)->first();
                    if ($import) return $import->ticket_id;
                    $status = match (strtoupper($source->status)) {
                        'IN PROGRESS' => 'in_progress', 'WAITING FOR COMPANY' => 'pending',
                        'RESOLVED' => 'resolved', 'CLOSED' => 'closed', 'REOPENED' => 'reopened', default => 'open',
                    };
                    $payload = [
                        'requester_id'=>$owner->id, 'requester_type'=>strtolower((string) $owner->role) === 'client' ? 'client' : 'employee',
                        'requester_name'=>$owner->name, 'subject'=>$source->subject, 'description'=>$source->description,
                        'priority'=>strtolower($source->priority ?: 'medium'), 'status'=>$status,
                        'created_at'=>$source->created_at ?? now(), 'updated_at'=>now(),
                    ];
                    if (Schema::connection('tenant')->hasColumn('tickets','company_id')) $payload['company_id']=$company->id;
                    $id = $tenant->table('tickets')->insertGetId($payload);
                    $tenant->table('ticket_activities')->insert([
                        'ticket_id'=>$id, 'user_id'=>$owner->id, 'status'=>$status, 'type'=>'Platform Ticket Import',
                        'content'=>$marker, 'created_at'=>now(), 'updated_at'=>now(),
                    ]);
                    if (Schema::connection('central')->hasTable('complaint_conversations')) {
                        // Platform replies remain in the preserved central archive. Only the staff
                        // requester's own messages are copied into their internal conversation.
                        foreach ($source->conversations()->where('sender_type','company_admin')->where('sender_id',$source->raised_by_id)->get() as $message) {
                            if ($message->message === $source->description) continue;
                            $tenant->table('replies')->insert(['ticket_id'=>$id,'user_id'=>$owner->id,'message'=>$message->message,
                                'created_at'=>$message->created_at ?? now(),'updated_at'=>$message->updated_at ?? now()]);
                        }
                    }
                    if (Schema::connection('central')->hasTable('complaint_attachments')) {
                        $ownMessageIds = Schema::connection('central')->hasTable('complaint_conversations')
                            ? $source->conversations()->where('sender_type','company_admin')->where('sender_id',$source->raised_by_id)->pluck('id')->all() : [];
                        $files = $source->attachments()->where('uploaded_by_type','company_admin')
                            ->where(fn ($query) => $query->whereNull('conversation_id')->orWhereIn('conversation_id',$ownMessageIds))->get();
                        foreach ($files as $file) {
                            if (!str_starts_with((string) $file->file_path,'admin/uploads/complaints/')) continue;
                            $tenant->table('replies')->insert(['ticket_id'=>$id,'user_id'=>$owner->id,
                                'message'=>'Original request attachment: '.$file->original_name,'attachment'=>$file->file_path,
                                'created_at'=>$file->created_at ?? now(),'updated_at'=>$file->updated_at ?? now()]);
                        }
                    }
                    return $id;
                });
                // Preserve the source, its conversations and attachments; remove it from active
                // platform queues only after the internal record is committed successfully.
                $source->update(['related_module'=>'Internal Tickets','related_record_id'=>(string) $ticketId]);
                $source->delete();
                return 1;
            });
        }
        return $moved;
    }
}

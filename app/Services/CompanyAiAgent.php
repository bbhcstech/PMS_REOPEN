<?php

namespace App\Services;

use App\Models\Central\Company;
use Illuminate\Support\Facades\{Auth, DB};

class CompanyAiAgent
{
    public function identity(): array
    {
        $company = $this->company();
        $user = Auth::guard('web')->user();
        $profileName = $user->fresh()?->name ?? $user->name;
        return [
            'id' => 'company:' . $company->id . ':user:' . $user->id,
            'name' => trim((string) $company->name) . ' Assistant · ' . trim((string) $profileName),
        ];
    }
    public function company(): Company
    {
        $user = Auth::guard('web')->user();
        abort_unless($user && $user->company_id && !TenantScope::isPlatformAdmin(), 403);
        $id = TenantScope::companyFilter();
        $company = Company::findOrFail($id);
        abort_unless((int) $user->company_id === (int) $company->id
            && (int) session('current_company_id') === (int) $company->id
            && $company->db_name === DB::connection('tenant')->getDatabaseName(), 403, 'Invalid company workspace.');
        abort_if($user->archived_at || !$user->is_active || !$user->login_allowed || !$user->canLogin(), 403);
        if ($user->company_staff_role_id) {
            $role = \App\Models\CompanyStaffRole::where('company_id', $company->id)->findOrFail($user->company_staff_role_id);
            abort_unless($role->access_role === $user->role, 403, 'Invalid company role.');
        }
        if (!data_get($company->settings, 'ai_agent.id')
            || data_get($company->settings, 'ai_agent.name') !== trim((string) $company->name) . ' Assistant') $company->save();
        return $company;
    }

    public function answer(string $question): array
    {
        $company = $this->company();
        $user = Auth::guard('web')->user();
        $refusal = ['answer' => "I can only help with your company workspace and the information you are allowed to access, and I couldn't find anything for that question.\n\nTry asking about holidays, office timings, your attendance, leave balance, tasks, events, your team, payslips, or how to do something in the workspace.", 'sources' => []];
        if ($this->isGreeting($question)) {
            return ['answer' => $this->capabilities($user), 'sources' => []];
        }
        $history = $this->history();
        $live = app(CompanyAiLiveContext::class);
        $topics = $live->topics($question);
        // Short follow-ups ("and next week?", "what about sick leave?") continue the previous topic.
        if ($history && mb_strlen($question) <= 80) {
            $topics = array_values(array_unique(array_merge($topics, $live->topics(end($history)['question']))));
        }
        $liveRecords = $live->build($company, $user, $topics);
        if ($result = app(CompanyAiWorkspaceAnswers::class)->answer($question, $liveRecords)) {
            $this->remember($question, $result['answer']);
            return $result;
        }
        $records = [];
        foreach (array_merge($liveRecords, app(CompanyAiKnowledge::class)->retrieve($company, $user, $question)) as $record) {
            $records[$record['id']] = $record;
        }
        if (!$records) return $refusal;
        $result = app(GroundedAiResponse::class)->answer($question, array_values($records), $this->identity()['name'], 'company', $history);
        $this->remember($question, $result['answer']);
        return $result;
    }

    /** Clears this tab's follow-up memory (a new assistant page starts a new conversation). */
    public function forgetConversation(): void
    {
        session()->forget($this->historyKey());
    }

    private function historyKey(): string
    {
        return 'company_ai_history.' . md5($this->identity()['id']);
    }

    /** Last few exchanges, kept server-side in this user's session only. */
    private function history(): array
    {
        $history = session($this->historyKey(), []);
        return is_array($history) ? array_slice($history, -4) : [];
    }

    private function remember(string $question, string $answer): void
    {
        $history = $this->history();
        $history[] = ['question' => mb_substr($question, 0, 500), 'answer' => mb_substr($answer, 0, 700)];
        session([$this->historyKey() => array_slice($history, -4)]);
    }

    private function isGreeting(string $question): bool
    {
        $text = trim(mb_strtolower($question), " \t\n\r\0\x0B!?.,");
        return (bool) preg_match('/^(hi+|hello+|hey+|hii+|namaste|namaskar|good (morning|afternoon|evening)|help|thanks?|thank you|who are you|what can you do|what do you do|how can you help( me)?)$/u', $text);
    }

    private function capabilities($user): string
    {
        $items = ['Holidays and weekly offs (today, this week, this month, upcoming)', 'Office timings, late/half-day rules and work-from-home days'];
        if ($user->canViewModule('attendance')) $items[] = 'Your attendance — today, this week and this month';
        if ($user->canViewModule('leaves')) $items[] = 'Your leave balance, leave types, pending requests and the leave policy';
        if ($user->canViewModule('tasks') || $user->canViewModule('projects')) $items[] = 'Your tasks, deadlines and projects';
        if ($user->canViewModule('events')) $items[] = 'Upcoming company events';
        $items[] = 'Your manager, team and department';
        if ($user->canViewModule('my-payslips')) $items[] = 'Your latest payslips';
        $items[] = 'How to do things in the workspace (apply leave, clock in, find a page…)';
        return 'Hi ' . strtok((string) $user->name, ' ') . "! I'm your " . trim((string) $this->company()->name) . " assistant. I can answer from your live workspace data:\n- " . implode("\n- ", $items) . "\n\nTry: \"How many holidays are there this week?\"";
    }
}

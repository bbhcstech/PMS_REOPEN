<?php

namespace App\Http\Controllers;

use App\Exceptions\AiProviderUnavailable;
use App\Services\CompanyAiAgent;
use Illuminate\Http\Request;

class CompanyAiController extends Controller
{
    public function index(Request $request, CompanyAiAgent $agent)
    {
        $identity = $agent->identity();
        $agent->forgetConversation();
        return response()->view('admin.company-ai', [
            'assistantName' => $identity['name'], 'workspaceKey' => $identity['id'],
            'askUrl' => route('company.ai.ask'), 'configured' => filled(config('company_ai.api_key')),
            'supportsLocalAnswers' => true,
            'suggestions' => $this->suggestions($request->user()),
        ])
            ->header('Cache-Control', 'private, no-store');
    }

    public function ask(Request $request, CompanyAiAgent $agent)
    {
        $data = $request->validate(['question' => 'required|string|max:1000', 'company_id' => 'prohibited', 'agent_id' => 'prohibited', 'messages' => 'prohibited']);
        $identity = $agent->identity();
        abort_unless($request->header('X-Company-Workspace') === $identity['id'], 409, 'Your company session changed. Reload the assistant before continuing.');
        try {
            $answer = $agent->answer($data['question']);
        } catch (AiProviderUnavailable $error) {
            return $error->response($this->suggestions($request->user()));
        }

        return response()->json($answer + ['assistant_name' => $identity['name']])->header('Cache-Control', 'private, no-store');
    }

    /** Starter questions, limited to what this user can open. */
    private function suggestions($user): array
    {
        $list = ['How many holidays are there this week?', 'What are the office timings?'];
        if ($user?->canViewModule('leaves')) $list[] = 'What is my leave balance?';
        if ($user?->canViewModule('attendance')) $list[] = 'How many days was I late this month?';
        if ($user?->canViewModule('tasks')) $list[] = 'Which of my tasks are due this week?';
        if ($user?->canViewModule('events')) $list[] = 'Any upcoming company events?';
        $list[] = 'Who is my reporting manager?';
        if ($user?->canViewModule('leaves')) $list[] = 'How do I apply for leave?';
        return array_slice($list, 0, 6);
    }
}

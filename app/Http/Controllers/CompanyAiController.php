<?php

namespace App\Http\Controllers;

use App\Services\CompanyAiAgent;
use Illuminate\Http\Request;

class CompanyAiController extends Controller
{
    public function index(CompanyAiAgent $agent)
    {
        $identity = $agent->identity();
        return response()->view('admin.company-ai', [
            'assistantName' => $identity['name'], 'workspaceKey' => $identity['id'],
            'askUrl' => route('company.ai.ask'), 'configured' => filled(config('company_ai.api_key')),
        ])
            ->header('Cache-Control', 'private, no-store');
    }

    public function ask(Request $request, CompanyAiAgent $agent)
    {
        $data = $request->validate(['question' => 'required|string|max:1000', 'company_id' => 'prohibited', 'agent_id' => 'prohibited', 'messages' => 'prohibited']);
        $identity = $agent->identity();
        abort_unless($request->header('X-Company-Workspace') === $identity['id'], 409, 'Your company session changed. Reload the assistant before continuing.');
        return response()->json($agent->answer($data['question']) + ['assistant_name' => $identity['name']])->header('Cache-Control', 'private, no-store');
    }
}

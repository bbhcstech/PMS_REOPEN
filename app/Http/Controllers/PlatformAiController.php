<?php

namespace App\Http\Controllers;

use App\Services\PlatformAiAgent;
use Illuminate\Http\Request;

class PlatformAiController extends Controller
{
    public function index(PlatformAiAgent $agent)
    {
        $identity = $agent->identity();
        return response()->view('admin.company-ai', [
            'assistantName' => $identity['name'], 'workspaceKey' => $identity['id'],
            'assistantLayout' => 'layouts.superadmin', 'scriptStack' => 'scripts',
            'askUrl' => route('super-admin.ai.ask'), 'configured' => filled(config('company_ai.api_key')),
            'assistantDescription' => 'Ask about platform company registry summaries or your Super Admin profile. Company operational records and other administrators’ profiles remain separate.',
        ])->header('Cache-Control', 'private, no-store');
    }

    public function ask(Request $request, PlatformAiAgent $agent)
    {
        $data = $request->validate(['question' => 'required|string|max:1000', 'company_id' => 'prohibited', 'agent_id' => 'prohibited', 'messages' => 'prohibited']);
        $identity = $agent->identity();
        abort_unless($request->header('X-Company-Workspace') === $identity['id'], 409, 'Your administrator session changed. Reload the assistant.');
        return response()->json($agent->answer($data['question']) + ['assistant_name' => $identity['name']])->header('Cache-Control', 'private, no-store');
    }
}

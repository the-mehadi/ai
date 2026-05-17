<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ai\Agents\ChatAgent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    public function chatting(Request $request)
    {
        $input = $request->input('input');
        $model = $request->input('model', 'gemma3:1b');
        if (!$input) {
            return response()->json(['error' => 'No input provided'], 400);
        }

        if(!Auth::check()){
            Auth::login(User::first());
        }
        // $conversationId = $this->resolveConversation($user);

        return (new ChatAgent())
            ->forUser(Auth::user())
            ->stream($input, model: $model);
    }

    public function getWebsiteContext()
    {
        $response = Http::get('https://qrgen.smartcardgenerator.net/api/packages/filter', [
            'country_name' => 'Bangladesh'
        ]);

        $packages = $response->json();

        return json_encode($packages);
    }

    public function resolveConversation($user)
    {
        return  DB::table('agent_conversations')->where('user_id', $user->id)
                ->latest('updated_at')
                ->value('id');
    }
}

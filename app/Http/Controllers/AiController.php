<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Ai\Agents\ChatAgent;
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
        return (new ChatAgent)->stream($input, model: $model);
    }

    public function getWebsiteContext()
    {
        $response = Http::get('https://qrgen.smartcardgenerator.net/api/packages/filter', [
            'country_name' => 'Bangladesh'
        ]);

        $packages = $response->json();

        return json_encode($packages);
    }
}

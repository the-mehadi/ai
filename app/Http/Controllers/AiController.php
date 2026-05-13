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
        if (!$input) {
            return response()->json(['error' => 'No input provided'], 400);
        }
        return (new ChatAgent)->stream($input);
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

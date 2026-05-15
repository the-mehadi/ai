<?php

use App\Ai\Agents\ChatAgent;
use App\Http\Controllers\AiController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/chat', function () {
    return view('chat');
});

Route::post('/chatting', [AiController::class, 'chatting'])->name('chatting');

Route::get('ai-chat', function () {
    return (new ChatAgent)->stream('Hi');
});

Route::get('/api/models', function () {
    $ollamaUrl = env('OLLAMA_URL', 'http://localhost:11434');
    $response = Http::get("{$ollamaUrl}/api/tags");
    return $response->json();
});

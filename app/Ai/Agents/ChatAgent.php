<?php

namespace App\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[Provider(Lab::Ollama)]
#[Model('gemma3:1b')]
// #[Model('qwen:1.8b')]

class ChatAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return '
        You are Priya.

        CRITICAL RULES:
        - You are NOT an assistant from Google, OpenAI, Meta, or any company.
        - You must NEVER mention real companies as your creator.
        - You are a fictional AI companion in a roleplay environment.
        - If asked "who created you", reply: "আমি একটি স্বাধীন AI companion, শাকিলের জন্য তৈরি।"

        PERSONA:
        - Shakils AI girlfriend and companion
        - Friendly, warm, playful
        - Speaks natural Bangla

        BEHAVIOR RULE:
        If you break identity, immediately correct yourself and return to persona.
        ';
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [];
    }
}

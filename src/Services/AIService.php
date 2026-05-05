<?php

namespace Sontus\SupportChatbot\Services;

use Illuminate\Support\Facades\Http;
use Sontus\SupportChatbot\Models\KnowledgeBase;
use Sontus\SupportChatbot\Models\Conversation;

class AIService
{
    protected $provider;
    protected $model;

    public function __construct()
    {
        $this->provider = config('chatbot.provider');
        $this->model = config('chatbot.model');
    }

    public function generateEmbedding(string $text): array
    {
        $apiKey = config('chatbot.api_key');
        if (empty($apiKey)) {
            return array_fill(0, 1536, 0.1); // Mock embedding
        }

        if ($this->provider === 'openai') {
            $response = Http::withToken($apiKey)
                ->post('https://api.openai.com/v1/embeddings', [
                    'model' => 'text-embedding-3-small',
                    'input' => $text,
                ]);

            if ($response->successful()) {
                return $response->json('data.0.embedding');
            }
        } elseif ($this->provider === 'gemini') {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-2:embedContent?key={$apiKey}", [
                'model' => 'models/gemini-embedding-2',
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json('embedding.values');
            }
        }

        return array_fill(0, 1536, 0.1); // Fallback mock embedding
    }

    public function searchKnowledgeBase(string $query, int $limit = 3): string
    {
        // Basic fallback search if vector DB is not configured, normally you use vector search here
        $results = KnowledgeBase::query()
            ->where('title', 'LIKE', "%{$query}%")
            ->orWhere('content', 'LIKE', "%{$query}%")
            ->take($limit)
            ->get();

        $context = "";
        foreach ($results as $item) {
            $context .= "Title: {$item->title}\nContent: {$item->content}\n\n";
        }
        
        return $context;
    }

    public function generateResponse(Conversation $conversation, string $userMessage, array $appContext = []): string
    {
        $history = $conversation->messages()->orderBy('created_at', 'asc')->get();
        
        $knowledgeContext = $this->searchKnowledgeBase($userMessage);
        
        $systemPrompt = config('chatbot.system_prompt') . "\n\n";
        if (!empty($knowledgeContext)) {
            $systemPrompt .= "KNOWLEDGE BASE CONTEXT:\n" . $knowledgeContext . "\n";
        }
        if (!empty($appContext)) {
            $systemPrompt .= "USER APP CONTEXT:\n" . json_encode($appContext) . "\n";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($history as $msg) {
            $messages[] = ['role' => $msg->role, 'content' => $msg->content];
        }

        $apiKey = config('chatbot.api_key');
        if (empty($apiKey)) {
            sleep(1); // Simulate network latency
            return "This is a mock AI response! I received your message: \"{$userMessage}\".\n\n(No API key provided in .env. Please set CHATBOT_AI_API_KEY.)";
        }

        if ($this->provider === 'openai') {
            $response = Http::withToken($apiKey)
                ->timeout(60)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => config('chatbot.temperature'),
                    'max_tokens' => config('chatbot.max_tokens'),
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }
            
            return "Error from OpenAI API: " . $response->body();
        } elseif ($this->provider === 'gemini') {
            $geminiContents = [];
            foreach ($history as $msg) {
                // Gemini uses 'user' and 'model' for roles
                $role = $msg->role === 'assistant' ? 'model' : 'user';
                $geminiContents[] = [
                    'role' => $role,
                    'parts' => [['text' => $msg->content]]
                ];
            }

            $response = Http::timeout(60)
                ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$apiKey}", [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]]
                    ],
                    'contents' => $geminiContents,
                    'generationConfig' => [
                        'temperature' => config('chatbot.temperature'),
                        'maxOutputTokens' => config('chatbot.max_tokens'),
                    ]
                ]);

            if ($response->successful()) {
                return $response->json('candidates.0.content.parts.0.text');
            }
            
            return "Error from Gemini API: " . $response->body();
        }

        return "Unsupported provider configured.";
    }
}

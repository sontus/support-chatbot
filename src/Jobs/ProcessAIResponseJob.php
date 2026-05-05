<?php

namespace Sontus\SupportChatbot\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Sontus\SupportChatbot\Models\Conversation;
use Sontus\SupportChatbot\Services\AIService;
use Sontus\SupportChatbot\Services\ConversationService;
use Sontus\SupportChatbot\Services\ContextResolver;

class ProcessAIResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $conversation;
    public $userMessage;
    public $userId;

    public function __construct(Conversation $conversation, string $userMessage, $userId = null)
    {
        $this->conversation = $conversation;
        $this->userMessage = $userMessage;
        $this->userId = $userId;
    }

    public function handle(
        AIService $aiService, 
        ConversationService $convService,
        ContextResolver $contextResolver
    ) {
        // App\Models\User is assumed for default auth
        $userClass = config('auth.providers.users.model', \App\Models\User::class);
        $user = $this->userId ? $userClass::find($this->userId) : null;
        $appContext = $contextResolver->resolveForUser($user);

        $aiResponse = $aiService->generateResponse($this->conversation, $this->userMessage, $appContext);

        $convService->addMessage($this->conversation, 'assistant', $aiResponse);
        
        // Optionally trigger event for WebSockets
        // event(new \Sontus\SupportChatbot\Events\MessageReceived($this->conversation->id, $aiResponse));
    }
}

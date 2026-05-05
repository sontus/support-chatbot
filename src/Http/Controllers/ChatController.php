<?php

namespace Sontus\SupportChatbot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Sontus\SupportChatbot\Services\ConversationService;
use Sontus\SupportChatbot\Jobs\ProcessAIResponseJob;

class ChatController extends Controller
{
    protected $convService;

    public function __construct(ConversationService $convService)
    {
        $this->convService = $convService;
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'session_id' => 'required_without:user_id|string',
        ]);

        $userId = auth()->id() ?? $request->input('user_id');
        $sessionId = $request->input('session_id');

        $conversation = $this->convService->getOrCreateConversation($userId, $sessionId);

        // Save user message
        $this->convService->addMessage($conversation, 'user', $request->input('message'));

        // Dispatch job for AI response
        ProcessAIResponseJob::dispatch($conversation, $request->input('message'), $userId);

        return response()->json([
            'status' => 'queued',
            'conversation_id' => $conversation->id,
            'message' => 'Your message is being processed.'
        ]);
    }

    public function getHistory(Request $request)
    {
        $userId = auth()->id() ?? $request->input('user_id');
        $sessionId = $request->input('session_id');

        $conversation = $this->convService->getOrCreateConversation($userId, $sessionId);

        return response()->json([
            'messages' => $conversation->messages()->orderBy('created_at', 'asc')->get()
        ]);
    }
}

<?php

namespace Sontus\SupportChatbot\Services;

use Sontus\SupportChatbot\Models\Conversation;
use Sontus\SupportChatbot\Models\Message;

class ConversationService
{
    public function getOrCreateConversation($userId = null, $sessionId = null): Conversation
    {
        if ($userId) {
            return Conversation::firstOrCreate(
                ['user_id' => $userId, 'status' => 'active']
            );
        }

        return Conversation::firstOrCreate(
            ['session_id' => $sessionId, 'status' => 'active']
        );
    }

    public function addMessage(Conversation $conversation, string $role, string $content): Message
    {
        return $conversation->messages()->create([
            'role' => $role,
            'content' => $content,
        ]);
    }
}

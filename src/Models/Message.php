<?php

namespace Sontus\SupportChatbot\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'chatbot_messages';
    protected $fillable = ['conversation_id', 'role', 'content'];

    public function getConnectionName()
    {
        return config('chatbot.database_connection');
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}

<?php

namespace Sontus\SupportChatbot\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table = 'chatbot_conversations';
    protected $fillable = ['user_id', 'session_id', 'status'];

    public function getConnectionName()
    {
        return config('chatbot.database_connection');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}

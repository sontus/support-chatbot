<?php

namespace Sontus\SupportChatbot\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $table = 'chatbot_knowledge_base';
    protected $fillable = ['title', 'content', 'embedding'];
    
    protected $casts = [
        'embedding' => 'array', 
    ];

    public function getConnectionName()
    {
        return config('chatbot.database_connection');
    }
}

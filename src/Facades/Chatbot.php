<?php

namespace Sontus\SupportChatbot\Facades;

use Illuminate\Support\Facades\Facade;

class Chatbot extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'chatbot';
    }
}

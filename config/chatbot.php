<?php

return [
    'api_key' => env('CHATBOT_AI_API_KEY', ''),
    'provider' => env('CHATBOT_AI_PROVIDER', 'openai'),
    'model' => env('CHATBOT_AI_MODEL', 'gpt-4o-mini'),
    'temperature' => (float) env('CHATBOT_AI_TEMPERATURE', 0.7),
    'max_tokens' => (int) env('CHATBOT_AI_MAX_TOKENS', 1000),
    'system_prompt' => env(
        'CHATBOT_SYSTEM_PROMPT',
        "You are a helpful customer support assistant. Answer based on provided knowledge base and application data. If unsure, ask for clarification."
    ),
    'database_connection' => env('CHATBOT_DB_CONNECTION', env('DB_CONNECTION', 'mysql')),
    'middleware' => ['web', 'auth'],
    'api_middleware' => ['api'],
    'admin_middleware' => ['web', 'auth'], // Add role middleware here as needed
];

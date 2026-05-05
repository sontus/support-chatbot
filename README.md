# Laravel Support Chatbot

A production-ready, AI-powered support chatbot package for Laravel applications. This package seamlessly integrates an intelligent, context-aware chatbot into your application. It uses direct HTTP integrations without heavy dependencies.

## Features

- **AI Integration**: Directly supports OpenAI and Gemini models without requiring PHP 8.4 dependencies.
- **Knowledge Base (RAG)**: Admin panel to add FAQs and documentation, searchable via AI embeddings.
- **Context Awareness**: The chatbot automatically understands the logged-in user, their orders, and tickets.
- **Frontend Widget**: A sleek, customizable, floating Vanilla JS widget for real-time chat. Drop it into any site!
- **Queue System**: Messages are processed asynchronously to maintain high performance.
- **Multi-channel API**: Interact via frontend widget or via exposed REST APIs.
- **Multi-Tenancy Ready**: Configurable database connection for tenant-specific deployments.

## Installation

1. **Install via Composer:**

```bash
composer require sontus/laravel-support-chatbot
```

2. **Run the installation command:**
```bash
php artisan chatbot:install
```

3. **Run Migrations:**
```bash
php artisan migrate
```

4. **Configure Environment:**
Add the following to your `.env` file:
```env
CHATBOT_AI_API_KEY="your_api_key_here"

# For Gemini (Default)
CHATBOT_AI_PROVIDER=gemini
CHATBOT_AI_MODEL=gemini-2.0-flash

# For OpenAI
# CHATBOT_AI_PROVIDER=openai
# CHATBOT_AI_MODEL=gpt-4o-mini
```

5. **Start the Queue Worker:**
Because the chatbot processes AI requests asynchronously to ensure high performance and avoid blocking your app, you need to run a queue worker:
```bash
php artisan queue:work
```

## Usage

### 1. Frontend Chat Widget

The frontend widget is pure Vanilla JavaScript and includes its own styling. It works on **any** Laravel site without needing a Node/Vue compilation step.

Simply add the script tag before the closing `</body>` tag in your layout (e.g. `resources/views/layouts/app.blade.php`):

```html
<script src="{{ asset('vendor/chatbot/chatbot-widget.js') }}"></script>
```

Add a meta tag for CSRF and optionally User ID if authenticated (in your `<head>`):
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
@auth
    <meta name="user-id" content="{{ auth()->id() }}">
@endauth
```

### 2. Admin Panel

To train the chatbot and view conversations, visit the admin dashboard:
**URL:** `/admin/chatbot`
Ensure your admin users pass the configured middleware (`web`, `auth`).

### 3. Customizing Context

You can bind your own `ContextResolver` in your application's `AppServiceProvider` if you want to provide custom data (like specific tenant data) to the AI:

```php
use Sontus\SupportChatbot\Services\ContextResolver;

$this->app->bind(ContextResolver::class, function ($app) {
    return new class extends ContextResolver {
        public function resolveForUser($user = null): array
        {
            // Return any array of data you want the AI to know about this user
            return [
                'name' => $user->name,
                'subscription_plan' => $user->plan->name,
            ];
        }
    };
});
```

### 4. API Endpoints

- `POST /api/chatbot/message` (Requires `message`, and `session_id` or `user_id`)
- `GET /api/chatbot/history` (Requires `session_id` or `user_id`)

## License
MIT

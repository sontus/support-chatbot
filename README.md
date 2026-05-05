# Laravel Support Chatbot

A production-ready, AI-powered support chatbot package for Laravel applications. This package seamlessly integrates an intelligent, context-aware chatbot into your application using the Laravel AI SDK.

## Features

- **AI Integration**: Powered by Laravel AI SDK (supports OpenAI, Claude, Gemini).
- **Knowledge Base (RAG)**: Admin panel to add FAQs and documentation, searchable via AI embeddings.
- **Context Awareness**: The chatbot automatically understands the logged-in user, their orders, and tickets.
- **Frontend Widget**: A sleek, customizable, floating Vanilla JS widget for real-time chat. Drop it into any site!
- **Queue System**: Messages are processed asynchronously to maintain high performance.
- **Multi-channel API**: Interact via frontend widget or via exposed REST APIs.
- **Multi-Tenancy Ready**: Configurable database connection for tenant-specific deployments.

## Installation

1. **Install via Composer:**

If this package is published to packagist:
```bash
composer require sontus/laravel-support-chatbot
```

**For Local Development / Testing:**
Add the package path to your main Laravel `composer.json` repositories section:
```json
"repositories": [
    {
        "type": "path",
        "url": "packages/laravel-support-chatbot"
    }
]
```
Then require it using the `@dev` tag:
```bash
composer require sontus/laravel-support-chatbot *@dev
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
CHATBOT_AI_PROVIDER=openai
CHATBOT_AI_MODEL=gpt-4o-mini
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

<?php

namespace Sontus\SupportChatbot;

use Illuminate\Support\ServiceProvider;

class ChatbotServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/chatbot.php', 'chatbot');

        $this->app->singleton('chatbot', function ($app) {
            return new \Sontus\SupportChatbot\Services\AIService();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/chatbot.php' => config_path('chatbot.php'),
            ], 'chatbot-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'chatbot-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/chatbot'),
                __DIR__ . '/../resources/js/chatbot-widget.js' => public_path('vendor/chatbot/chatbot-widget.js'),
            ], 'chatbot-assets');
            
            $this->commands([
                \Sontus\SupportChatbot\Console\InstallCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'chatbot');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}

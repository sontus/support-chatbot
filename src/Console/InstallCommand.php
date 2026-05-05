<?php

namespace Sontus\SupportChatbot\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'chatbot:install';
    protected $description = 'Install the Laravel Support Chatbot package';

    public function handle()
    {
        $this->info('Installing Support Chatbot Package...');

        $this->call('vendor:publish', [
            '--tag' => 'chatbot-config'
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'chatbot-migrations'
        ]);
        
        $this->call('vendor:publish', [
            '--tag' => 'chatbot-assets'
        ]);

        $this->info('Installed successfully! Run `php artisan migrate` to create the tables.');
    }
}

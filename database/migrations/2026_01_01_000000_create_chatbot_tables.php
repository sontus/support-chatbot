<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $connection = config('chatbot.database_connection');

        Schema::connection($connection)->create('chatbot_knowledge_base', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            // Adding vector column for embeddings. Make sure pgvector or equivalent is enabled.
            // In a real package, fallback to longText or json if vector type is not supported.
            $table->json('embedding')->nullable(); 
            $table->timestamps();
        });

        Schema::connection($connection)->create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->string('status')->default('active'); // active, closed
            $table->timestamps();
            
            // Assuming users table exists
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::connection($connection)->create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->text('content');
            $table->timestamps();
            
            $table->foreign('conversation_id')->references('id')->on('chatbot_conversations')->cascadeOnDelete();
        });
    }

    public function down()
    {
        $connection = config('chatbot.database_connection');
        Schema::connection($connection)->dropIfExists('chatbot_messages');
        Schema::connection($connection)->dropIfExists('chatbot_conversations');
        Schema::connection($connection)->dropIfExists('chatbot_knowledge_base');
    }
};

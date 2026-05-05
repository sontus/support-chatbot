<?php

use Illuminate\Support\Facades\Route;
use Sontus\SupportChatbot\Http\Controllers\ChatController;

Route::prefix('api/chatbot')->middleware(config('chatbot.api_middleware'))->group(function () {
    Route::post('/message', [ChatController::class, 'sendMessage']);
    Route::get('/history', [ChatController::class, 'getHistory']);
});

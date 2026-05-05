<?php

use Illuminate\Support\Facades\Route;
use Sontus\SupportChatbot\Http\Controllers\AdminController;

Route::prefix('admin/chatbot')->middleware(config('chatbot.admin_middleware'))->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('chatbot.admin.index');
    Route::post('/train', [AdminController::class, 'train'])->name('chatbot.admin.train');
    Route::post('/sync-embeddings', [AdminController::class, 'syncEmbeddings'])->name('chatbot.admin.sync');
});

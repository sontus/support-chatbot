<?php

namespace Sontus\SupportChatbot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Sontus\SupportChatbot\Services\TrainingService;
use Sontus\SupportChatbot\Models\KnowledgeBase;
use Sontus\SupportChatbot\Models\Conversation;

class AdminController extends Controller
{
    protected $trainingService;

    public function __construct(TrainingService $trainingService)
    {
        $this->trainingService = $trainingService;
    }

    public function index()
    {
        $conversations = Conversation::withCount('messages')->latest()->paginate(20);
        $knowledge = KnowledgeBase::latest()->paginate(20);

        return view('chatbot::admin.index', compact('conversations', 'knowledge'));
    }

    public function train(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $this->trainingService->train($request->input('title'), $request->input('content'));

        return back()->with('success', 'Knowledge base updated and trained.');
    }
    
    public function syncEmbeddings()
    {
        $this->trainingService->syncAll();
        return back()->with('success', 'Embeddings synced.');
    }
}

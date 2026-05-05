<?php

namespace Sontus\SupportChatbot\Services;

use Sontus\SupportChatbot\Models\KnowledgeBase;

class TrainingService
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function train(string $title, string $content): KnowledgeBase
    {
        $embedding = $this->aiService->generateEmbedding($content);

        return KnowledgeBase::create([
            'title' => $title,
            'content' => $content,
            'embedding' => $embedding,
        ]);
    }
    
    public function syncAll()
    {
        KnowledgeBase::whereNull('embedding')->chunk(100, function ($items) {
            foreach ($items as $item) {
                $item->embedding = $this->aiService->generateEmbedding($item->content);
                $item->save();
            }
        });
    }
}

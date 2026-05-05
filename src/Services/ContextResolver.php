<?php

namespace Sontus\SupportChatbot\Services;

class ContextResolver
{
    public function resolveForUser($user = null): array
    {
        if (!$user) {
            return [];
        }

        $context = [
            'user_name' => $user->name ?? 'Guest',
            'user_email' => $user->email ?? 'N/A',
        ];

        // Example relationships
        if (method_exists($user, 'orders')) {
            $context['recent_orders'] = $user->orders()->latest()->take(3)->get()->toArray();
        }

        if (method_exists($user, 'tickets')) {
            $context['recent_tickets'] = $user->tickets()->latest()->take(3)->get()->toArray();
        }

        return $context;
    }
}

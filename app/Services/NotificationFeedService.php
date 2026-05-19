<?php

namespace App\Services;

use App\Models\User;

class NotificationFeedService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function forUser(User $user, int $limit = 20): array
    {
        return $user->notifications()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($notification) => $this->map($notification))
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function map(object $notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->data['title'] ?? '',
            'body' => $notification->data['message'] ?? '',
            'icon' => $notification->data['icon'] ?? 'notifications',
            'created_at' => $notification->created_at->toISOString(),
            'read_at' => $notification->read_at?->toISOString(),
            'url' => $notification->data['url'] ?? null,
        ];
    }
}

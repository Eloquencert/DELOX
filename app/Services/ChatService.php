<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Chat;
use App\Repositories\Contracts\ChatRepositoryInterface;
use RuntimeException;

class ChatService
{
    public function __construct(
        private readonly ChatRepositoryInterface $chats,
    ) {}

    public function getUserChats(int $userId): array
    {
        return $this->chats->findByUser($userId);
    }

    public function findById(int $chatId, int $viewerId): ?Chat
    {
        return $this->chats->findById($chatId, $viewerId);
    }

    public function createPrivateChat(int $userId, int $otherUserId): Chat
    {
        if ($userId === $otherUserId) {
            throw new RuntimeException('You cannot start a chat with yourself.');
        }

        $existing = $this->chats->findPrivateBetween($userId, $otherUserId);

        if ($existing !== null) {
            return $existing;
        }

        $chatId = $this->chats->create([
            'type'       => 'private',
            'created_by' => $userId,
        ]);

        $this->chats->addMember($chatId, $userId, 'admin');
        $this->chats->addMember($chatId, $otherUserId, 'member');

        return $this->chats->findById($chatId, $userId);
    }

    public function isMember(int $chatId, int $userId): bool
    {
        return $this->chats->isMember($chatId, $userId);
    }
}

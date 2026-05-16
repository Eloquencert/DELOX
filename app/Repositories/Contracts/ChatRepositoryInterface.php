<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Chat;

interface ChatRepositoryInterface
{
    /** Find a chat by ID, enriched with context for $viewerId. */
    public function findById(int $id, int $viewerId): ?Chat;

    /** All chats the user belongs to, sorted by last activity. */
    public function findByUser(int $userId): array;

    /** Find an existing private chat between two users, or null. */
    public function findPrivateBetween(int $userId1, int $userId2): ?Chat;

    public function create(array $data): int;
    public function addMember(int $chatId, int $userId, string $role = 'member'): void;
    public function isMember(int $chatId, int $userId): bool;
}

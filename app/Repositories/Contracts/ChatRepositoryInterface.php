<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Chat;

interface ChatRepositoryInterface
{
    public function findById(int $id, int $viewerId): ?Chat;

    public function findByUser(int $userId): array;

    public function findPrivateBetween(int $userId1, int $userId2): ?Chat;

    public function create(array $data): int;
    public function addMember(int $chatId, int $userId, string $role = 'member'): void;
    public function isMember(int $chatId, int $userId): bool;
}

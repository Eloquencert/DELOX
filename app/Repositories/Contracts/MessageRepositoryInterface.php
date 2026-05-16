<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Message;

interface MessageRepositoryInterface
{
    /**
     * Returns messages for a chat newer than $after, ordered oldest-first.
     *
     * @return Message[]
     */
    public function findByChatId(int $chatId, int $after = 0, int $limit = 50): array;

    public function create(int $chatId, int $senderId, string $content, string $type = 'text'): Message;
}

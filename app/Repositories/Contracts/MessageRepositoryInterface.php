<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Message;

interface MessageRepositoryInterface
{

    public function findByChatId(int $chatId, int $after = 0, int $limit = 50): array;

    public function create(int $chatId, int $senderId, string $content, string $type = 'text'): Message;
}

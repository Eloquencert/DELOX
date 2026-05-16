<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Message;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use RuntimeException;

class MessageService
{
    private const MAX_LENGTH = 4096;

    public function __construct(
        private readonly MessageRepositoryInterface $messages,
        private readonly ChatRepositoryInterface    $chats,
    ) {}

    /** @return Message[] */
    public function getMessages(int $chatId, int $after = 0, int $limit = 50): array
    {
        return $this->messages->findByChatId($chatId, $after, $limit);
    }

    /**
     * Validate access and content, then persist and return the new message.
     *
     * @throws RuntimeException on access violation or empty/too-long content
     */
    public function send(int $chatId, int $senderId, string $content): Message
    {
        if (!$this->chats->isMember($chatId, $senderId)) {
            throw new RuntimeException('You are not a member of this chat.');
        }

        $content = trim($content);

        if ($content === '') {
            throw new RuntimeException('Message cannot be empty.');
        }

        if (mb_strlen($content) > self::MAX_LENGTH) {
            throw new RuntimeException('Message is too long (max ' . self::MAX_LENGTH . ' characters).');
        }

        return $this->messages->create($chatId, $senderId, $content);
    }
}

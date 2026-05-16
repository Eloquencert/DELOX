<?php

declare(strict_types=1);

namespace App\Models;

class Message
{
    public function __construct(
        public readonly int    $id,
        public readonly int    $chatId,
        public readonly int    $senderId,
        public readonly string $type,
        public readonly string $content,
        public readonly string $createdAt,
        public readonly ?string $senderUsername    = null,
        public readonly ?string $senderDisplayName = null,
        public readonly ?string $senderAvatar      = null,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:                (int) $row['id'],
            chatId:            (int) $row['chat_id'],
            senderId:          (int) $row['sender_id'],
            type:              $row['type'],
            content:           $row['content'],
            createdAt:         $row['created_at'],
            senderUsername:    $row['sender_username'] ?? null,
            senderDisplayName: $row['sender_display_name'] ?? null,
            senderAvatar:      $row['sender_avatar'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'chat_id'    => $this->chatId,
            'sender_id'  => $this->senderId,
            'type'       => $this->type,
            'content'    => $this->content,
            'created_at' => $this->createdAt,
            'time'       => date('H:i', strtotime($this->createdAt)),
            'sender'     => [
                'id'           => $this->senderId,
                'username'     => $this->senderUsername,
                'display_name' => $this->senderDisplayName,
                'avatar'       => $this->senderAvatar,
            ],
        ];
    }
}

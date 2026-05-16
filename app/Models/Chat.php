<?php

declare(strict_types=1);

namespace App\Models;

class Chat
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $type,
        public readonly ?string $name,
        public readonly ?string $avatar,
        public readonly int     $createdBy,
        public readonly string  $createdAt,
        public readonly ?string $lastMessage     = null,
        public readonly ?string $lastMessageAt   = null,
        public readonly ?string $otherUsername   = null,
        public readonly ?string $otherDisplayName = null,
        public readonly ?string $otherAvatar     = null,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:               (int) $row['id'],
            type:             $row['type'],
            name:             $row['name'] ?? null,
            avatar:           $row['avatar'] ?? null,
            createdBy:        (int) $row['created_by'],
            createdAt:        $row['created_at'],
            lastMessage:      $row['last_message'] ?? null,
            lastMessageAt:    $row['last_message_at'] ?? null,
            otherUsername:    $row['other_username'] ?? null,
            otherDisplayName: $row['other_display_name'] ?? null,
            otherAvatar:      $row['other_avatar'] ?? null,
        );
    }

    public function displayName(): string
    {
        return $this->type === 'private'
            ? ($this->otherDisplayName ?? 'Unknown User')
            : ($this->name ?? 'Group Chat');
    }

    public function avatarUrl(): ?string
    {
        $file = $this->type === 'private' ? $this->otherAvatar : $this->avatar;
        return $file ? '/DELOX/storage/uploads/avatars/' . $file : null;
    }

    public function initial(): string
    {
        return mb_strtoupper(mb_substr($this->displayName(), 0, 1));
    }
}

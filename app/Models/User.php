<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $username,
        public readonly string  $email,
        public readonly string  $displayName,
        public readonly ?string $avatar,
        public readonly ?string $bio,
        public readonly ?string $lastSeenAt,
        public readonly string  $createdAt,
    ) {}

    public static function fromArray(array $row): self
    {
        return new self(
            id:          (int) $row['id'],
            username:    $row['username'],
            email:       $row['email'],
            displayName: $row['display_name'],
            avatar:      $row['avatar'],
            bio:         $row['bio'],
            lastSeenAt:  $row['last_seen_at'],
            createdAt:   $row['created_at'],
        );
    }

    public function avatarUrl(): string
    {
        return $this->avatar
            ? '/DELOX/storage/uploads/avatars/' . $this->avatar
            : '/DELOX/public/assets/default-avatar.png';
    }
}

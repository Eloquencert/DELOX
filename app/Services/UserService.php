<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use RuntimeException;

class UserService
{
    private const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private const MAX_AVATAR_SIZE    = 2 * 1024 * 1024; // 2 MB

    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function updateProfile(int $userId, string $displayName, string $bio): User
    {
        $this->users->update($userId, [
            'display_name' => $displayName,
            'bio'          => $bio,
        ]);

        // Keep session in sync so the nav bar reflects changes immediately
        $_SESSION['display_name'] = $displayName;

        return $this->users->findById($userId);
    }

    public function uploadAvatar(int $userId, array $file): User
    {
        $this->validateAvatarFile($file);

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('avatar_', true) . '.' . $ext;
        $dest     = STORAGE_PATH . '/uploads/avatars/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Failed to save the uploaded file.');
        }

        $this->users->update($userId, ['avatar' => $filename]);

        // Keep session in sync
        $_SESSION['avatar'] = $filename;

        return $this->users->findById($userId);
    }

    private function validateAvatarFile(array $file): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed. Please try again.');
        }

        $info = getimagesize($file['tmp_name']);
        $mime = $info ? $info['mime'] : '';

        if (!in_array($mime, self::ALLOWED_MIME_TYPES, true)) {
            throw new RuntimeException('Only JPEG, PNG, GIF, and WebP images are allowed.');
        }

        if ($file['size'] > self::MAX_AVATAR_SIZE) {
            throw new RuntimeException('Avatar must be smaller than 2 MB.');
        }
    }
}

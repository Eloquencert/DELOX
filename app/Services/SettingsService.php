<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\Contracts\UserRepositoryInterface;
use RuntimeException;

class SettingsService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}
    public function changeEmail(int $userId, string $newEmail, string $password): void
    {
        $this->verifyPassword($userId, $password);

        if ($this->users->emailExists($newEmail)) {
            throw new RuntimeException('This email address is already registered.');
        }

        $this->users->updateEmail($userId, $newEmail);
    }

    /**
     * Change the user's password after verifying the current one.
     *
     * @throws RuntimeException on wrong current password
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): void
    {
        $this->verifyPassword($userId, $currentPassword);

        $this->users->updatePasswordHash(
            $userId,
            password_hash($newPassword, PASSWORD_BCRYPT)
        );
    }

    /**
     * Permanently delete the account after password confirmation.
     *
     * @throws RuntimeException on wrong password
     */
    public function deleteAccount(int $userId, string $password): void
    {
        $this->verifyPassword($userId, $password);
        $this->users->delete($userId);
    }

    /** @throws RuntimeException when password does not match */
    private function verifyPassword(int $userId, string $password): void
    {
        $raw = $this->users->findRawById($userId);

        if ($raw === null || !password_verify($password, $raw['password_hash'])) {
            throw new RuntimeException('Incorrect password.');
        }
    }
}

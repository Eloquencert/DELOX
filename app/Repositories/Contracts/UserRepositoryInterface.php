<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByUsername(string $username): ?User;
    public function emailExists(string $email): bool;
    public function usernameExists(string $username): bool;

    /** Returns raw row including password_hash — for auth verification only. */
    public function findCredentialsByEmail(string $email): ?array;

    public function create(array $data): int;
    public function updateLastSeen(int $id): void;
}

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

    public function findCredentialsByEmail(string $email): ?array;

    public function findRawById(int $id): ?array;

    public function create(array $data): int;
    public function update(int $id, array $data): void;
    public function updateLastSeen(int $id): void;

    public function updateEmail(int $id, string $email): void;
    public function updatePasswordHash(int $id, string $hash): void;
    public function delete(int $id): void;

    /** Search users by username or display_name, excluding $excludeId. */
    public function search(string $query, int $excludeId, int $limit = 15): array;
}

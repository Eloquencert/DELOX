<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();

        return $row ? User::fromArray($row) : null;
    }

    public function findCredentialsByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;
    }

    public function findRawById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetch();
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM users WHERE username = :username');
        $stmt->execute(['username' => $username]);

        return (bool) $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash, display_name)
             VALUES (:username, :email, :password_hash, :display_name)'
        );

        $stmt->execute([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'display_name'  => $data['display_name'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $allowed = ['display_name', 'bio', 'avatar'];
        $data    = array_intersect_key($data, array_flip($allowed));

        if (empty($data)) {
            return;
        }

        $setClauses = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $data['id'] = $id;

        $stmt = $this->db->prepare("UPDATE users SET {$setClauses} WHERE id = :id");
        $stmt->execute($data);
    }

    public function updateEmail(int $id, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE users SET email = :email WHERE id = :id');
        $stmt->execute(['email' => $email, 'id' => $id]);
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => $hash, 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function search(string $query, int $excludeId, int $limit = 15): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users
             WHERE id != :excludeId
               AND (username LIKE :q1 OR display_name LIKE :q2)
             ORDER BY display_name
             LIMIT :limit'
        );
        $stmt->bindValue('excludeId', $excludeId, PDO::PARAM_INT);
        $stmt->bindValue('q1', '%' . $query . '%');
        $stmt->bindValue('q2', '%' . $query . '%');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map([User::class, 'fromArray'], $stmt->fetchAll());
    }

    public function updateLastSeen(int $id): void
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET last_seen_at = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Chat;
use App\Repositories\Contracts\ChatRepositoryInterface;
use PDO;

class ChatRepository implements ChatRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findById(int $id, int $viewerId): ?Chat
    {
        $stmt = $this->db->prepare(
            'SELECT c.*,
                    u2.username       AS other_username,
                    u2.display_name   AS other_display_name,
                    u2.avatar         AS other_avatar
             FROM chats c
             LEFT JOIN chat_members cm2
                    ON cm2.chat_id = c.id
                   AND cm2.user_id != :viewerId
                   AND c.type = \'private\'
             LEFT JOIN users u2 ON u2.id = cm2.user_id
             WHERE c.id = :id'
        );
        $stmt->execute(['id' => $id, 'viewerId' => $viewerId]);
        $row = $stmt->fetch();

        return $row ? Chat::fromArray($row) : null;
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.*,
                    last_msg.content    AS last_message,
                    last_msg.created_at AS last_message_at,
                    u2.username         AS other_username,
                    u2.display_name     AS other_display_name,
                    u2.avatar           AS other_avatar
             FROM chats c
             INNER JOIN chat_members cm ON cm.chat_id = c.id AND cm.user_id = :userId
             LEFT JOIN messages last_msg
                    ON last_msg.id = (
                        SELECT id FROM messages
                        WHERE chat_id = c.id
                        ORDER BY created_at DESC
                        LIMIT 1
                    )
             LEFT JOIN chat_members cm2
                    ON cm2.chat_id = c.id
                   AND cm2.user_id != :userId2
                   AND c.type = \'private\'
             LEFT JOIN users u2 ON u2.id = cm2.user_id
             ORDER BY COALESCE(last_msg.created_at, c.created_at) DESC'
        );
        $stmt->execute(['userId' => $userId, 'userId2' => $userId]);

        return array_map([Chat::class, 'fromArray'], $stmt->fetchAll());
    }

    public function findPrivateBetween(int $userId1, int $userId2): ?Chat
    {
        $stmt = $this->db->prepare(
            'SELECT c.*,
                    u2.username       AS other_username,
                    u2.display_name   AS other_display_name,
                    u2.avatar         AS other_avatar
             FROM chats c
             INNER JOIN chat_members cm1 ON cm1.chat_id = c.id AND cm1.user_id = :u1
             INNER JOIN chat_members cm2 ON cm2.chat_id = c.id AND cm2.user_id = :u2
             LEFT JOIN users u2 ON u2.id = :u2b
             WHERE c.type = \'private\'
             LIMIT 1'
        );
        $stmt->execute(['u1' => $userId1, 'u2' => $userId2, 'u2b' => $userId2]);
        $row = $stmt->fetch();

        return $row ? Chat::fromArray($row) : null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO chats (type, name, avatar, created_by)
             VALUES (:type, :name, :avatar, :created_by)'
        );
        $stmt->execute([
            'type'       => $data['type'],
            'name'       => $data['name'] ?? null,
            'avatar'     => $data['avatar'] ?? null,
            'created_by' => $data['created_by'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function addMember(int $chatId, int $userId, string $role = 'member'): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO chat_members (chat_id, user_id, role)
             VALUES (:chat_id, :user_id, :role)'
        );
        $stmt->execute(['chat_id' => $chatId, 'user_id' => $userId, 'role' => $role]);
    }

    public function isMember(int $chatId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM chat_members WHERE chat_id = :chat_id AND user_id = :user_id'
        );
        $stmt->execute(['chat_id' => $chatId, 'user_id' => $userId]);

        return (bool) $stmt->fetch();
    }
}

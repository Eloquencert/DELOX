<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Message;
use App\Repositories\Contracts\MessageRepositoryInterface;
use PDO;

class MessageRepository implements MessageRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByChatId(int $chatId, int $after = 0, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            'SELECT m.*,
                    u.username     AS sender_username,
                    u.display_name AS sender_display_name,
                    u.avatar       AS sender_avatar
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.chat_id = :chatId
               AND m.id > :after
             ORDER BY m.created_at ASC
             LIMIT :limit'
        );
        $stmt->bindValue('chatId', $chatId, PDO::PARAM_INT);
        $stmt->bindValue('after',  $after,  PDO::PARAM_INT);
        $stmt->bindValue('limit',  $limit,  PDO::PARAM_INT);
        $stmt->execute();

        return array_map([Message::class, 'fromArray'], $stmt->fetchAll());
    }

    public function create(int $chatId, int $senderId, string $content, string $type = 'text'): Message
    {
        $stmt = $this->db->prepare(
            'INSERT INTO messages (chat_id, sender_id, type, content)
             VALUES (:chat_id, :sender_id, :type, :content)'
        );
        $stmt->execute([
            'chat_id'   => $chatId,
            'sender_id' => $senderId,
            'type'      => $type,
            'content'   => $content,
        ]);

        return $this->findById((int) $this->db->lastInsertId());
    }

    private function findById(int $id): Message
    {
        $stmt = $this->db->prepare(
            'SELECT m.*,
                    u.username     AS sender_username,
                    u.display_name AS sender_display_name,
                    u.avatar       AS sender_avatar
             FROM messages m
             JOIN users u ON u.id = m.sender_id
             WHERE m.id = :id'
        );
        $stmt->execute(['id' => $id]);

        return Message::fromArray($stmt->fetch());
    }
}

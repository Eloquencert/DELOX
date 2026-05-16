<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Repositories\ChatRepository;
use App\Repositories\MessageRepository;
use App\Services\ChatService;
use App\Services\MessageService;
use RuntimeException;

class MessageController extends Controller
{
    private MessageService $messageService;
    private ChatService    $chatService;

    public function __construct(
        \App\Core\Request  $request,
        \App\Core\Response $response,
    ) {
        parent::__construct($request, $response);
        $chatRepo             = new ChatRepository();
        $this->chatService    = new ChatService($chatRepo);
        $this->messageService = new MessageService(new MessageRepository(), $chatRepo);
    }

    /** GET /api/chats/:chatId/messages?after=0 */
    public function index(string $chatId): void
    {
        AuthMiddleware::handle();

        $userId = (int) $_SESSION['user_id'];
        $chatId = (int) $chatId;
        $after  = (int) $this->request->get('after', 0);
        $limit  = min((int) $this->request->get('limit', 50), 100);

        if (!$this->chatService->isMember($chatId, $userId)) {
            $this->json(['error' => 'Access denied.'], 403);
        }

        $messages = $this->messageService->getMessages($chatId, $after, $limit);

        $this->json([
            'messages' => array_map(fn($m) => $m->toArray(), $messages),
        ]);
    }

    /** POST /api/chats/:chatId/messages  body: { "content": "..." } */
    public function store(string $chatId): void
    {
        AuthMiddleware::handle();

        $userId  = (int) $_SESSION['user_id'];
        $chatId  = (int) $chatId;
        $payload = $this->request->json();
        $content = $payload['content'] ?? '';

        try {
            $message = $this->messageService->send($chatId, $userId, $content);
            $this->json(['message' => $message->toArray()], 201);
        } catch (RuntimeException $e) {
            $this->json(['error' => $e->getMessage()], 400);
        }
    }
}

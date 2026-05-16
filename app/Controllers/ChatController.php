<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Repositories\ChatRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\ChatService;
use RuntimeException;

class ChatController extends Controller
{
    private AuthService $authService;
    private ChatService $chatService;

    public function __construct(
        \App\Core\Request  $request,
        \App\Core\Response $response,
    ) {
        parent::__construct($request, $response);
        $this->authService = new AuthService(new UserRepository());
        $this->chatService = new ChatService(new ChatRepository());
    }

    public function index(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();
        $chats       = $this->chatService->getUserChats($currentUser->id);

        $this->view('chats/index', [
            'layout'      => 'app',
            'title'       => 'Chats',
            'chats'       => $chats,
            'currentUser' => $currentUser,
            'activeChat'  => null,
        ]);
    }

    public function show(string $id): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();
        $chatId      = (int) $id;

        if (!$this->chatService->isMember($chatId, $currentUser->id)) {
            $this->response->setStatusCode(403);
            $this->view('errors/404', ['title' => 'Access denied']);
            return;
        }

        $chat  = $this->chatService->findById($chatId, $currentUser->id);
        $chats = $this->chatService->getUserChats($currentUser->id);

        $this->view('chats/show', [
            'layout'      => 'app',
            'title'       => $chat->displayName(),
            'chats'       => $chats,
            'currentUser' => $currentUser,
            'activeChat'  => $chat,
        ]);
    }

    public function newChat(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();
        $chats       = $this->chatService->getUserChats($currentUser->id);

        $this->view('chats/new', [
            'layout'      => 'app',
            'title'       => 'New Message',
            'chats'       => $chats,
            'currentUser' => $currentUser,
            'activeChat'  => null,
        ]);
    }

    public function store(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();
        $otherUserId = (int) $this->request->post('user_id', 0);

        if ($otherUserId <= 0) {
            $this->redirect('/DELOX/chats/new');
        }

        try {
            $chat = $this->chatService->createPrivateChat($currentUser->id, $otherUserId);
            $this->redirect('/DELOX/chats/' . $chat->id);
        } catch (RuntimeException) {
            $this->redirect('/DELOX/chats/new');
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;
use App\Services\AuthService;

class ChatController extends Controller
{
    private AuthService $authService;

    public function __construct(
        \App\Core\Request  $request,
        \App\Core\Response $response,
    ) {
        parent::__construct($request, $response);
        $this->authService = new AuthService(new UserRepository());
    }

    public function index(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();

        $this->view('chats/index', [
            'title'       => 'Chats',
            'currentUser' => $currentUser,
        ]);
    }
}

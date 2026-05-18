<?php

declare(strict_types=1);

use App\Controllers\MessageController;
use App\Controllers\UserController;

$router->get('/api/users/search', [UserController::class, 'search']);

$router->get('/api/chats/:chatId/messages',  [MessageController::class, 'index']);
$router->post('/api/chats/:chatId/messages', [MessageController::class, 'store']);

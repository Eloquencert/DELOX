<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    public function search(): void
    {
        AuthMiddleware::handle();

        $query = trim($this->request->get('q', ''));

        if (mb_strlen($query) < 2) {
            $this->json(['users' => []]);
        }

        $currentUserId = (int) $_SESSION['user_id'];
        $users = (new UserRepository())->search($query, $currentUserId);

        $this->json([
            'users' => array_map(fn($u) => [
                'id'           => $u->id,
                'username'     => $u->username,
                'display_name' => $u->displayName,
                'avatar'       => $u->avatar,
            ], $users),
        ]);
    }
}

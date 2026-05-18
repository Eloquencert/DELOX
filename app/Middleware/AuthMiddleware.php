<?php

declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (empty($_SESSION['user_id'])) {
            header('Location: /DELOX/login');
            exit;
        }
    }
}

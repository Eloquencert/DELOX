<?php

declare(strict_types=1);

namespace App\Middleware;

/** Redirects already-authenticated users away from guest-only pages. */
class GuestMiddleware
{
    public static function handle(): void
    {
        if (!empty($_SESSION['user_id'])) {
            header('Location: /DELOX/chats');
            exit;
        }
    }
}

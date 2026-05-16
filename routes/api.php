<?php

declare(strict_types=1);

use App\Controllers\UserController;

/** @var \App\Core\Router $router */

$router->get('/api/users/search', [UserController::class, 'search']);

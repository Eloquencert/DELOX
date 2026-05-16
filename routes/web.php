<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\HomeController;

/** @var \App\Core\Router $router */

// ─── Public ───────────────────────────────────────────────────────────────────
$router->get('/', [HomeController::class, 'index']);

// ─── Auth (guest-only) ────────────────────────────────────────────────────────
$router->get('/login',    [AuthController::class, 'showLogin']);
$router->post('/login',   [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register',[AuthController::class, 'register']);
$router->post('/logout',  [AuthController::class, 'logout']);

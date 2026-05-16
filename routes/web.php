<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\ChatController;
use App\Controllers\HomeController;
use App\Controllers\ProfileController;

/** @var \App\Core\Router $router */

// ─── Public ───────────────────────────────────────────────────────────────────
$router->get('/', [HomeController::class, 'index']);

// ─── Auth (guest-only) ────────────────────────────────────────────────────────
$router->get('/login',     [AuthController::class, 'showLogin']);
$router->post('/login',    [AuthController::class, 'login']);
$router->get('/register',  [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout',   [AuthController::class, 'logout']);

// ─── Chats ────────────────────────────────────────────────────────────────────
$router->get('/chats',         [ChatController::class, 'index']);
$router->get('/chats/new',     [ChatController::class, 'newChat']);
$router->post('/chats',        [ChatController::class, 'store']);
$router->get('/chats/:id',     [ChatController::class, 'show']);

// ─── Profile ──────────────────────────────────────────────────────────────────
$router->get('/profile/edit',      [ProfileController::class, 'edit']);
$router->post('/profile/update',   [ProfileController::class, 'update']);
$router->post('/profile/avatar',   [ProfileController::class, 'uploadAvatar']);
$router->get('/profile/:username', [ProfileController::class, 'show']);

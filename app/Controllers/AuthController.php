<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Middleware\GuestMiddleware;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use RuntimeException;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(
        \App\Core\Request  $request,
        \App\Core\Response $response,
    ) {
        parent::__construct($request, $response);
        $this->authService = new AuthService(new UserRepository());
    }

    public function showLogin(): void
    {
        GuestMiddleware::handle();
        $this->view('auth/login', ['title' => 'Sign In', 'layout' => 'auth']);
    }

    public function login(): void
    {
        GuestMiddleware::handle();

        $email    = trim($this->request->post('email', ''));
        $password = $this->request->post('password', '');

        $user = $this->authService->attempt($email, $password);

        if ($user === null) {
            Session::flash('error', 'Invalid email or password.');
            $this->redirect('/DELOX/login');
        }

        $this->authService->login($user);
        $this->redirect('/DELOX/chats');
    }

    public function showRegister(): void
    {
        GuestMiddleware::handle();
        $this->view('auth/register', ['title' => 'Create Account', 'layout' => 'auth']);
    }

    public function register(): void
    {
        GuestMiddleware::handle();

        $username = trim($this->request->post('username', ''));
        $email    = trim($this->request->post('email', ''));
        $password = $this->request->post('password', '');

        $validator = (new Validator())
            ->required('username', $username)
            ->alphanumericUnderscore('username', $username)
            ->minLength('username', $username, 3)
            ->maxLength('username', $username, 32)
            ->required('email', $email)
            ->email('email', $email)
            ->required('password', $password)
            ->minLength('password', $password, 8);

        if (!$validator->passes()) {
            Session::flash('errors', $validator->errors());
            Session::flash('old', compact('username', 'email'));
            $this->redirect('/DELOX/register');
        }

        try {
            $user = $this->authService->register($username, $email, $password);
            $this->authService->login($user);
            $this->redirect('/DELOX/chats');
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            Session::flash('old', compact('username', 'email'));
            $this->redirect('/DELOX/register');
        }
    }

    public function logout(): void
    {
        $this->authService->logout();
        $this->redirect('/DELOX/login');
    }
}

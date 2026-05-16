<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\UserService;
use RuntimeException;

class ProfileController extends Controller
{
    private AuthService $authService;
    private UserService $userService;

    public function __construct(
        \App\Core\Request  $request,
        \App\Core\Response $response,
    ) {
        parent::__construct($request, $response);
        $repo = new UserRepository();
        $this->authService = new AuthService($repo);
        $this->userService = new UserService($repo);
    }

    public function show(string $username): void
    {
        AuthMiddleware::handle();

        $user = (new UserRepository())->findByUsername($username);

        if ($user === null) {
            $this->response->setStatusCode(404);
            $this->view('errors/404', ['title' => 'User not found']);
            return;
        }

        $currentUser = $this->authService->currentUser();
        $isOwn       = $currentUser !== null && $currentUser->id === $user->id;

        $this->view('profile/show', [
            'title'       => $user->displayName,
            'user'        => $user,
            'currentUser' => $currentUser,
            'isOwn'       => $isOwn,
        ]);
    }

    public function edit(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();

        $this->view('profile/edit', [
            'title' => 'Edit Profile',
            'user'  => $currentUser,
        ]);
    }

    public function update(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();

        $displayName = trim($this->request->post('display_name', ''));
        $bio         = trim($this->request->post('bio', ''));

        $validator = (new Validator())
            ->required('display_name', $displayName)
            ->maxLength('display_name', $displayName, 64)
            ->maxLength('bio', $bio, 300);

        if (!$validator->passes()) {
            Session::flash('errors', $validator->errors());
            $this->redirect('/DELOX/profile/edit');
        }

        try {
            $this->userService->updateProfile($currentUser->id, $displayName, $bio);
            Session::flash('success', 'Profile updated.');
            $this->redirect('/DELOX/profile/' . $currentUser->username);
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect('/DELOX/profile/edit');
        }
    }

    public function uploadAvatar(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();
        $file        = $this->request->file('avatar');

        if ($file === null) {
            Session::flash('error', 'No file selected.');
            $this->redirect('/DELOX/profile/edit');
        }

        try {
            $this->userService->uploadAvatar($currentUser->id, $file);
            Session::flash('success', 'Avatar updated.');
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect('/DELOX/profile/edit');
    }
}

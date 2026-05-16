<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Helpers\Session;
use App\Helpers\Validator;
use App\Middleware\AuthMiddleware;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\SettingsService;
use RuntimeException;

class SettingsController extends Controller
{
    private AuthService     $authService;
    private SettingsService $settingsService;

    public function __construct(
        \App\Core\Request  $request,
        \App\Core\Response $response,
    ) {
        parent::__construct($request, $response);
        $repo                  = new UserRepository();
        $this->authService     = new AuthService($repo);
        $this->settingsService = new SettingsService($repo);
    }

    public function index(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();

        $this->view('settings/index', [
            'title'       => 'Settings',
            'currentUser' => $currentUser,
        ]);
    }

    public function updateEmail(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();

        $newEmail = trim($this->request->post('email', ''));
        $password = $this->request->post('password', '');

        $validator = (new Validator())
            ->required('email', $newEmail)
            ->email('email', $newEmail)
            ->required('password', $password);

        if (!$validator->passes()) {
            Session::flash('email_errors', $validator->errors());
            $this->redirect('/DELOX/settings#security');
        }

        try {
            $this->settingsService->changeEmail($currentUser->id, $newEmail, $password);
            Session::flash('success', 'Email updated successfully.');
        } catch (RuntimeException $e) {
            Session::flash('email_error', $e->getMessage());
        }

        $this->redirect('/DELOX/settings#security');
    }

    public function updatePassword(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();

        $currentPassword = $this->request->post('current_password', '');
        $newPassword     = $this->request->post('new_password', '');
        $confirmPassword = $this->request->post('confirm_password', '');

        $validator = (new Validator())
            ->required('current_password', $currentPassword)
            ->required('new_password', $newPassword)
            ->minLength('new_password', $newPassword, 8)
            ->required('confirm_password', $confirmPassword);

        if (!$validator->passes()) {
            Session::flash('password_errors', $validator->errors());
            $this->redirect('/DELOX/settings#security');
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('password_error', 'New passwords do not match.');
            $this->redirect('/DELOX/settings#security');
        }

        try {
            $this->settingsService->changePassword($currentUser->id, $currentPassword, $newPassword);
            Session::flash('success', 'Password changed successfully.');
        } catch (RuntimeException $e) {
            Session::flash('password_error', $e->getMessage());
        }

        $this->redirect('/DELOX/settings#security');
    }

    public function deleteAccount(): void
    {
        AuthMiddleware::handle();
        $currentUser = $this->authService->currentUser();

        $password    = $this->request->post('password', '');
        $confirmation = $this->request->post('confirmation', '');

        if ($confirmation !== 'DELETE') {
            Session::flash('delete_error', 'Type DELETE to confirm account deletion.');
            $this->redirect('/DELOX/settings#danger');
        }

        try {
            $this->settingsService->deleteAccount($currentUser->id, $password);
            $this->authService->logout();
            $this->redirect('/DELOX/register');
        } catch (RuntimeException $e) {
            Session::flash('delete_error', $e->getMessage());
            $this->redirect('/DELOX/settings#danger');
        }
    }
}

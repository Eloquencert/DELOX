<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use RuntimeException;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Register a new user and return the created entity.
     *
     * @throws RuntimeException when email or username is already taken
     */
    public function register(string $username, string $email, string $password): User
    {
        if ($this->users->emailExists($email)) {
            throw new RuntimeException('This email is already registered.');
        }

        if ($this->users->usernameExists($username)) {
            throw new RuntimeException('This username is already taken.');
        }

        $id = $this->users->create([
            'username'      => $username,
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'display_name'  => $username,
        ]);

        return $this->users->findById($id);
    }

    /**
     * Verify credentials and return the User, or null on failure.
     */
    public function attempt(string $email, string $password): ?User
    {
        $row = $this->users->findCredentialsByEmail($email);

        if ($row === null || !password_verify($password, $row['password_hash'])) {
            return null;
        }

        return User::fromArray($row);
    }

    /** Persist the authenticated user in the session. */
    public function login(User $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function currentUser(): ?User
    {
        if (!$this->check()) {
            return null;
        }

        $user = $this->users->findById((int) $_SESSION['user_id']);

        if ($user) {
            $this->users->updateLastSeen($user->id);
        }

        return $user;
    }
}

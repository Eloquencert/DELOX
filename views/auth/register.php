<?php

use App\Helpers\Session;

$error  = Session::getFlash('error');
$errors = Session::getFlash('errors', []);
$old    = Session::getFlash('old', []);

$fieldError = fn(string $field): string =>
    isset($errors[$field])
        ? '<span class="field-error">' . htmlspecialchars($errors[$field]) . '</span>'
        : '';
?>

<h2 class="auth-title">Create account</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form action="/DELOX/register" method="POST" class="auth-form">
    <div class="form-group">
        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
            placeholder="john_doe"
            value="<?= htmlspecialchars($old['username'] ?? '') ?>"
            required
            autofocus
        >
        <?= $fieldError('username') ?>
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
            placeholder="you@example.com"
            value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            required
        >
        <?= $fieldError('email') ?>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
            placeholder="Min. 8 characters"
            required
        >
        <?= $fieldError('password') ?>
    </div>

    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
</form>

<p class="auth-footer">
    Already have an account? <a href="/DELOX/login">Sign in</a>
</p>

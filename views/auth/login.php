<?php

use App\Helpers\Session;

$error = Session::getFlash('error');
?>

<h2 class="auth-title">Welcome back</h2>

<?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form action="/DELOX/login" method="POST" class="auth-form">
    <div class="form-group">
        <label for="email">Email</label>
        <input
            type="email"
            id="email"
            name="email"
            class="form-control"
            placeholder="you@example.com"
            required
            autofocus
        >
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="••••••••"
            required
        >
    </div>

    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
</form>

<p class="auth-footer">
    Don't have an account? <a href="/DELOX/register">Create one</a>
</p>

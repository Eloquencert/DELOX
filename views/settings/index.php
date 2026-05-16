<?php

use App\Helpers\Session;

$success       = Session::getFlash('success');
$emailError    = Session::getFlash('email_error');
$emailErrors   = Session::getFlash('email_errors', []);
$passwordError = Session::getFlash('password_error');
$passwordErrors= Session::getFlash('password_errors', []);
$deleteError   = Session::getFlash('delete_error');

$fe = fn(array $errs, string $field): string =>
    isset($errs[$field])
        ? '<span class="field-error">' . htmlspecialchars($errs[$field]) . '</span>'
        : '';
?>

<div class="settings-page">
    <aside class="settings-nav">
        <h2 class="settings-nav-title">Settings</h2>
        <a href="#profile"  class="settings-nav-link">Profile</a>
        <a href="#security" class="settings-nav-link">Security</a>
        <a href="#danger"   class="settings-nav-link danger">Danger Zone</a>
    </aside>

    <div class="settings-content">

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- ─── Profile ─────────────────────────────────────────────── -->
        <section class="settings-section" id="profile">
            <h3 class="settings-section-title">Profile</h3>

            <div class="settings-profile-row">
                <div class="settings-avatar">
                    <?php if ($currentUser->avatar): ?>
                        <img
                            src="/DELOX/storage/uploads/avatars/<?= htmlspecialchars($currentUser->avatar) ?>"
                            alt="avatar"
                        >
                    <?php else: ?>
                        <span><?= mb_strtoupper(mb_substr($currentUser->displayName, 0, 1)) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="settings-name"><?= htmlspecialchars($currentUser->displayName) ?></p>
                    <p class="settings-username">@<?= htmlspecialchars($currentUser->username) ?></p>
                    <?php if ($currentUser->bio): ?>
                        <p class="settings-bio"><?= htmlspecialchars($currentUser->bio) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <a href="/DELOX/profile/edit" class="btn btn-ghost" style="margin-top:1rem">
                Edit Profile &amp; Avatar
            </a>
        </section>

        <hr class="settings-divider">

        <!-- ─── Security ────────────────────────────────────────────── -->
        <section class="settings-section" id="security">
            <h3 class="settings-section-title">Security</h3>

            <!-- Change Email -->
            <div class="settings-card">
                <h4 class="settings-card-title">Change Email</h4>
                <p class="settings-card-sub">Current: <strong><?= htmlspecialchars($currentUser->email) ?></strong></p>

                <?php if ($emailError): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($emailError) ?></div>
                <?php endif; ?>

                <form action="/DELOX/settings/email" method="POST" class="settings-form">
                    <div class="form-group">
                        <label for="email">New Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control <?= isset($emailErrors['email']) ? 'is-invalid' : '' ?>"
                            placeholder="new@example.com"
                            required
                        >
                        <?= $fe($emailErrors, 'email') ?>
                    </div>
                    <div class="form-group">
                        <label for="email_password">Confirm with Password</label>
                        <input
                            type="password"
                            id="email_password"
                            name="password"
                            class="form-control <?= isset($emailErrors['password']) ? 'is-invalid' : '' ?>"
                            placeholder="Your current password"
                            required
                        >
                        <?= $fe($emailErrors, 'password') ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Email</button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="settings-card" style="margin-top:1.5rem">
                <h4 class="settings-card-title">Change Password</h4>

                <?php if ($passwordError): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($passwordError) ?></div>
                <?php endif; ?>

                <form action="/DELOX/settings/password" method="POST" class="settings-form">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            class="form-control <?= isset($passwordErrors['current_password']) ? 'is-invalid' : '' ?>"
                            required
                        >
                        <?= $fe($passwordErrors, 'current_password') ?>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            class="form-control <?= isset($passwordErrors['new_password']) ? 'is-invalid' : '' ?>"
                            placeholder="Min. 8 characters"
                            required
                        >
                        <?= $fe($passwordErrors, 'new_password') ?>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="form-control"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </section>

        <hr class="settings-divider">

        <!-- ─── Danger Zone ──────────────────────────────────────────── -->
        <section class="settings-section" id="danger">
            <h3 class="settings-section-title danger-title">Danger Zone</h3>

            <div class="settings-card danger-card">
                <h4 class="settings-card-title">Delete Account</h4>
                <p class="settings-card-sub">
                    This will permanently delete your account, all your chats, and messages.
                    <strong>This action cannot be undone.</strong>
                </p>

                <?php if ($deleteError): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($deleteError) ?></div>
                <?php endif; ?>

                <form
                    action="/DELOX/settings/delete"
                    method="POST"
                    class="settings-form"
                    onsubmit="return confirm('Are you absolutely sure? This cannot be undone.')"
                >
                    <div class="form-group">
                        <label for="delete_password">Password</label>
                        <input
                            type="password"
                            id="delete_password"
                            name="password"
                            class="form-control"
                            placeholder="Confirm your password"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="confirmation">
                            Type <strong>DELETE</strong> to confirm
                        </label>
                        <input
                            type="text"
                            id="confirmation"
                            name="confirmation"
                            class="form-control"
                            placeholder="DELETE"
                            autocomplete="off"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-danger">Delete My Account</button>
                </form>
            </div>
        </section>

    </div>
</div>

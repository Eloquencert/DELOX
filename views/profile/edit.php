<?php

    use App\Helpers\Session;

    $success = Session::getFlash('success');
    $error   = Session::getFlash('error');
    $errors  = Session::getFlash('errors', []);

    $fieldError = fn(string $field): string =>
    isset($errors[$field])
    ? '<span class="field-error">' . htmlspecialchars($errors[$field]) . '</span>'
    : '';
?>

<div class="profile-page">
    <div class="profile-card">
        <h2 class="profile-edit-title">Edit Profile</h2>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Avatar upload -->
        <form action="/DELOX/profile/avatar" method="POST" enctype="multipart/form-data" class="avatar-upload-form">
            <div class="avatar-upload-wrap">
                <?php if ($user->avatar): ?>
                    <img
                        src="/DELOX/storage/uploads/avatars/<?php echo htmlspecialchars($user->avatar) ?>"
                        alt="avatar"
                        class="profile-avatar"
                        id="avatarPreview"
                    >
                <?php else: ?>
                    <div class="profile-avatar profile-avatar-initials" id="avatarPreview">
                        <?php echo mb_strtoupper(mb_substr($user->displayName, 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <label for="avatarInput" class="avatar-upload-label">Change photo</label>
                <input
                    type="file"
                    id="avatarInput"
                    name="avatar"
                    accept="image/*"
                    class="avatar-upload-input"
                    onchange="this.form.submit()"
                >
            </div>
        </form>

        <!-- Profile info -->
        <form action="/DELOX/profile/update" method="POST" class="auth-form" style="margin-top:1.5rem">
            <div class="form-group">
                <label for="display_name">Display Name</label>
                <input
                    type="text"
                    id="display_name"
                    name="display_name"
                    class="form-control <?php echo isset($errors['display_name']) ? 'is-invalid' : '' ?>"
                    value="<?php echo htmlspecialchars($user->displayName) ?>"
                    maxlength="64"
                    required
                >
                <?php echo $fieldError('display_name') ?>
            </div>

            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea
                    id="bio"
                    name="bio"
                    class="form-control <?php echo isset($errors['bio']) ? 'is-invalid' : '' ?>"
                    rows="4"
                    maxlength="300"
                    placeholder="Tell something about yourself..."
                ><?php echo htmlspecialchars($user->bio ?? '') ?></textarea>
                <?php echo $fieldError('bio') ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
        </form>

        <div style="text-align:center; margin-top:1rem">
            <a href="/DELOX/profile/<?php echo htmlspecialchars($user->username) ?>" class="btn btn-ghost">
                Back to Profile
            </a>
        </div>
    </div>
</div>

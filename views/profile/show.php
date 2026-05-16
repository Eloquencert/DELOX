<?php

use App\Helpers\Session;

$flash = Session::getFlash('success');
?>

<div class="profile-page">
    <div class="profile-card">

        <div class="profile-avatar-wrap">
            <?php if ($user->avatar): ?>
                <img
                    src="/DELOX/storage/uploads/avatars/<?= htmlspecialchars($user->avatar) ?>"
                    alt="<?= htmlspecialchars($user->displayName) ?>"
                    class="profile-avatar"
                >
            <?php else: ?>
                <div class="profile-avatar profile-avatar-initials">
                    <?= mb_strtoupper(mb_substr($user->displayName, 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-info">
            <h1 class="profile-display-name"><?= htmlspecialchars($user->displayName) ?></h1>
            <p class="profile-username">@<?= htmlspecialchars($user->username) ?></p>

            <?php if ($user->bio): ?>
                <p class="profile-bio"><?= nl2br(htmlspecialchars($user->bio)) ?></p>
            <?php endif; ?>

            <p class="profile-meta">
                Member since <?= date('F Y', strtotime($user->createdAt)) ?>
            </p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <div class="profile-actions">
            <?php if ($isOwn): ?>
                <a href="/DELOX/profile/edit" class="btn btn-primary">Edit Profile</a>
            <?php else: ?>
                <a href="/DELOX/chats" class="btn btn-primary">Send Message</a>
            <?php endif; ?>
        </div>

    </div>
</div>

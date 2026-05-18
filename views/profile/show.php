<?php

    use App\Helpers\Session;

    $flash = Session::getFlash('success');
?>

<div class="profile-page">
    <div class="profile-card">

        <div class="profile-avatar-wrap">
            <?php if ($user->avatar): ?>
                <img
                    src="/DELOX/storage/uploads/avatars/<?php echo htmlspecialchars($user->avatar) ?>"
                    alt="<?php echo htmlspecialchars($user->displayName) ?>"
                    class="profile-avatar"
                >
            <?php else: ?>
                <div class="profile-avatar profile-avatar-initials">
                    <?php echo mb_strtoupper(mb_substr($user->displayName, 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="profile-info">
            <h1 class="profile-display-name"><?php echo htmlspecialchars($user->displayName) ?></h1>
            <p class="profile-username">@<?php echo htmlspecialchars($user->username) ?></p>

            <?php if ($user->bio): ?>
                <p class="profile-bio"><?php echo nl2br(htmlspecialchars($user->bio)) ?></p>
            <?php endif; ?>

            <p class="profile-meta">
                Member since <?php echo date('F Y', strtotime($user->createdAt)) ?>
            </p>
        </div>

        <?php if ($flash): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flash) ?></div>
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

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'DELOX') ?> — DELOX</title>
    <link rel="stylesheet" href="/DELOX/public/css/app.css">
</head>
<body>

<?php if (!empty($_SESSION['user_id'])): ?>
<nav class="app-nav">
    <a href="/DELOX/" class="app-nav-brand">💬 DELOX</a>

    <div class="app-nav-links">
        <a href="/DELOX/chats"    class="nav-link">Chats</a>
        <a href="/DELOX/contacts" class="nav-link">People</a>
    </div>

    <div class="app-nav-user">
        <?php if (!empty($_SESSION['avatar'])): ?>
            <img
                src="/DELOX/storage/uploads/avatars/<?= htmlspecialchars($_SESSION['avatar']) ?>"
                class="nav-avatar"
                alt="avatar"
            >
        <?php else: ?>
            <div class="nav-avatar nav-avatar-initials">
                <?= mb_strtoupper(mb_substr($_SESSION['display_name'] ?? 'U', 0, 1)) ?>
            </div>
        <?php endif; ?>

        <a href="/DELOX/profile/<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" class="nav-username">
            <?= htmlspecialchars($_SESSION['display_name'] ?? '') ?>
        </a>

        <a href="/DELOX/settings" class="nav-link" title="Settings">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
        </a>

        <form action="/DELOX/logout" method="POST">
            <button type="submit" class="btn-logout">Sign out</button>
        </form>
    </div>
</nav>
<?php endif; ?>

<main class="app-main">
    <?= $content ?>
</main>

<script src="/DELOX/public/js/app.js"></script>
</body>
</html>

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

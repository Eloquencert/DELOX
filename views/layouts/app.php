<?php use App\Helpers\DateHelper; ?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'DELOX') ?> — DELOX</title>
    <link rel="stylesheet" href="/DELOX/public/css/app.css">
</head>
<body class="app-body">
<div class="app-layout">

    <!-- ─── Sidebar ─────────────────────────────────────────────── -->
    <aside class="sidebar">

        <!-- Header -->
        <div class="sidebar-header">
            <div class="sidebar-me">
                <?php if (!empty($_SESSION['avatar'])): ?>
                    <img
                        src="/DELOX/storage/uploads/avatars/<?= htmlspecialchars($_SESSION['avatar']) ?>"
                        class="sidebar-avatar"
                        alt="me"
                    >
                <?php else: ?>
                    <div class="sidebar-avatar sidebar-avatar-initials">
                        <?= mb_strtoupper(mb_substr($_SESSION['display_name'] ?? 'U', 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <a href="/DELOX/profile/<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" class="sidebar-me-name">
                    <?= htmlspecialchars($_SESSION['display_name'] ?? '') ?>
                </a>
            </div>
            <div class="sidebar-header-actions">
                <a href="/DELOX/chats/new" class="btn-icon" title="New message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </a>
                <form action="/DELOX/logout" method="POST">
                    <button type="submit" class="btn-icon" title="Sign out">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Search -->
        <div class="sidebar-search">
            <input
                type="search"
                id="chatSearch"
                placeholder="Search chats..."
                autocomplete="off"
            >
        </div>

        <!-- Chat list -->
        <nav class="chat-list" id="chatList">
            <?php if (empty($chats)): ?>
                <div class="chat-list-empty">
                    <p>No chats yet.</p>
                    <a href="/DELOX/chats/new">Start one →</a>
                </div>
            <?php else: ?>
                <?php foreach ($chats as $chat): ?>
                    <?php $isActive = isset($activeChat) && $activeChat->id === $chat->id; ?>
                    <a
                        href="/DELOX/chats/<?= $chat->id ?>"
                        class="chat-item <?= $isActive ? 'active' : '' ?>"
                        data-name="<?= htmlspecialchars(mb_strtolower($chat->displayName())) ?>"
                    >
                        <!-- Avatar -->
                        <div class="chat-item-avatar">
                            <?php if ($chat->avatarUrl()): ?>
                                <img src="<?= htmlspecialchars($chat->avatarUrl()) ?>" alt="avatar">
                            <?php else: ?>
                                <span><?= htmlspecialchars($chat->initial()) ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Body -->
                        <div class="chat-item-body">
                            <div class="chat-item-top">
                                <span class="chat-item-name"><?= htmlspecialchars($chat->displayName()) ?></span>
                                <?php if ($chat->lastMessageAt): ?>
                                    <span class="chat-item-time"><?= DateHelper::chatTime($chat->lastMessageAt) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="chat-item-preview">
                                <?= $chat->lastMessage
                                    ? htmlspecialchars(mb_substr($chat->lastMessage, 0, 60))
                                    : '<em>No messages yet</em>' ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </nav>

    </aside>

    <!-- ─── Main content ─────────────────────────────────────────── -->
    <main class="chat-content">
        <?= $content ?>
    </main>

</div>
<script src="/DELOX/public/js/app.js"></script>
<?php if (isset($activeChat) && $activeChat !== null): ?>
    <script>
        const CHAT_ID        = <?= (int) $activeChat->id ?>;
        const CURRENT_USER_ID = <?= (int) $_SESSION['user_id'] ?>;
    </script>
    <script src="/DELOX/public/js/chat.js"></script>
<?php endif; ?>
</body>
</html>

<!-- Chat header -->
<div class="chat-header">
    <div class="chat-header-avatar">
        <?php if ($activeChat->avatarUrl()): ?>
            <img src="<?= htmlspecialchars($activeChat->avatarUrl()) ?>" alt="avatar">
        <?php else: ?>
            <span><?= htmlspecialchars($activeChat->initial()) ?></span>
        <?php endif; ?>
    </div>
    <div class="chat-header-info">
        <h2 class="chat-header-name"><?= htmlspecialchars($activeChat->displayName()) ?></h2>
        <?php if ($activeChat->type === 'private' && $activeChat->otherUsername): ?>
            <span class="chat-header-sub">
                @<?= htmlspecialchars($activeChat->otherUsername) ?>
            </span>
        <?php else: ?>
            <span class="chat-header-sub">Group chat</span>
        <?php endif; ?>
    </div>
</div>

<!-- Messages area (populated in feature/messages) -->
<div class="chat-messages" id="chatMessages" data-chat-id="<?= $activeChat->id ?>">
    <div class="chat-messages-empty">
        <p>No messages yet. Say hi! 👋</p>
    </div>
</div>

<!-- Message input (activated in feature/messages) -->
<div class="chat-input-bar">
    <input
        type="text"
        id="messageInput"
        class="chat-input"
        placeholder="Write a message..."
        autocomplete="off"
        disabled
    >
    <button class="btn-send" disabled title="Send">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
            <path d="M2 21l21-9L2 3v7l15 2-15 2z"/>
        </svg>
    </button>
</div>

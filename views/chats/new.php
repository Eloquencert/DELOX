<div class="new-chat-panel">
    <div class="new-chat-header">
        <h2>New Message</h2>
        <p>Search for a user to start a conversation</p>
    </div>

    <div class="new-chat-search">
        <input
            type="search"
            id="userSearch"
            class="form-control"
            placeholder="Search by name or username..."
            autocomplete="off"
        >
    </div>

    <div id="searchResults" class="user-search-results"></div>
</div>

<script>
(function () {
    const input   = document.getElementById('userSearch');
    const results = document.getElementById('searchResults');
    let   timer   = null;

    input.addEventListener('input', () => {
        clearTimeout(timer);
        const q = input.value.trim();

        if (q.length < 2) {
            results.innerHTML = '';
            return;
        }

        timer = setTimeout(() => {
            App.api('/api/users/search?q=' + encodeURIComponent(q))
                .then(data => {
                    if (!data.users.length) {
                        results.innerHTML = '<p class="search-empty">No users found.</p>';
                        return;
                    }

                    results.innerHTML = data.users.map(user => `
                        <form action="/DELOX/chats" method="POST" class="user-result-form">
                            <input type="hidden" name="user_id" value="${user.id}">
                            <button type="submit" class="user-result">
                                <div class="user-result-avatar">
                                    ${user.avatar
                                        ? `<img src="/DELOX/storage/uploads/avatars/${user.avatar}" alt="">`
                                        : `<span>${user.display_name[0].toUpperCase()}</span>`
                                    }
                                </div>
                                <div class="user-result-info">
                                    <span class="user-result-name">${user.display_name}</span>
                                    <span class="user-result-username">@${user.username}</span>
                                </div>
                            </button>
                        </form>
                    `).join('');
                });
        }, 300);
    });

    input.focus();
}());
</script>

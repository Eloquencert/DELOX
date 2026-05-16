'use strict';

const App = {
    baseUrl: '/DELOX',

    api(endpoint, options = {}) {
        return fetch(this.baseUrl + endpoint, {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...options.headers,
            },
            ...options,
        }).then(res => res.json());
    },
};

// ─── Sidebar chat filter ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('chatSearch');
    const chatItems   = document.querySelectorAll('.chat-item');

    if (searchInput && chatItems.length) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.toLowerCase().trim();

            chatItems.forEach(item => {
                const name = item.dataset.name ?? '';
                item.style.display = (!q || name.includes(q)) ? '' : 'none';
            });
        });
    }
});

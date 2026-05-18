'use strict';

(function () {
    const messagesEl = document.getElementById('chatMessages');
    const inputEl    = document.getElementById('messageInput');
    const sendBtn    = document.getElementById('sendBtn');

    if (!messagesEl || !inputEl || !sendBtn) return;

    let lastId   = 0;
    let isSending = false;

    // ─── HTML escaping ────────────────────────────────────────────────────────
    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ─── Render a single message bubble ──────────────────────────────────────
    function renderMessage(msg) {
        const isOwn = msg.sender_id === CURRENT_USER_ID;

        const avatarHtml = isOwn ? '' : `
            <div class="msg-avatar">
                ${msg.sender.avatar
                    ? `<img src="/DELOX/storage/uploads/avatars/${esc(msg.sender.avatar)}" alt="">`
                    : `<span>${esc(msg.sender.display_name[0].toUpperCase())}</span>`
                }
            </div>`;

        const senderNameHtml = (!isOwn)
            ? `<span class="msg-sender">${esc(msg.sender.display_name)}</span>`
            : '';

        const wrap = document.createElement('div');
        wrap.className = `msg-wrap ${isOwn ? 'msg-own' : 'msg-other'}`;
        wrap.dataset.id = msg.id;
        wrap.innerHTML = `
            ${avatarHtml}
            <div class="msg-bubble">
                ${senderNameHtml}
                <p class="msg-text">${esc(msg.content)}</p>
                <span class="msg-time">${esc(msg.time)}</span>
            </div>`;

        messagesEl.querySelector('.chat-messages-empty')?.remove();
        messagesEl.appendChild(wrap);
    }

    function scrollToBottom(smooth = false) {
        messagesEl.scrollTo({
            top:      messagesEl.scrollHeight,
            behavior: smooth ? 'smooth' : 'instant',
        });
    }

    function isNearBottom() {
        return messagesEl.scrollHeight - messagesEl.scrollTop - messagesEl.clientHeight < 120;
    }

    // ─── Fetch new messages (long-polling tick) ───────────────────────────────
    function poll() {
        App.api(`/api/chats/${CHAT_ID}/messages?after=${lastId}`)
            .then(data => {
                if (!Array.isArray(data.messages) || data.messages.length === 0) return;

                const shouldScroll = isNearBottom();

                data.messages.forEach(renderMessage);
                lastId = data.messages.at(-1).id;

                if (shouldScroll) scrollToBottom(true);
            })
            .catch(() => {}); 
    }

    // ─── Send message ─────────────────────────────────────────────────────────
    function sendMessage() {
        const content = inputEl.value.trim();
        if (!content || isSending) return;

        isSending        = true;
        inputEl.disabled = true;
        sendBtn.disabled = true;
        inputEl.value    = '';

        App.api(`/api/chats/${CHAT_ID}/messages`, {
            method: 'POST',
            body:   JSON.stringify({ content }),
        })
        .then(data => {
            if (data.message) {
                renderMessage(data.message);
                lastId = data.message.id;
                scrollToBottom(true);
            }
        })
        .catch(() => {
            inputEl.value = content; 
        })
        .finally(() => {
            isSending        = false;
            inputEl.disabled = false;
            sendBtn.disabled = false;
            inputEl.focus();
        });
    }

    // ─── Event listeners ─────────────────────────────────────────────────────
    sendBtn.addEventListener('click', sendMessage);

    inputEl.addEventListener('keydown', e => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // ─── Bootstrap ───────────────────────────────────────────────────────────
    inputEl.focus();
    poll();                       
    setInterval(poll, 2000);        
}());

import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        axios: typeof axios;
        Echo?: Echo<'reverb'>;
        Pusher: typeof Pusher;
    }
}

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
        forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: '/broadcasting/auth',
        csrfToken:
            document
                .querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
                ?.content ?? null,
    });

    window.axios.interceptors.request.use((config) => {
        const socketId = window.Echo?.socketId();

        if (socketId) {
            config.headers.set('X-Socket-ID', socketId);
        }

        return config;
    });
}

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Reverb speaks the Pusher protocol, so the browser client is pusher-js driven
// by Echo. All connection details come from VITE_REVERB_* (see .env), and the
// private-channel handshake rides the session cookie plus the CSRF token.
window.Pusher = Pusher;

const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'https';
const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
    forceTLS: scheme === 'https',
    enabledTransports: ['ws', 'wss'],
    auth: { headers: { 'X-CSRF-TOKEN': csrf } },
});

export default window.Echo;

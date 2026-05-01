import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        Pusher: typeof Pusher;
        Echo?: Echo<'reverb'>;
    }
}

if (typeof window !== 'undefined') {
    window.Pusher = Pusher;

    /**
     * pusher-js ignores `forceTLS: false` when `document.protocol` is https (it always
     * chooses WSS first). Plain `ws://localhost:8080` can never work from an https Herd/site.
     * Fix: match `shouldUseTLS` — use WSS whenever the page is HTTPS, put Reverb on TLS
     * (`REVERB_HOST` = APP_URL hostname so Herd certs auto-load), and set VITE_REVERB_*.
     *
     * @see node_modules/pusher-js/dist/web/pusher.js `shouldUseTLS`
     */
    const pageHttps = window.location.protocol === 'https:';
    let wsHost = String(import.meta.env.VITE_REVERB_HOST ?? '').trim();
    if (!wsHost) {
        wsHost = window.location.hostname;
    }
    // Avoid doomed wss://localhost when the Laravel app runs on https://site.test instead.
    if (pageHttps && (wsHost === 'localhost' || wsHost === '127.0.0.1')) {
        wsHost = window.location.hostname;
    }

    const configuredPort = String(import.meta.env.VITE_REVERB_PORT ?? '').trim();
    const port =
        configuredPort !== ''
            ? Number(import.meta.env.VITE_REVERB_PORT)
            : pageHttps
              ? Number(window.location.port || 443)
              : Number(window.location.port || 80);

    const useTls = pageHttps || String(import.meta.env.VITE_REVERB_SCHEME ?? '')
        .trim()
        .toLowerCase() === 'https';

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost,
        wsPort: port,
        wssPort: port,
        forceTLS: useTls,
        enabledTransports: useTls ? ['wss'] : ['ws'],
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        },
    });
}

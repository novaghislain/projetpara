import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Global CSRF Token for all axios requests (SPA protection)
const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

// ─── Dynamic API base URL ─────────────────────────────────────────────
// Supports both XAMPP subdirectory deployments (e.g. /Para/public/)
// and artisan serve (http://127.0.0.1:8000/).
//
// The meta tag is set in app.blade.php: <meta name="base-url" content="...">
const baseUrlMeta = document.querySelector('meta[name=base-url]');
const apiBaseUrl = baseUrlMeta ? baseUrlMeta.content : '';

if (apiBaseUrl) {
    window.axios.defaults.baseURL = apiBaseUrl.endsWith('/') ? apiBaseUrl : apiBaseUrl + '/';

    // ─── Fix for fetch() API calls ─────────────────────────────────────
    // Many Vue components use fetch('/api/...') directly instead of axios.
    // Without a base URL prefix, /api/stats resolves to http://localhost/api/stats
    // instead of the correct http://localhost/Para/public/api/stats.
    // We wrap fetch to automatically prepend the base URL for API paths.
    const originalFetch = window.fetch;
    window.fetch = function(input, init) {
        if (typeof input === 'string' && input.startsWith('/') && !input.startsWith(apiBaseUrl)) {
            input = apiBaseUrl + input;
        } else if (input instanceof Request) {
            const url = input.url;
            if (typeof url === 'string' && url.startsWith('/') && !url.startsWith(apiBaseUrl)) {
                const headers = input.headers;
                input = new Request(apiBaseUrl + url, {
                    method: input.method,
                    headers: headers,
                    body: input.body,
                    mode: input.mode,
                    credentials: input.credentials,
                    cache: input.cache,
                    redirect: input.redirect,
                    referrer: input.referrer,
                    signal: input.signal,
                });
            }
        }
        return originalFetch.call(this, input, init);
    };
}

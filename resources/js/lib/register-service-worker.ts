/**
 * Registers the root service worker so browsers treat the app as installable.
 * The worker is network-only (no offline caching).
 */
export function registerServiceWorker(): void {
    if (!('serviceWorker' in navigator)) {
        return;
    }

    const register = (): void => {
        void navigator.serviceWorker.register('/sw.js').catch(() => {
            // Ignore insecure contexts and registration failures.
        });
    };

    // Module scripts often run after `load`; only waiting for that event
    // means registration never happens.
    if (document.readyState === 'complete') {
        register();
    } else {
        window.addEventListener('load', register);
    }
}

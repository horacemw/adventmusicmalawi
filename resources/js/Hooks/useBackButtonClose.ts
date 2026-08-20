import { useEffect, useRef } from 'react';

/**
 * Make the browser/phone back button close an overlay instead of leaving the site.
 *
 * On mount we push an extra history entry (same URL, sentinel state). When the
 * user hits back, popstate fires and we call `onClose`. If the overlay is closed
 * some other way (e.g. an in-app close button), the cleanup effect pops the
 * sentinel entry so the history stays balanced.
 *
 * `onClose` may be re-created every render (e.g. inline arrow in JSX) — we
 * route it through a ref so the effect only runs once per overlay lifetime.
 */
export function useBackButtonClose(onClose: () => void) {
    const onCloseRef = useRef(onClose);
    useEffect(() => {
        onCloseRef.current = onClose;
    });

    useEffect(() => {
        if (typeof window === 'undefined') return;

        window.history.pushState({ overlayOpen: true }, '');
        let closedByBack = false;

        const onPopState = () => {
            closedByBack = true;
            onCloseRef.current();
        };
        window.addEventListener('popstate', onPopState);

        return () => {
            window.removeEventListener('popstate', onPopState);
            // If the overlay was closed by other means, pop our sentinel entry
            // so hitting back later doesn't traverse a phantom step.
            if (!closedByBack) {
                window.history.back();
            }
        };
    }, []);
}

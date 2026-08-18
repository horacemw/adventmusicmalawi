import { createContext, useCallback, useContext, useEffect, useState, type ReactNode } from 'react';
import { router } from '@inertiajs/react';

interface MobileNavContextValue {
    isOpen: boolean;
    open: () => void;
    close: () => void;
    toggle: () => void;
}

const MobileNavContext = createContext<MobileNavContextValue | null>(null);

export function MobileNavProvider({ children }: { children: ReactNode }) {
    const [isOpen, setIsOpen] = useState(false);

    const open = useCallback(() => setIsOpen(true), []);
    const close = useCallback(() => setIsOpen(false), []);
    const toggle = useCallback(() => setIsOpen((v) => !v), []);

    // Close on route change so navigating from the drawer closes it
    useEffect(() => {
        const unregister = router.on('navigate', () => setIsOpen(false));
        return () => unregister();
    }, []);

    // Prevent body scroll when drawer is open
    useEffect(() => {
        if (typeof document === 'undefined') return;
        document.body.style.overflow = isOpen ? 'hidden' : '';
        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    // Close on Escape
    useEffect(() => {
        if (!isOpen) return;
        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') setIsOpen(false);
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [isOpen]);

    return (
        <MobileNavContext.Provider value={{ isOpen, open, close, toggle }}>
            {children}
        </MobileNavContext.Provider>
    );
}

export function useMobileNav(): MobileNavContextValue {
    const ctx = useContext(MobileNavContext);
    if (!ctx) throw new Error('useMobileNav must be used within MobileNavProvider');
    return ctx;
}

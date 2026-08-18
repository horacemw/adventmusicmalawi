import { X } from 'lucide-react';
import { useMobileNav } from '@/Contexts/MobileNavContext';
import { SidebarBrand, SidebarNav } from './SidebarNav';

export default function MobileNav() {
    const { isOpen, close } = useMobileNav();

    return (
        <>
            {/* Backdrop */}
            <button
                type="button"
                aria-hidden={!isOpen}
                onClick={close}
                className={`fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm transition-opacity lg:hidden ${
                    isOpen ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'
                }`}
                tabIndex={-1}
            />

            {/* Drawer */}
            <aside
                role="dialog"
                aria-modal="true"
                aria-label="Navigation"
                className={`fixed left-0 top-0 bottom-0 w-72 max-w-[85vw] z-50 bg-white border-r border-slate-200 flex flex-col shadow-2xl transition-transform duration-200 lg:hidden ${
                    isOpen ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="px-5 pt-5 pb-3 flex items-center justify-between">
                    <SidebarBrand />
                    <button
                        type="button"
                        onClick={close}
                        aria-label="Close navigation"
                        className="h-9 w-9 rounded-full flex items-center justify-center text-slate-500 hover:text-ink hover:bg-slate-100"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>
                <nav className="flex-1 overflow-y-auto px-3 pb-24 mt-2">
                    <SidebarNav />
                </nav>
            </aside>
        </>
    );
}

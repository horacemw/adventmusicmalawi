import { SidebarBrand, SidebarNav } from './SidebarNav';

export default function Sidebar() {
    return (
        <aside className="hidden lg:flex fixed left-0 top-0 bottom-0 w-64 flex-col bg-white border-r border-slate-200 z-30">
            <div className="px-6 pt-6 pb-4">
                <SidebarBrand />
            </div>
            <nav className="flex-1 overflow-y-auto px-3 pb-24 mt-2">
                <SidebarNav />
            </nav>
        </aside>
    );
}

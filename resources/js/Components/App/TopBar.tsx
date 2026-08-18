import { Link, usePage } from '@inertiajs/react';
import { Bell, Search, User as UserIcon } from 'lucide-react';
import type { PageProps } from '@/types';

export default function TopBar() {
    const {
        props: { auth },
    } = usePage<PageProps>();

    return (
        <div className="sticky top-0 z-20 -mx-4 md:-mx-6 lg:-mx-8 mb-6 border-b border-slate-200 bg-white/85 backdrop-blur px-4 md:px-6 lg:px-8">
            <div className="flex items-center gap-4 h-16">
                {/* Search */}
                <label className="flex-1 max-w-2xl flex items-center gap-2 rounded-full bg-slate-100 hover:bg-slate-50 focus-within:bg-white focus-within:ring-2 focus-within:ring-brand-500 border border-slate-200 px-4 h-10 transition-colors">
                    <Search className="h-4 w-4 text-slate-500" />
                    <input
                        type="search"
                        placeholder="Search songs, artists, groups, choirs, churches, hymns…"
                        className="flex-1 bg-transparent text-sm text-ink placeholder:text-slate-400 border-0 focus:ring-0 focus:outline-none"
                        aria-label="Search"
                    />
                </label>

                <div className="flex items-center gap-2">
                    <button
                        type="button"
                        className="h-10 w-10 rounded-full flex items-center justify-center text-slate-500 hover:text-ink hover:bg-slate-100 transition-colors"
                        aria-label="Notifications"
                    >
                        <Bell className="h-5 w-5" />
                    </button>

                    {auth?.user ? (
                        <Link
                            href="/dashboard"
                            className="flex items-center gap-2 pl-1 pr-3 py-1 rounded-full hover:bg-slate-100 transition-colors"
                        >
                            <span className="h-8 w-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-sm font-semibold">
                                {auth.user.name.charAt(0).toUpperCase()}
                            </span>
                            <span className="hidden sm:block text-sm font-medium text-ink">
                                {auth.user.name.split(' ')[0]}
                            </span>
                        </Link>
                    ) : (
                        <div className="flex items-center gap-2">
                            <Link
                                href="/login"
                                className="text-sm font-medium text-slate-700 hover:text-ink px-3 py-1.5"
                            >
                                Sign in
                            </Link>
                            <Link
                                href="/register"
                                className="text-sm font-semibold text-white bg-brand-600 hover:bg-brand-700 px-4 py-1.5 rounded-full"
                            >
                                Sign up
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

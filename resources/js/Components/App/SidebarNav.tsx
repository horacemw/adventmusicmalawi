import { Link, usePage } from '@inertiajs/react';
import clsx from 'clsx';
import {
    Album,
    BookOpen,
    Calendar,
    Church,
    Compass,
    Crown,
    Disc3,
    Flame,
    HeartHandshake,
    Info,
    Mail,
    Mic,
    Music2,
    Music4,
    Piano,
    Rocket,
    Settings,
    Sparkles,
    Sun,
    Users,
    Users2,
} from 'lucide-react';
import type { ComponentType } from 'react';

interface NavItem {
    label: string;
    href: string;
    icon: ComponentType<{ className?: string }>;
    match?: string;
}

interface NavSection {
    label?: string;
    items: NavItem[];
}

const sections: NavSection[] = [
    {
        label: 'Menu',
        items: [
            { label: 'Discover', href: '/', icon: Compass, match: '/' },
            { label: 'Songs', href: '/songs', icon: Music2 },
            { label: 'Albums', href: '/albums', icon: Disc3 },
            { label: 'Artists', href: '/artists', icon: Mic },
            { label: 'Groups & Choirs', href: '/groups', icon: Users2 },
            { label: 'Churches', href: '/churches', icon: Church },
            { label: 'Playlists', href: '/playlists', icon: Album },
        ],
    },
    {
        label: 'Discover',
        items: [
            { label: 'Occasions', href: '/occasions', icon: Calendar },
            { label: 'Hymn Books', href: '/hymn-books', icon: BookOpen },
            { label: 'Youth Music', href: '/discover', icon: Users },
            { label: 'Acapella', href: '/discover', icon: Music4 },
            { label: "Children's Music", href: '/discover', icon: Sparkles },
            { label: 'Instrumental', href: '/discover', icon: Piano },
            { label: 'Trending', href: '/trending', icon: Flame },
            { label: 'Top 100', href: '/top-100', icon: Crown },
            { label: 'Wedding', href: '/occasions', icon: HeartHandshake },
        ],
    },
    {
        label: 'General',
        items: [
            { label: 'About', href: '/about', icon: Info },
            { label: 'Contact', href: '/contact', icon: Mail },
            { label: 'Settings', href: '/settings', icon: Settings },
        ],
    },
];

export function SidebarBrand() {
    return (
        <Link href="/" className="flex items-center gap-2.5">
            <span className="relative h-9 w-9 rounded-xl bg-brand-600 flex items-center justify-center shadow-card">
                <Music2 className="h-5 w-5 text-white" />
            </span>
            <span className="flex flex-col leading-tight">
                <span className="text-[11px] font-semibold uppercase tracking-widest text-brand-700">
                    Malawi
                </span>
                <span className="text-sm font-semibold text-ink">Adventist Music</span>
            </span>
        </Link>
    );
}

export function SidebarNav() {
    const { url } = usePage();

    return (
        <>
            {sections.map((section, idx) => (
                <div key={idx} className="mb-6">
                    {section.label && (
                        <p className="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            {section.label}
                        </p>
                    )}
                    <ul className="space-y-1">
                        {section.items.map((item) => {
                            const isActive =
                                item.match !== undefined
                                    ? url === item.match
                                    : url.startsWith(item.href);
                            const Icon = item.icon;
                            return (
                                <li key={item.label}>
                                    <Link
                                        href={item.href}
                                        className={clsx(
                                            'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition-colors',
                                            isActive
                                                ? 'bg-brand-50 text-brand-700 font-semibold'
                                                : 'text-slate-600 hover:bg-slate-50 hover:text-ink',
                                        )}
                                    >
                                        <Icon
                                            className={clsx(
                                                'h-4 w-4 shrink-0',
                                                isActive ? 'text-brand-600' : 'text-slate-400',
                                            )}
                                        />
                                        <span className="truncate">{item.label}</span>
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </div>
            ))}

            <div className="mx-3 mt-4 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 p-4 text-white">
                <div className="flex items-center gap-2 mb-2">
                    <Rocket className="h-4 w-4" />
                    <span className="text-[11px] font-semibold uppercase tracking-wider">
                        Free
                    </span>
                </div>
                <p className="text-sm font-semibold leading-snug mb-1">
                    Malawian Music Streaming
                </p>
                <p className="text-[12px] text-brand-50/90 leading-relaxed">
                    Discover choirs and songs from across the country. Always free for
                    listeners.
                </p>
                <Link
                    href="/discover"
                    className="mt-3 inline-flex items-center gap-1 text-[12px] font-semibold text-white/95 hover:text-white"
                >
                    Explore
                    <Sun className="h-3.5 w-3.5" />
                </Link>
            </div>
        </>
    );
}

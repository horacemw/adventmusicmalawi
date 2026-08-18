import { Link } from '@inertiajs/react';
import {
    BookOpen,
    Heart,
    Mic,
    Music,
    Music2,
    Sparkles,
    Sun,
    User as UserIcon,
    Users,
} from 'lucide-react';
import type { ComponentType } from 'react';
import type { CategoryChip } from '@/types';

// Fallback icon map by name (icons on the category rows in the reference are round emoji-style).
const ICONS: Record<string, ComponentType<{ className?: string }>> = {
    hymns: BookOpen,
    worship: Heart,
    acapella: Mic,
    children: Sparkles,
    "children's music": Sparkles,
    youth: Users,
    'youth music': Users,
    wedding: Heart,
    marriage: Users,
    sad: Music,
    comfort: Sun,
    gospel: Music2,
    contemporary: Sparkles,
    instrumental: Music,
    quartet: UserIcon,
    'sabbath songs': Sun,
    evangelism: Music2,
    'special music': Sparkles,
    'choir music': Users,
    'solo music': UserIcon,
};

const COLORS = [
    'from-brand-100 to-brand-200 text-brand-700',
    'from-purple-100 to-purple-200 text-purple-700',
    'from-amber-100 to-amber-200 text-amber-700',
    'from-sky-100 to-sky-200 text-sky-700',
    'from-rose-100 to-rose-200 text-rose-700',
    'from-emerald-100 to-emerald-200 text-emerald-700',
    'from-orange-100 to-orange-200 text-orange-700',
    'from-slate-100 to-slate-200 text-slate-700',
    'from-cyan-100 to-cyan-200 text-cyan-700',
];

export default function CategoryChips({ chips }: { chips: CategoryChip[] }) {
    return (
        <section className="mt-6">
            <div className="flex items-center gap-4 md:gap-6 overflow-x-auto scrollbar-none pb-2 -mx-1 px-1">
                {chips.map((chip, idx) => {
                    const key = chip.name.toLowerCase();
                    const Icon = ICONS[key] ?? Music;
                    const color = COLORS[idx % COLORS.length];
                    return (
                        <Link
                            key={chip.id}
                            href={`/discover?category=${chip.slug}`}
                            className="group flex flex-col items-center gap-2 shrink-0"
                        >
                            <span
                                className={`h-14 w-14 md:h-16 md:w-16 rounded-full bg-gradient-to-br ${color} flex items-center justify-center shadow-card group-hover:scale-105 transition-transform`}
                            >
                                <Icon className="h-6 w-6" />
                            </span>
                            <span className="text-xs md:text-sm font-medium text-ink text-center max-w-[80px] leading-tight">
                                {chip.name}
                            </span>
                        </Link>
                    );
                })}
            </div>
        </section>
    );
}

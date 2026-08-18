import { Link } from '@inertiajs/react';
import { Users } from 'lucide-react';
import type { FeaturedGroup } from '@/types';
import SectionHeader from './SectionHeader';

export default function FeaturedGroups({ items }: { items: FeaturedGroup[] }) {
    if (items.length === 0) return null;

    return (
        <section>
            <SectionHeader title="Popular Groups & Choirs" href="/groups" />
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                {items.map((g) => (
                    <Link
                        key={g.id}
                        href={`/groups/${g.slug}`}
                        className="group rounded-2xl bg-white border border-slate-200 p-3 hover:shadow-card-hover hover:border-slate-300 transition-all flex items-center gap-3"
                    >
                        <div className="h-12 w-12 rounded-full bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0 overflow-hidden">
                            {g.image ? (
                                <img
                                    src={g.image}
                                    alt=""
                                    className="w-full h-full object-cover"
                                />
                            ) : (
                                <Users className="h-5 w-5 text-white/80" />
                            )}
                        </div>
                        <div className="min-w-0">
                            <p className="text-sm font-semibold text-ink line-clamp-1">
                                {g.name}
                            </p>
                            <p className="text-xs text-slate-500 capitalize">
                                {g.type.replace('_', ' ')}
                            </p>
                        </div>
                    </Link>
                ))}
            </div>
        </section>
    );
}

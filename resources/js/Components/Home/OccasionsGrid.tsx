import { Link } from '@inertiajs/react';
import { Calendar } from 'lucide-react';
import type { OccasionCard } from '@/types';
import SectionHeader from './SectionHeader';

const GRADIENTS = [
    'from-rose-400 to-pink-600',
    'from-amber-400 to-orange-600',
    'from-sky-400 to-blue-600',
    'from-emerald-400 to-teal-600',
    'from-violet-400 to-purple-600',
    'from-fuchsia-400 to-pink-600',
];

export default function OccasionsGrid({ items }: { items: OccasionCard[] }) {
    if (items.length === 0) return null;

    return (
        <section>
            <SectionHeader title="Browse by Occasion" href="/occasions" />
            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                {items.map((occ, idx) => (
                    <Link
                        key={occ.id}
                        href={`/occasions/${occ.slug}`}
                        className={`group relative overflow-hidden rounded-2xl aspect-[4/3] flex items-end p-4 text-white shadow-card hover:shadow-card-hover transition-shadow bg-gradient-to-br ${GRADIENTS[idx % GRADIENTS.length]}`}
                    >
                        {occ.image ? (
                            <img
                                src={occ.image}
                                alt=""
                                className="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-80 transition-opacity"
                            />
                        ) : (
                            <div className="absolute inset-0 flex items-center justify-center opacity-30">
                                <Calendar className="h-16 w-16" />
                            </div>
                        )}
                        <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
                        <div className="relative">
                            <p className="text-xs uppercase tracking-wider text-white/80 mb-1">
                                Occasion
                            </p>
                            <p className="text-sm md:text-base font-semibold">{occ.name}</p>
                        </div>
                    </Link>
                ))}
            </div>
        </section>
    );
}

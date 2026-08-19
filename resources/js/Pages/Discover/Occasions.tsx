import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';

interface OccasionItem { id: number; name: string; slug: string; description: string | null; image: string | null; songs_count: number; }
interface Props { occasions: OccasionItem[]; }

export default function Occasions({ occasions }: Props) {
    return (
        <AppLayout title="Occasions">
            <PageHeader title="Occasions" subtitle="Music for every moment in worship." />
            {occasions.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No occasions defined yet.
                </div>
            ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    {occasions.map((o) => (
                        <Link
                            key={o.id}
                            href={`/occasions/${o.slug}`}
                            className="group relative aspect-[4/3] rounded-2xl overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700"
                        >
                            {o.image && <img src={o.image} alt="" className="absolute inset-0 w-full h-full object-cover opacity-70 group-hover:opacity-90 transition-opacity" />}
                            <div className="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent" />
                            <div className="absolute bottom-4 left-4 right-4 text-white">
                                <p className="font-bold text-lg">{o.name}</p>
                                <p className="text-xs text-white/70">{o.songs_count} song{o.songs_count === 1 ? '' : 's'}</p>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </AppLayout>
    );
}

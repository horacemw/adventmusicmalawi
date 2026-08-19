import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import Pagination from '@/Components/Discover/Pagination';
import SongRow from '@/Components/Discover/SongRow';
import type { SongPayload } from '@/types';

interface Props {
    occasion: { id: number; name: string; slug: string; description: string | null; image: string | null };
    songs: {
        data: SongPayload[];
        meta: { current_page: number; last_page: number; total: number };
    };
}

export default function OccasionShow({ occasion, songs }: Props) {
    return (
        <AppLayout title={occasion.name}>
            <section className="relative rounded-3xl overflow-hidden mb-6 bg-gradient-to-br from-brand-600 to-brand-800 aspect-[3/1] md:aspect-[4/1]">
                {occasion.image && <img src={occasion.image} alt="" className="absolute inset-0 w-full h-full object-cover opacity-40" />}
                <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent" />
                <div className="absolute bottom-6 left-6 right-6 text-white">
                    <h1 className="text-3xl md:text-4xl font-bold">{occasion.name}</h1>
                    {occasion.description && <p className="mt-1 text-white/80 max-w-2xl text-sm md:text-base">{occasion.description}</p>}
                </div>
            </section>

            <PageHeader title="Songs" count={songs.meta.total} />

            {songs.data.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No songs tagged with this occasion yet.
                </div>
            ) : (
                <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                    {songs.data.map((s, i) => (
                        <SongRow key={s.id} song={s} index={i} queue={songs.data} showRank={false} />
                    ))}
                </ul>
            )}
            <Pagination meta={songs.meta} />
        </AppLayout>
    );
}

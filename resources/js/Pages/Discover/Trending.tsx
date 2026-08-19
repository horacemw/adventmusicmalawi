import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import SongRow from '@/Components/Discover/SongRow';
import type { SongPayload } from '@/types';

interface Props { items: SongPayload[]; }

export default function Trending({ items }: Props) {
    return (
        <AppLayout title="Trending">
            <PageHeader title="Trending" count={items.length} subtitle="Songs picking up plays right now." />
            {items.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    Nothing trending yet.
                </div>
            ) : (
                <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                    {items.map((s, i) => (
                        <SongRow key={s.id} song={s} index={i} queue={items} />
                    ))}
                </ul>
            )}
        </AppLayout>
    );
}

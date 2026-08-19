import { router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import Pagination from '@/Components/Discover/Pagination';
import SearchFilterBar from '@/Components/Discover/SearchFilterBar';
import SongRow from '@/Components/Discover/SongRow';
import type { SongPayload } from '@/types';

interface Facet { id: number; name: string; slug: string; }
interface Props {
    filters: { q: string; genre: string; language: string; category: string; occasion: string; sort: string };
    facets: { genres: Facet[]; languages: Facet[]; categories: Facet[]; occasions: Facet[] };
    songs: {
        data: SongPayload[];
        meta: { current_page: number; last_page: number; total: number; per_page: number };
    };
}

export default function DiscoverSongs({ filters, facets, songs }: Props) {
    const setFilter = (key: string, value: string) => {
        const next = { ...filters, [key]: value === filters[key as keyof typeof filters] ? '' : value };
        router.get('/songs', Object.fromEntries(Object.entries(next).filter(([, v]) => v)), {
            preserveState: true, preserveScroll: true, replace: true,
        });
    };

    return (
        <AppLayout title="Songs">
            <PageHeader title="Songs" count={songs.meta.total} subtitle="Every published song on the platform." />
            <SearchFilterBar initialQuery={filters.q} placeholder="Search songs by title…" routeName="/songs" extraParams={{ genre: filters.genre, language: filters.language, category: filters.category, occasion: filters.occasion, sort: filters.sort }} />

            <div className="grid grid-cols-1 lg:grid-cols-[220px_1fr] gap-6">
                <aside className="space-y-5">
                    <FilterGroup title="Sort" items={[
                        { value: 'newest', label: 'Newest' },
                        { value: 'popular', label: 'Most played' },
                        { value: 'oldest', label: 'Oldest' },
                    ]} active={filters.sort} onSelect={(v) => setFilter('sort', v)} />
                    <FilterGroup title="Genre" items={facets.genres.map((f) => ({ value: f.slug, label: f.name }))} active={filters.genre} onSelect={(v) => setFilter('genre', v)} />
                    <FilterGroup title="Language" items={facets.languages.map((f) => ({ value: f.slug, label: f.name }))} active={filters.language} onSelect={(v) => setFilter('language', v)} />
                    <FilterGroup title="Category" items={facets.categories.map((f) => ({ value: f.slug, label: f.name }))} active={filters.category} onSelect={(v) => setFilter('category', v)} />
                    <FilterGroup title="Occasion" items={facets.occasions.map((f) => ({ value: f.slug, label: f.name }))} active={filters.occasion} onSelect={(v) => setFilter('occasion', v)} />
                </aside>

                <main>
                    {songs.data.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                            No songs match these filters.
                        </div>
                    ) : (
                        <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                            {songs.data.map((s, i) => (
                                <SongRow key={s.id} song={s} index={i} queue={songs.data} showRank={false} />
                            ))}
                        </ul>
                    )}
                    <Pagination meta={songs.meta} />
                </main>
            </div>
        </AppLayout>
    );
}

interface FilterGroupProps {
    title: string;
    items: { value: string; label: string }[];
    active: string;
    onSelect: (v: string) => void;
}

function FilterGroup({ title, items, active, onSelect }: FilterGroupProps) {
    if (items.length === 0) return null;
    return (
        <div>
            <h3 className="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">{title}</h3>
            <div className="space-y-1">
                {items.map((it) => (
                    <button
                        key={it.value}
                        onClick={() => onSelect(it.value)}
                        className={`w-full text-left px-3 py-1.5 rounded-lg text-sm transition-colors ${active === it.value ? 'bg-brand-100 text-brand-800 font-medium' : 'text-slate-700 hover:bg-slate-100'}`}
                    >
                        {it.label}
                    </button>
                ))}
            </div>
        </div>
    );
}

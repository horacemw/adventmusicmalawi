import { router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import Pagination from '@/Components/Discover/Pagination';
import SearchFilterBar from '@/Components/Discover/SearchFilterBar';
import PoemCard from '@/Components/Discover/PoemCard';

interface Poem {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    image: string | null;
    author: string;
    category: string | null;
    language: string | null;
    published_at: string | null;
}
interface Facet { id: number; name: string; slug: string; }
interface Props {
    filters: { q: string; category: string; language: string; sort: string };
    facets: { categories: Facet[]; languages: Facet[] };
    poems: {
        data: Poem[];
        meta: { current_page: number; last_page: number; total: number };
    };
}

export default function DiscoverPoems({ filters, facets, poems }: Props) {
    const setFilter = (key: string, value: string) => {
        const next = { ...filters, [key]: value === filters[key as keyof typeof filters] ? '' : value };
        router.get('/poems', Object.fromEntries(Object.entries(next).filter(([, v]) => v)), {
            preserveState: true, preserveScroll: true, replace: true,
        });
    };

    return (
        <AppLayout title="Poems">
            <PageHeader title="Poems" count={poems.meta.total} subtitle="Spoken and written verse from Adventist voices across Malawi." />
            <SearchFilterBar initialQuery={filters.q} placeholder="Search poems by title…" routeName="/poems" extraParams={{ category: filters.category, language: filters.language, sort: filters.sort }} />

            <div className="grid grid-cols-1 lg:grid-cols-[220px_1fr] gap-6">
                <aside className="space-y-5">
                    <FilterGroup title="Sort" items={[
                        { value: 'newest', label: 'Newest' },
                        { value: 'popular', label: 'Most read' },
                        { value: 'featured', label: 'Featured first' },
                    ]} active={filters.sort} onSelect={(v) => setFilter('sort', v)} />
                    <FilterGroup title="Category" items={facets.categories.map((f) => ({ value: f.slug, label: f.name }))} active={filters.category} onSelect={(v) => setFilter('category', v)} />
                    <FilterGroup title="Language" items={facets.languages.map((f) => ({ value: f.slug, label: f.name }))} active={filters.language} onSelect={(v) => setFilter('language', v)} />
                </aside>

                <main>
                    {poems.data.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                            No poems match these filters.
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            {poems.data.map((p) => (
                                <PoemCard key={p.id} poem={p} />
                            ))}
                        </div>
                    )}
                    <Pagination meta={poems.meta} />
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

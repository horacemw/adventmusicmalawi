import { router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import Pagination from '@/Components/Discover/Pagination';
import SearchFilterBar from '@/Components/Discover/SearchFilterBar';
import EntityCard from '@/Components/Discover/EntityCard';

interface GroupCard { id: number; name: string; slug: string; type: string; image: string | null; is_verified: boolean; church: string | null; songs_count: number; }
interface TypeOption { value: string; label: string; }
interface Props {
    filters: { q: string; type: string; sort: string };
    types: TypeOption[];
    groups: {
        data: GroupCard[];
        meta: { current_page: number; last_page: number; total: number };
    };
}

export default function DiscoverGroups({ filters, types, groups }: Props) {
    const setType = (v: string) => {
        const next = { ...filters, type: v === filters.type ? '' : v };
        router.get('/groups', Object.fromEntries(Object.entries(next).filter(([, val]) => val)), {
            preserveState: true, preserveScroll: true, replace: true,
        });
    };

    return (
        <AppLayout title="Groups & Choirs">
            <PageHeader title="Groups & Choirs" count={groups.meta.total} subtitle="Choirs, quartets, family groups and church music teams." />
            <SearchFilterBar initialQuery={filters.q} placeholder="Search groups…" routeName="/groups" extraParams={{ type: filters.type, sort: filters.sort }} />

            <div className="mb-5 flex flex-wrap gap-2">
                {types.map((t) => (
                    <button
                        key={t.value}
                        onClick={() => setType(t.value)}
                        className={`px-4 py-1.5 rounded-full text-sm font-medium border transition-colors ${filters.type === t.value ? 'bg-brand-600 border-brand-600 text-white' : 'bg-white border-slate-200 text-slate-700 hover:border-brand-400'}`}
                    >
                        {t.label}
                    </button>
                ))}
            </div>

            {groups.data.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No groups match these filters.
                </div>
            ) : (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    {groups.data.map((g) => (
                        <EntityCard
                            key={g.id}
                            href={`/groups/${g.slug}`}
                            name={g.name}
                            image={g.image}
                            subtitle={g.church || g.type}
                            verified={g.is_verified}
                        />
                    ))}
                </div>
            )}
            <Pagination meta={groups.meta} />
        </AppLayout>
    );
}

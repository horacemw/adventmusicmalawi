import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import Pagination from '@/Components/Discover/Pagination';
import SearchFilterBar from '@/Components/Discover/SearchFilterBar';
import EntityCard from '@/Components/Discover/EntityCard';

interface ChurchCard { id: number; name: string; slug: string; image: string | null; is_verified: boolean; region: string | null; district: string | null; groups_count: number; }
interface Props {
    filters: { q: string };
    churches: {
        data: ChurchCard[];
        meta: { current_page: number; last_page: number; total: number };
    };
}

export default function DiscoverChurches({ filters, churches }: Props) {
    return (
        <AppLayout title="Churches">
            <PageHeader title="Churches" count={churches.meta.total} subtitle="SDA churches with music groups on the platform." />
            <SearchFilterBar initialQuery={filters.q} placeholder="Search churches…" routeName="/churches" />

            {churches.data.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No churches yet.
                </div>
            ) : (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    {churches.data.map((c) => (
                        <EntityCard
                            key={c.id}
                            href={`/churches/${c.slug}`}
                            name={c.name}
                            image={c.image}
                            subtitle={[c.district, c.region].filter(Boolean).join(' · ') || null}
                            verified={c.is_verified}
                        />
                    ))}
                </div>
            )}
            <Pagination meta={churches.meta} />
        </AppLayout>
    );
}

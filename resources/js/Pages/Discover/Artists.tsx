import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import Pagination from '@/Components/Discover/Pagination';
import SearchFilterBar from '@/Components/Discover/SearchFilterBar';
import EntityCard from '@/Components/Discover/EntityCard';

interface ArtistCard { id: number; name: string; slug: string; image: string | null; is_verified: boolean; songs_count: number; }
interface Props {
    filters: { q: string; sort: string };
    artists: {
        data: ArtistCard[];
        meta: { current_page: number; last_page: number; total: number };
    };
}

export default function DiscoverArtists({ filters, artists }: Props) {
    return (
        <AppLayout title="Artists">
            <PageHeader title="Artists" count={artists.meta.total} subtitle="Solo vocalists and instrumentalists across the platform." />
            <SearchFilterBar initialQuery={filters.q} placeholder="Search artists…" routeName="/artists" extraParams={{ sort: filters.sort }} />

            {artists.data.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No artists yet.
                </div>
            ) : (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    {artists.data.map((a) => (
                        <EntityCard
                            key={a.id}
                            href={`/artists/${a.slug}`}
                            name={a.name}
                            image={a.image}
                            subtitle={`${a.songs_count} song${a.songs_count === 1 ? '' : 's'}`}
                            verified={a.is_verified}
                            aspect="portrait"
                        />
                    ))}
                </div>
            )}
            <Pagination meta={artists.meta} />
        </AppLayout>
    );
}

import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import Pagination from '@/Components/Discover/Pagination';
import SearchFilterBar from '@/Components/Discover/SearchFilterBar';
import EntityCard from '@/Components/Discover/EntityCard';

interface AlbumCard { id: number; title: string; slug: string; artwork: string | null; artist: string; year: number | null; songs_count: number | null; }
interface Props {
    filters: { q: string; sort: string };
    albums: {
        data: AlbumCard[];
        meta: { current_page: number; last_page: number; total: number };
    };
}

export default function DiscoverAlbums({ filters, albums }: Props) {
    return (
        <AppLayout title="Albums">
            <PageHeader title="Albums" count={albums.meta.total} subtitle="Full collections from artists, choirs and churches." />
            <SearchFilterBar initialQuery={filters.q} placeholder="Search albums…" routeName="/albums" extraParams={{ sort: filters.sort }} />

            {albums.data.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No albums yet.
                </div>
            ) : (
                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    {albums.data.map((a) => (
                        <EntityCard
                            key={a.id}
                            href={`/albums/${a.slug}`}
                            name={a.title}
                            image={a.artwork}
                            subtitle={`${a.artist}${a.year ? ` · ${a.year}` : ''}`}
                        />
                    ))}
                </div>
            )}
            <Pagination meta={{ current_page: albums.meta.current_page, last_page: albums.meta.last_page, total: albums.meta.total }} />
        </AppLayout>
    );
}

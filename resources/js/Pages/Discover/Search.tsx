import { useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Discover/PageHeader';
import SongRow from '@/Components/Discover/SongRow';
import EntityCard from '@/Components/Discover/EntityCard';
import { Search as SearchIcon } from 'lucide-react';
import type { SongPayload } from '@/types';

interface AlbumCard { id: number; title: string; slug: string; artwork: string | null; artist: string; year: number | null; songs_count: number | null; }
interface ArtistCard { id: number; name: string; slug: string; image: string | null; is_verified: boolean; }
interface GroupCard { id: number; name: string; slug: string; type: string; image: string | null; }
interface ChurchCard { id: number; name: string; slug: string; image: string | null; }

interface Props {
    query: string;
    results: {
        songs: SongPayload[];
        albums: AlbumCard[];
        artists: ArtistCard[];
        groups: GroupCard[];
        churches: ChurchCard[];
    };
}

export default function Search({ query, results }: Props) {
    const [q, setQ] = useState(query);
    const debounced = useRef<number | null>(null);

    useEffect(() => {
        if (debounced.current) window.clearTimeout(debounced.current);
        debounced.current = window.setTimeout(() => {
            if (q === query) return;
            router.get('/search', { q: q || undefined }, { preserveState: true, preserveScroll: true, replace: true });
        }, 300);
        return () => {
            if (debounced.current) window.clearTimeout(debounced.current);
        };
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [q]);

    const total = results.songs.length + results.albums.length + results.artists.length + results.groups.length + results.churches.length;
    const hasQuery = query.length >= 2;

    return (
        <AppLayout title={hasQuery ? `Search: ${query}` : 'Search'}>
            <PageHeader title="Search" subtitle={hasQuery ? `${total} result${total === 1 ? '' : 's'} for “${query}”` : 'Find songs, albums, artists, choirs and churches.'} />

            <div className="mb-6 relative">
                <SearchIcon className="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" />
                <input
                    autoFocus
                    type="search"
                    value={q}
                    onChange={(e) => setQ(e.target.value)}
                    placeholder="Search…"
                    className="w-full h-14 pl-12 pr-4 rounded-2xl border border-slate-200 bg-white text-base placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-transparent"
                />
            </div>

            {!hasQuery && (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    Start typing to search — minimum 2 characters.
                </div>
            )}

            {hasQuery && total === 0 && (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    Nothing found. Try a different search term.
                </div>
            )}

            {hasQuery && results.songs.length > 0 && (
                <section className="mb-6">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Songs</h2>
                    <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                        {results.songs.map((s, i) => (
                            <SongRow key={s.id} song={s} index={i} queue={results.songs} showRank={false} />
                        ))}
                    </ul>
                </section>
            )}

            {hasQuery && results.albums.length > 0 && (
                <section className="mb-6">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Albums</h2>
                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        {results.albums.map((a) => (
                            <EntityCard key={a.id} href={`/albums/${a.slug}`} name={a.title} image={a.artwork} subtitle={a.artist} />
                        ))}
                    </div>
                </section>
            )}

            {hasQuery && results.artists.length > 0 && (
                <section className="mb-6">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Artists</h2>
                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        {results.artists.map((a) => (
                            <EntityCard key={a.id} href={`/artists/${a.slug}`} name={a.name} image={a.image} verified={a.is_verified} aspect="portrait" />
                        ))}
                    </div>
                </section>
            )}

            {hasQuery && results.groups.length > 0 && (
                <section className="mb-6">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Groups & Choirs</h2>
                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        {results.groups.map((g) => (
                            <EntityCard key={g.id} href={`/groups/${g.slug}`} name={g.name} image={g.image} subtitle={g.type} />
                        ))}
                    </div>
                </section>
            )}

            {hasQuery && results.churches.length > 0 && (
                <section className="mb-6">
                    <h2 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Churches</h2>
                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        {results.churches.map((c) => (
                            <EntityCard key={c.id} href={`/churches/${c.slug}`} name={c.name} image={c.image} />
                        ))}
                    </div>
                </section>
            )}
        </AppLayout>
    );
}

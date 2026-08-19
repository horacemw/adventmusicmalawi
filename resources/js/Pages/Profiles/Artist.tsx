import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import SongRow from '@/Components/Discover/SongRow';
import EntityCard from '@/Components/Discover/EntityCard';
import { BadgeCheck, User } from 'lucide-react';
import type { SongPayload } from '@/types';

interface ArtistDetail {
    id: number;
    name: string;
    real_name: string;
    slug: string;
    bio: string | null;
    image: string | null;
    cover: string | null;
    is_verified: boolean;
    is_featured: boolean;
    social_links: Record<string, string>;
    church: { name: string; slug: string } | null;
    region: string | null;
    district: string | null;
}
interface AlbumCard { id: number; title: string; slug: string; artwork: string | null; year: number | null; }
interface Props { artist: ArtistDetail; songs: SongPayload[]; albums: AlbumCard[]; }

export default function ArtistDetail({ artist, songs, albums }: Props) {
    return (
        <AppLayout title={artist.name}>
            <section className="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-900 text-white p-6 md:p-10 mb-8 relative overflow-hidden">
                {artist.cover && <img src={artist.cover} alt="" className="absolute inset-0 w-full h-full object-cover opacity-30" />}
                <div className="relative flex flex-col md:flex-row gap-6 items-start">
                    <div className="w-32 h-32 md:w-40 md:h-40 rounded-full overflow-hidden bg-black/20 shrink-0 border-4 border-white/20">
                        {artist.image ? (
                            <img src={artist.image} alt="" className="w-full h-full object-cover" />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center">
                                <User className="h-12 w-12 text-white/50" />
                            </div>
                        )}
                    </div>
                    <div className="flex-1 min-w-0">
                        <p className="text-xs uppercase tracking-wider text-white/70 mb-1">Artist</p>
                        <h1 className="text-3xl md:text-5xl font-bold mb-1 break-words flex items-center gap-2">
                            {artist.name}
                            {artist.is_verified && <BadgeCheck className="h-6 w-6 text-brand-200" />}
                        </h1>
                        {artist.name !== artist.real_name && <p className="text-white/70 text-sm">{artist.real_name}</p>}
                        <p className="text-xs text-white/70 mt-3">
                            {[artist.church?.name, artist.district, artist.region].filter(Boolean).join(' · ')}
                        </p>
                    </div>
                </div>
            </section>

            {artist.bio && (
                <section className="rounded-2xl bg-white border border-slate-200 p-5 mb-6">
                    <p className="text-sm text-ink whitespace-pre-wrap">{artist.bio}</p>
                </section>
            )}

            {songs.length > 0 && (
                <section className="mb-8">
                    <h2 className="text-lg font-bold text-ink mb-3">Songs</h2>
                    <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                        {songs.map((s, i) => (
                            <SongRow key={s.id} song={s} index={i} queue={songs} showRank={false} />
                        ))}
                    </ul>
                </section>
            )}

            {albums.length > 0 && (
                <section>
                    <h2 className="text-lg font-bold text-ink mb-3">Albums</h2>
                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        {albums.map((a) => (
                            <EntityCard key={a.id} href={`/albums/${a.slug}`} name={a.title} image={a.artwork} subtitle={a.year ? String(a.year) : null} />
                        ))}
                    </div>
                </section>
            )}

            {songs.length === 0 && albums.length === 0 && (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No published music yet.
                </div>
            )}
        </AppLayout>
    );
}

import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import SongRow from '@/Components/Discover/SongRow';
import { Music2, Play } from 'lucide-react';
import { usePlayer } from '@/Contexts/PlayerContext';
import type { SongPayload } from '@/types';

interface AlbumDetail {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    artwork: string | null;
    release_year: number | null;
    released_at: string | null;
    label: string | null;
    language: string | null;
    is_featured: boolean;
    artist: { name: string; slug: string; image: string | null } | null;
    group: { name: string; slug: string; image: string | null } | null;
    church: { name: string; slug: string } | null;
}

interface Props { album: AlbumDetail; songs: SongPayload[]; }

export default function AlbumDetail({ album, songs }: Props) {
    const player = usePlayer();

    const owner = album.group
        ? { name: album.group.name, href: `/groups/${album.group.slug}` }
        : album.artist
            ? { name: album.artist.name, href: `/artists/${album.artist.slug}` }
            : album.church
                ? { name: album.church.name, href: `/churches/${album.church.slug}` }
                : null;

    return (
        <AppLayout title={album.title}>
            <section className="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-900 text-white p-6 md:p-10 mb-8">
                <div className="flex flex-col md:flex-row gap-6 items-start">
                    <div className="w-40 h-40 md:w-52 md:h-52 rounded-2xl overflow-hidden bg-black/20 shrink-0">
                        {album.artwork ? (
                            <img src={album.artwork} alt="" className="w-full h-full object-cover" />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center">
                                <Music2 className="h-12 w-12 text-white/50" />
                            </div>
                        )}
                    </div>
                    <div className="flex-1 min-w-0">
                        <p className="text-xs uppercase tracking-wider text-white/70 mb-1">Album</p>
                        <h1 className="text-3xl md:text-5xl font-bold mb-2 break-words">{album.title}</h1>
                        {owner && <p className="text-white/90 text-sm md:text-base mb-4"><Link href={owner.href} className="hover:underline">{owner.name}</Link></p>}
                        <p className="text-xs text-white/70 mb-6">
                            {album.release_year && <>{album.release_year} · </>}
                            {songs.length} track{songs.length === 1 ? '' : 's'}
                            {album.language && <> · {album.language}</>}
                            {album.label && <> · {album.label}</>}
                        </p>
                        {songs.length > 0 && (
                            <button
                                onClick={() => player.play(songs[0], songs.slice(1))}
                                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white text-brand-700 font-semibold hover:bg-brand-50 transition-colors"
                            >
                                <Play className="h-4 w-4" /> Play album
                            </button>
                        )}
                    </div>
                </div>
            </section>

            {album.description && (
                <section className="rounded-2xl bg-white border border-slate-200 p-5 mb-6">
                    <p className="text-sm text-ink whitespace-pre-wrap">{album.description}</p>
                </section>
            )}

            {songs.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                    No tracks published yet.
                </div>
            ) : (
                <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                    {songs.map((s, i) => (
                        <SongRow key={s.id} song={s} index={i} queue={songs} showRank />
                    ))}
                </ul>
            )}
        </AppLayout>
    );
}

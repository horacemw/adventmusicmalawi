import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import SongRow from '@/Components/Discover/SongRow';
import { usePlayer, formatDuration } from '@/Contexts/PlayerContext';
import { Download, Music2, Play } from 'lucide-react';
import type { SongPayload } from '@/types';

interface SongDetail {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    lyrics: string | null;
    artwork: string | null;
    audio: string | null;
    duration: number | null;
    streams: number;
    likes: number;
    downloads: number;
    allow_download: boolean;
    is_featured: boolean;
    released_at: string | null;
    release_year: number | null;
    group: { name: string; slug: string; image: string | null } | null;
    artist: { name: string; slug: string; image: string | null } | null;
    church: { name: string; slug: string } | null;
    album: { title: string; slug: string; artwork: string | null } | null;
    language: string | null;
    genre: string | null;
    categories: { name: string; slug: string }[];
    occasions: { name: string; slug: string }[];
    featured_artists: { name: string; slug: string }[];
}

interface Props {
    song: SongDetail;
    payload: SongPayload;
    related: SongPayload[];
}

export default function SongDetail({ song, payload, related }: Props) {
    const player = usePlayer();

    return (
        <AppLayout title={song.title}>
            <section className="rounded-3xl bg-gradient-to-br from-brand-600 to-brand-900 text-white p-6 md:p-10 mb-8">
                <div className="flex flex-col md:flex-row gap-6 items-start">
                    <div className="w-40 h-40 md:w-52 md:h-52 rounded-2xl overflow-hidden bg-black/20 shrink-0">
                        {song.artwork ? (
                            <img src={song.artwork} alt="" className="w-full h-full object-cover" />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center">
                                <Music2 className="h-12 w-12 text-white/50" />
                            </div>
                        )}
                    </div>
                    <div className="flex-1 min-w-0">
                        <p className="text-xs uppercase tracking-wider text-white/70 mb-1">Song</p>
                        <h1 className="text-3xl md:text-5xl font-bold mb-2 break-words">{song.title}</h1>
                        <p className="text-white/90 text-sm md:text-base mb-4">
                            {song.group && <Link href={`/groups/${song.group.slug}`} className="hover:underline">{song.group.name}</Link>}
                            {song.artist && <Link href={`/artists/${song.artist.slug}`} className="hover:underline">{song.group ? ' · ' : ''}{song.artist.name}</Link>}
                            {!song.group && !song.artist && song.church && <Link href={`/churches/${song.church.slug}`} className="hover:underline">{song.church.name}</Link>}
                        </p>
                        <p className="text-xs text-white/70 mb-6">
                            {song.album && <Link href={`/albums/${song.album.slug}`} className="hover:underline">From {song.album.title}</Link>}
                            {song.album && song.release_year && ' · '}
                            {song.release_year}
                            {song.duration && ` · ${formatDuration(song.duration)}`}
                            {' · '}{song.streams.toLocaleString()} plays
                        </p>
                        <div className="flex gap-2 flex-wrap">
                            <button
                                onClick={() => player.play(payload, related)}
                                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white text-brand-700 font-semibold hover:bg-brand-50 transition-colors"
                            >
                                <Play className="h-4 w-4" /> Play
                            </button>
                            {song.allow_download && (
                                <a
                                    href={`/download/song/${song.slug}`}
                                    className="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-white/10 text-white font-medium hover:bg-white/20 transition-colors"
                                >
                                    <Download className="h-4 w-4" /> Download
                                </a>
                            )}
                        </div>
                    </div>
                </div>
            </section>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div className="lg:col-span-2 space-y-6">
                    {song.description && (
                        <section className="rounded-2xl bg-white border border-slate-200 p-5">
                            <h2 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Description</h2>
                            <p className="text-sm text-ink whitespace-pre-wrap">{song.description}</p>
                        </section>
                    )}
                    {song.lyrics && (
                        <section className="rounded-2xl bg-white border border-slate-200 p-5">
                            <h2 className="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-2">Lyrics</h2>
                            <pre className="text-sm text-ink whitespace-pre-wrap font-sans leading-relaxed">{song.lyrics}</pre>
                        </section>
                    )}
                </div>
                <aside className="space-y-4">
                    <section className="rounded-2xl bg-white border border-slate-200 p-5 space-y-3">
                        {song.language && <MetaRow label="Language" value={song.language} />}
                        {song.genre && <MetaRow label="Genre" value={song.genre} />}
                        {song.categories.length > 0 && (
                            <div>
                                <p className="text-xs uppercase tracking-wider text-slate-500 mb-1">Categories</p>
                                <div className="flex flex-wrap gap-1">
                                    {song.categories.map((c) => (
                                        <Link key={c.slug} href={`/songs?category=${c.slug}`} className="px-2.5 py-1 text-xs rounded-full bg-brand-50 text-brand-800 hover:bg-brand-100">{c.name}</Link>
                                    ))}
                                </div>
                            </div>
                        )}
                        {song.occasions.length > 0 && (
                            <div>
                                <p className="text-xs uppercase tracking-wider text-slate-500 mb-1">Occasions</p>
                                <div className="flex flex-wrap gap-1">
                                    {song.occasions.map((o) => (
                                        <Link key={o.slug} href={`/occasions/${o.slug}`} className="px-2.5 py-1 text-xs rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">{o.name}</Link>
                                    ))}
                                </div>
                            </div>
                        )}
                        {song.featured_artists.length > 0 && (
                            <div>
                                <p className="text-xs uppercase tracking-wider text-slate-500 mb-1">Featured</p>
                                <div className="flex flex-wrap gap-1">
                                    {song.featured_artists.map((a) => (
                                        <Link key={a.slug} href={`/artists/${a.slug}`} className="px-2.5 py-1 text-xs rounded-full bg-slate-100 text-slate-700 hover:bg-slate-200">{a.name}</Link>
                                    ))}
                                </div>
                            </div>
                        )}
                    </section>
                </aside>
            </div>

            {related.length > 0 && (
                <section>
                    <h2 className="text-lg font-bold text-ink mb-3">More like this</h2>
                    <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                        {related.map((s, i) => (
                            <SongRow key={s.id} song={s} index={i} queue={related} showRank={false} />
                        ))}
                    </ul>
                </section>
            )}
        </AppLayout>
    );
}

function MetaRow({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <p className="text-xs uppercase tracking-wider text-slate-500">{label}</p>
            <p className="text-sm text-ink">{value}</p>
        </div>
    );
}

import { Link } from '@inertiajs/react';
import { FilePlus2, Heart, ListMusic, Music2, Play, Plus, Users2 } from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';
import { usePlayer } from '@/Contexts/PlayerContext';
import type { SongPayload } from '@/types';
import clsx from 'clsx';

interface PlaylistCard {
    id: number;
    name: string;
    slug: string;
    cover: string | null;
    visibility: string;
}

interface DashboardProps {
    greeting: string;
    stats: {
        playlists: number;
        liked: number;
        following: number;
        submissions: number;
    };
    playlists: PlaylistCard[];
    likedSongs: SongPayload[];
    recommended: SongPayload[];
    recentSubmissions: Array<{
        id: number;
        reference: string;
        song_title: string;
        status: string;
    }>;
}

const STATUS_COLOR: Record<string, string> = {
    draft: 'bg-slate-100 text-slate-700',
    awaiting_payment: 'bg-amber-100 text-amber-800',
    payment_pending: 'bg-amber-100 text-amber-800',
    paid: 'bg-brand-100 text-brand-800',
    under_review: 'bg-sky-100 text-sky-800',
    approved: 'bg-brand-100 text-brand-800',
    rejected: 'bg-rose-100 text-rose-800',
    changes_requested: 'bg-orange-100 text-orange-800',
    published: 'bg-brand-100 text-brand-800',
    withdrawn: 'bg-slate-100 text-slate-500',
};

function StatCard({
    icon: Icon,
    label,
    value,
    href,
}: {
    icon: React.ComponentType<{ className?: string }>;
    label: string;
    value: number;
    href: string;
}) {
    return (
        <Link
            href={href}
            className="rounded-2xl bg-white border border-slate-200 hover:border-slate-300 hover:shadow-card p-4 flex items-center gap-3 transition-all"
        >
            <span className="h-10 w-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                <Icon className="h-5 w-5" />
            </span>
            <div className="min-w-0">
                <p className="text-2xl font-bold text-ink leading-none">{value}</p>
                <p className="text-xs text-slate-500 mt-1">{label}</p>
            </div>
        </Link>
    );
}

function SongRow({ song, rank }: { song: SongPayload; rank?: number }) {
    const player = usePlayer();
    return (
        <button
            onClick={() => player.play(song)}
            className="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors group text-left"
        >
            {rank !== undefined && (
                <span className="w-6 text-sm font-semibold tabular-nums text-slate-400 text-center">
                    {rank}
                </span>
            )}
            <div className="relative h-10 w-10 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shrink-0 overflow-hidden">
                {song.artwork ? (
                    <img src={song.artwork} alt="" className="w-full h-full object-cover" />
                ) : (
                    <Music2 className="h-4 w-4 text-white/70" />
                )}
                <span className="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <Play className="h-4 w-4 text-white translate-x-[1px]" />
                </span>
            </div>
            <div className="flex-1 min-w-0">
                <p className="text-sm font-medium text-ink truncate">{song.title}</p>
                <p className="text-xs text-slate-500 truncate">{song.artist}</p>
            </div>
        </button>
    );
}

function PlaylistTile({ p }: { p: PlaylistCard }) {
    return (
        <Link
            href={`/playlists/${p.id}`}
            className="rounded-2xl bg-white border border-slate-200 p-3 hover:shadow-card-hover hover:border-slate-300 transition-all"
        >
            <div className="aspect-square rounded-xl overflow-hidden bg-gradient-to-br from-brand-500 to-brand-700 mb-3 flex items-center justify-center">
                {p.cover ? (
                    <img src={p.cover} alt="" className="w-full h-full object-cover" />
                ) : (
                    <ListMusic className="h-10 w-10 text-white/60" />
                )}
            </div>
            <p className="text-sm font-semibold text-ink line-clamp-1">{p.name}</p>
            <p className="text-xs text-slate-500 capitalize">{p.visibility}</p>
        </Link>
    );
}

export default function Dashboard(props: DashboardProps) {
    return (
        <AppLayout title="Home">
            <div className="space-y-8">
                <header>
                    <h1 className="text-2xl md:text-3xl font-bold text-ink">
                        {props.greeting}
                    </h1>
                    <p className="text-sm text-slate-500 mt-1">
                        Your music, your playlists, your submissions.
                    </p>
                </header>

                <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <StatCard icon={ListMusic} label="Playlists" value={props.stats.playlists} href="/playlists" />
                    <StatCard icon={Heart} label="Liked songs" value={props.stats.liked} href="/likes" />
                    <StatCard icon={Users2} label="Following" value={props.stats.following} href="/following" />
                    <StatCard icon={FilePlus2} label="Submissions" value={props.stats.submissions} href="/submissions" />
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <Link
                        href="/playlists/new"
                        className="rounded-2xl bg-brand-600 hover:bg-brand-700 text-white p-4 flex items-center gap-3 transition-colors"
                    >
                        <span className="h-10 w-10 rounded-lg bg-white/15 flex items-center justify-center">
                            <Plus className="h-5 w-5" />
                        </span>
                        <div>
                            <p className="font-semibold text-sm">Create playlist</p>
                            <p className="text-xs text-brand-50/85">Build your own mix</p>
                        </div>
                    </Link>
                    <Link
                        href="/submit-music"
                        className="rounded-2xl bg-white border border-slate-200 hover:border-slate-300 p-4 flex items-center gap-3 hover:shadow-card transition-all"
                    >
                        <span className="h-10 w-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                            <FilePlus2 className="h-5 w-5" />
                        </span>
                        <div>
                            <p className="font-semibold text-sm text-ink">Submit music</p>
                            <p className="text-xs text-slate-500">Share your song</p>
                        </div>
                    </Link>
                    <Link
                        href="/discover"
                        className="rounded-2xl bg-white border border-slate-200 hover:border-slate-300 p-4 flex items-center gap-3 hover:shadow-card transition-all"
                    >
                        <span className="h-10 w-10 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                            <Music2 className="h-5 w-5" />
                        </span>
                        <div>
                            <p className="font-semibold text-sm text-ink">Discover</p>
                            <p className="text-xs text-slate-500">Find new music</p>
                        </div>
                    </Link>
                </div>

                <section>
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-lg font-semibold text-ink">My playlists</h2>
                        <Link href="/playlists" className="text-xs font-medium text-slate-500 hover:text-brand-700">
                            See all
                        </Link>
                    </div>
                    {props.playlists.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center">
                            <ListMusic className="h-8 w-8 text-slate-300 mx-auto mb-2" />
                            <p className="text-sm text-slate-500 mb-3">You haven't created any playlists yet.</p>
                            <Link
                                href="/playlists/new"
                                className="inline-flex items-center gap-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2"
                            >
                                <Plus className="h-4 w-4" />
                                Create your first playlist
                            </Link>
                        </div>
                    ) : (
                        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                            {props.playlists.map((p) => <PlaylistTile key={p.id} p={p} />)}
                        </div>
                    )}
                </section>

                {props.recentSubmissions.length > 0 && (
                    <section>
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-lg font-semibold text-ink">My submissions</h2>
                            <Link href="/submissions" className="text-xs font-medium text-slate-500 hover:text-brand-700">
                                See all
                            </Link>
                        </div>
                        <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                            {props.recentSubmissions.map((s) => (
                                <li key={s.id}>
                                    <Link href={`/submissions/${s.id}/edit`} className="flex items-center gap-4 px-4 py-3 hover:bg-slate-50">
                                        <div className="h-10 w-10 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                                            <Music2 className="h-4 w-4" />
                                        </div>
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-semibold text-ink truncate">{s.song_title}</p>
                                            <p className="text-xs text-slate-500 font-mono truncate">{s.reference}</p>
                                        </div>
                                        <span className={clsx('px-2 py-1 rounded-full text-[11px] font-semibold whitespace-nowrap', STATUS_COLOR[s.status] ?? 'bg-slate-100 text-slate-700')}>
                                            {s.status.replace('_', ' ')}
                                        </span>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}

                {props.likedSongs.length > 0 && (
                    <section>
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-lg font-semibold text-ink">Liked songs</h2>
                            <Link href="/likes" className="text-xs font-medium text-slate-500 hover:text-brand-700">
                                See all
                            </Link>
                        </div>
                        <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                            {props.likedSongs.map((song) => (
                                <li key={song.id}><SongRow song={song} /></li>
                            ))}
                        </ul>
                    </section>
                )}

                <section>
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-lg font-semibold text-ink">Made for you</h2>
                        <Link href="/discover" className="text-xs font-medium text-slate-500 hover:text-brand-700">
                            More
                        </Link>
                    </div>
                    {props.recommended.length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500">
                            No recommendations yet — the catalogue is still growing.
                        </div>
                    ) : (
                        <ul className="rounded-2xl bg-white border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                            {props.recommended.map((song, idx) => (
                                <li key={song.id}><SongRow song={song} rank={idx + 1} /></li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
